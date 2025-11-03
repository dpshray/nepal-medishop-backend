<?php

namespace App\Services;

use App\Enums\OrderUserTypeEnum;
use App\Enums\Purchase\OrderStatusEnum;
use App\Enums\Purchase\OrderTypeEnum;
use App\Enums\Purchase\PaymentStatusEnum;
use App\Enums\SettingEnum;
use App\Models\Package;
use App\Models\Product;
use App\Models\Purchase\Order;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class OrderService
{
    function saveOrder(Request $request, OrderTypeEnum $order_type)
    {
        $products_ordered = [];

        if ($request->has('products')) {
            $product_slug = $request->collect('products')->pluck('product_slug');
            $products = Product::select('id', 'slug', 'discount_percent', 'name', 'slug')->with(['variations'])->whereIn('slug', $product_slug)->get()->keyBy('slug');

            $products_ordered = $request->collect('products')->map(function ($item) use ($products) {
                $product = $products[$item['product_slug']];
                $product_variant = collect($product['variations'])->firstWhere('id', $item['variant_id']);
                // Log::info($product_variant);
                $product_variant_price = $product_variant->platform_price;
                $product_discount_percent = $product['discount_percent'];
                $price = empty($product_discount_percent) ? $product_variant_price : ($product_variant_price - ($product_variant_price * $product_discount_percent) / 100);
                $quantity = $item['quantity'];
                return [
                    'item_type' => Product::class,
                    'item_id' => $products[$item['product_slug']]->id,
                    'item_name' => $products[$item['product_slug']]->name,
                    'item_slug' => $item['product_slug'],
                    'item_variant_id' => $item['variant_id'],
                    'variant_name' => $product_variant->name,
                    'variant_size' => $product_variant->size_value . ' ' . $product_variant->size_unit,
                    'quantity' => $quantity,
                    'price' => $price,
                    'total' => $price * $quantity
                ];
            });
        }

        $packages_ordered = [];
        if ($request->has('packages')) {
            $product_slug = $request->collect('packages')->pluck('package_slug');
            $packages = Package::select('id', 'slug', 'name', 'price', 'discount_percent')->whereIn('slug', $product_slug)->get()->keyBy('slug');

            $packages_ordered = $request->collect('packages')->map(function ($item) use ($packages) {
                $package = $packages[$item['package_slug']];
                $actual_package_price = $package['price'];
                $package_discount_precent = $package['discount_percent'];
                $package_price = empty($package_discount_precent) ? $actual_package_price : ($actual_package_price - ($actual_package_price * $package_discount_precent) / 100);
                $package_quantity = $item['quantity'];

                return [
                    'item_type' => Package::class,
                    'item_name' => $packages[$item['package_slug']]->name,
                    'item_slug' => $item['package_slug'],
                    'item_id' => $packages[$item['package_slug']]->id,
                    'quantity' => $package_quantity,
                    'price' => $package_price,
                    'total' => $package_quantity * $package_price
                ];
            });
        }
        $order_items = [...$products_ordered, ...$packages_ordered];
        // Log::info($order_items);
        $price = collect($order_items)->sum('total');

        $gift_wrap_status = $request->gift_wrap;
        $gift_wrap_charge = 0;

        if ($gift_wrap_status) {
            $gift_wrap_charge = Setting::firstWhere('key', SettingEnum::GIFT_WRAP_CHARGE->value);
            if ($gift_wrap_charge) {
                $gift_wrap_charge = $gift_wrap_charge->value;
                $price += $gift_wrap_charge;
            }
        }

        $order_detail = [
            'price' => $price,
            'payment_method' => $request->payment_method,
            'payment_status' => PaymentStatusEnum::UNPAID->value,
            'status' => OrderStatusEnum::PENDING->value,
            'gift_wrap' => $request->gift_wrap,
            'gift_wrap_remarks' => $request->gift_wrap ? $request->gift_wrap_remarks : null,
            'gift_wrap_charge' => $gift_wrap_charge,
            'order_type' => $order_type,
            'created_at' => now()
        ];

        $response = null;
        $order_code = Str::random(20);
        DB::transaction(function () use ($request, $order_detail, $order_items, $order_code, $order_type) {
            $user = Auth::user();
            if ($user) { # Logged user
                $order = array_merge(
                    $request->only(['address', 'description']),
                    $order_detail,
                    [
                        'user_type' => OrderUserTypeEnum::USER, 
                        'order_code' => $order_code, 
                        'user_id' => $user
                    ]
                );
                $user->orders()->create($order)->orderItems()->createMany($order_items);
                $item_slugs = $request->collect('packages')->pluck('package_slug')->merge($request->collect('products')->pluck('product_slug'));
                $user->cart()->whereIn('item_slug', $item_slugs)->delete();
            } else { # Guest user
                $order = array_merge(
                    $request->only(['name', 'email', 'mobile', 'address', 'description']),
                    $order_detail,
                    ['user_type' => OrderUserTypeEnum::GUEST->value, 'order_code' => $order_code]
                );
                Order::create($order)->OrderItems()->createMany($order_items);
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
                        'total' => (float) $item->total
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

        $response = [
            'amount' => (float) $order->price,
            'order_number' => $order->order_code,
            'payment_method' => $order->payment_method,
            'date' => $order->created_at->format('Y/m/d'),
            'ordered_items' => $order_items,
            'delivery_address' => $order->address,
            'gift_wrap' => $request->gift_wrap,
            'gift_wrap_remarks' => $request->gift_wrap ? $request->gift_wrap_remarks : null,
            'gift_wrap_charge' => (float) $gift_wrap_charge
        ];
        return $response;
    }
}
