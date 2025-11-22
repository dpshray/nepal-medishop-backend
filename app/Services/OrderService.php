<?php

namespace App\Services;

use App\Enums\OrderUserTypeEnum;
use App\Enums\Purchase\OrderStatusEnum;
use App\Enums\Purchase\OrderTypeEnum;
use App\Enums\Purchase\PaymentStatusEnum;
use App\Enums\SettingEnum;
use App\Models\Package;
use App\Models\Point\CouponCode;
use App\Models\Product;
use App\Models\Purchase\Order;
use App\Models\Purchase\OrderItem;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class OrderService
{
    function saveOrder(Request $request, OrderTypeEnum $order_type)
    {
        $products_ordered = [];
        $promocode = CouponCode::where('code', $request->code)->where('is_active', true)->first();

        if ($request->has('products')) {
            $product_slug = $request->collect('products')->pluck('product_slug');
            $products = Product::select('id', 'slug', 'discount_percent', 'name', 'slug', 'prescription_required')
                ->with(['variations.vendorProductPrice', 'media'])
                ->whereIn('slug', $product_slug)
                ->get()
                ->keyBy('slug');

            // Log::info($request->products);

            $products_ordered = array_map(function ($item) use ($products) {
                // Log::info($item);

                $product = $products[$item['product_slug']];
                $product_variant = collect($product['variations'])->firstWhere('id', $item['variant_id']);

                $product_variant_price = $product_variant->platform_price;
                $stock = $product_variant->vendorProductPrice->units_in_stock;
                $product_discount_percent = $product['discount_percent'];
                $price = empty($product_discount_percent) ? $product_variant_price : ($product_variant_price - ($product_variant_price * $product_discount_percent) / 100);
                Log::info($stock);
                $quantity = $item['quantity'];
                if ($quantity > $stock) {
                    throw ValidationException::withMessages([
                        'products' => [
                            'Requested quantity (' . $item['quantity'] . ') exceeds available stock (' . $stock . ') for: ' . $product->name
                        ],
                    ]);
                }
                if ($product->prescription_required && empty($item['prescription_image'])) {
                    throw ValidationException::withMessages([
                        'products' => [
                            'Requested quantity (' . $item['quantity'] . ') exceeds available stock (' . $stock . ') for: ' . $product->name
                        ],
                    ]);
                }

                return [
                    'status' => OrderStatusEnum::PENDING->value,
                    'item_type' => Product::class,
                    'item_id' => $products[$item['product_slug']]->id,
                    'item_name' => $products[$item['product_slug']]->name,
                    'item_slug' => $item['product_slug'],
                    'status' => OrderStatusEnum::PENDING->value,
                    'item_variant_id' => $item['variant_id'],
                    'variant_name' => $product_variant->name,
                    'variant_size' => $product_variant->size_value . ' ' . $product_variant->size_unit,
                    'quantity' => $quantity,
                    'price' => $price,
                    'total' => $price * $quantity,
                    'image' => $product->getFirstMediaUrl(Product::PRODUCT_FEATURE),
                    'prescription_image' => array_key_exists('prescription_image', $item) ? $item['prescription_image'] : null
                ];
            }, $request->products);
        }

        $packages_ordered = [];
        if ($request->has('packages')) {
            $product_slug = $request->collect('packages')->pluck('package_slug');
            $packages = Package::select('id', 'slug', 'name', 'price', 'discount_percent')
                ->with('media')
                ->whereIn('slug', $product_slug)
                ->get()
                ->keyBy('slug');

            $packages_ordered = $request->collect('packages')->map(function ($item) use ($packages) {
                $package = $packages[$item['package_slug']];
                $actual_package_price = $package['price'];
                $package_discount_precent = $package['discount_percent'];
                $package_price = empty($package_discount_precent) ? $actual_package_price : ($actual_package_price - ($actual_package_price * $package_discount_precent) / 100);
                $package_quantity = $item['quantity'];

                return [
                    'status' => OrderStatusEnum::PENDING->value,
                    'item_type' => Package::class,
                    'item_name' => $packages[$item['package_slug']]->name,
                    'item_slug' => $item['package_slug'],
                    'item_id' => $packages[$item['package_slug']]->id,
                    'quantity' => $package_quantity,
                    'status' => OrderStatusEnum::PENDING->value,
                    'price' => $package_price,
                    'total' => $package_quantity * $package_price,
                    'image' => $packages[$item['package_slug']]->getFirstMediaUrl(Package::PACKAGE_FEATURED),
                ];
            });
        }

        $order_items = [...$products_ordered, ...$packages_ordered];
        $price = collect($order_items)->sum('total');
        $previous_price = $price;
        // =====================================================
        // === PROMOCODE SECTION START ====
        // =====================================================
        $promo_discount = 0;

        if ($promocode) {
            $now = now();

            if ($promocode->start_date && $promocode->start_date > $now) {
                throw ValidationException::withMessages([
                    'code' => ['Promocode is not active yet.'],
                ]);
            }

            if ($promocode->end_date && $promocode->end_date < $now) {
                throw ValidationException::withMessages([
                    'code' => ['Promocode has expired.'],
                ]);
            }

            // Calculate discount
            $promo_discount = ($price * $promocode->discount_percent) / 100;
            if ($promo_discount > $price) {
                $promo_discount = $price;
            }
            $price -= $promo_discount;
        }
        // =====================================================
        // === PROMOCODE SECTION END ===========================
        // =====================================================

        $gift_wrap_status = $request->gift_wrap;
        $gift_wrap_charge = 0;

        if ($gift_wrap_status) {
            $gift_wrap_charge = Setting::firstWhere('key', SettingEnum::GIFT_WRAP_CHARGE->value);
            if ($gift_wrap_charge) {
                $gift_wrap_charge = $gift_wrap_charge->value;
                $price += $gift_wrap_charge;
                $previous_price += $gift_wrap_charge;
            }
        }


        $order_detail = [
            'previous_price' => $previous_price,
            'price' => $price,
            'promo_code' => $promocode?->code,          // <-- Added
            'promo_discount' => $promo_discount,        // <-- Added
            'used_coupon_code_id' => $promocode?->id,
            'payment_method' => $request->payment_method,
            'payment_status' => PaymentStatusEnum::UNPAID->value,
            'status' => OrderStatusEnum::PENDING->value,
            'gift_wrap' => $request->gift_wrap,
            'gift_wrap_remarks' => $request->gift_wrap ? $request->gift_wrap_remarks : null,
            'gift_wrap_charge' => $gift_wrap_charge,
            'order_type' => $order_type,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'created_at' => now()
        ];

        $response = null;
        $order_code = Str::random(6);

        DB::transaction(function () use ($request, $order_detail, $order_items, $order_code, $order_type) {
            $user = Auth::user();

            if ($user) {
                $order = array_merge(
                    $request->only(['address', 'description']),
                    $order_detail,
                    [
                        'user_type' => OrderUserTypeEnum::USER,
                        'order_code' => $order_code,
                        'user_id' => $user
                    ]
                );

                $order_items_ins = $user->orders()->create($order)->orderItems();

                foreach ($order_items as $item) {
                    $created_order_items = $order_items_ins->create($item);
                    if (array_key_exists('prescription_image', $item) && $item['prescription_image']) {
                        $created_order_items->addMedia($item['prescription_image'])->toMediaCollection(OrderItem::PRESCRIPTION_IMAGE);
                    }
                }

                $item_slugs = $request->collect('packages')->pluck('package_slug')
                    ->merge($request->collect('products')->pluck('product_slug'));

                $user->cart()->whereIn('item_slug', $item_slugs)->delete();
            } else {
                $order = array_merge(
                    $request->only(['name', 'email', 'mobile', 'address', 'description']),
                    $order_detail,
                    [
                        'user_type' => OrderUserTypeEnum::GUEST->value,
                        'order_code' => $order_code
                    ]
                );

                $order_items_ins = Order::create($order)->orderItems();

                foreach ($order_items as $item) {
                    $created_order_items = $order_items_ins->create($item);
                    if (array_key_exists('prescription_image', $item) && $item['prescription_image']) {
                        $created_order_items->addMedia($item['prescription_image'])->toMediaCollection(OrderItem::PRESCRIPTION_IMAGE);
                    }
                }
            }

            $user = Auth::user();
        });

        $order = Order::where('order_code', $order_code)->firstOrFail();
        $order_items = $order
            ->orderItems()
            ->with(['package', 'product.variations'])
            ->get()
            ->map(function ($item) {
                if ($item->item_type == Product::class) {
                    return [
                        'item_name' => $item->product->name,
                        'variant_name' => $item->product->variations->firstWhere('id', $item->item_variant_id)->name,
                        'quantity' => (int) $item->quantity,
                        'price' => (float) $item->price,
                        'total' => (float) $item->total,
                        'prescription_image' => $item->getFirstMediaUrl(OrderItem::PRESCRIPTION_IMAGE)
                    ];
                } elseif ($item->item_type == Package::class) {
                    return [
                        'item_name' => $item->package->name,
                        'variant_name' => null,
                        'quantity' => (int) $item->quantity,
                        'price' => (float) $item->price,
                        'total' => (float) $item->total
                    ];
                }
            });
        Log::info($order_items);
        $response = [
            'previous_price' => $previous_price,
            'amount' => (float) $order->price,
            'order_number' => $order->order_code,
            'payment_method' => $order->payment_method,
            'date' => $order->created_at->format('Y/m/d'),
            'delivery_address' => $order->address,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'gift_wrap' => $request->gift_wrap,
            'gift_wrap_remarks' => $request->gift_wrap ? $request->gift_wrap_remarks : null,
            'gift_wrap_charge' => (float) $gift_wrap_charge,

            'promo_code' => $promocode?->code,                 // <-- Added
            'promo_discount' => (float) $promo_discount,       // <-- Added

            'ordered_items' => $order_items,
        ];

        return $response;
    }
}
