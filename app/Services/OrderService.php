<?php

namespace App\Services;

use App\Enums\OrderUserTypeEnum;
use App\Enums\Purchase\DiscountEnum;
use App\Enums\Purchase\OrderItemStatusEnum;
use App\Enums\Purchase\OrderStatusEnum;
use App\Enums\Purchase\OrderTypeEnum;
use App\Enums\Purchase\PaymentStatusEnum;
use App\Enums\SettingEnum;
use App\Enums\UserTypeEnum;
use App\Exceptions\OrderException;
use App\Models\Package;
use App\Models\Point\CouponCode;
use App\Models\Product;
use App\Models\Purchase\Order;
use App\Models\Purchase\OrderItem;
use App\Models\Purchase\OrderItemProduct;
use App\Models\Setting;
use App\Models\User;
use App\Models\VendorProductPrice;
use App\Notifications\UserOrderNotification;
use Exception;
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
        $order_item_products = [];
        $products_ordered = [];

        if ($request->has('products')) {
            $product_slug = $request->collect('products')->pluck('product_slug');
            $DB_products = Product::select('id', 'slug', 'discount_percent', 'name', 'slug', 'prescription_required')
                ->with(['variations.vendorProductPrices', 'media'])
                ->whereIn('slug', $product_slug)
                ->get()
                ->keyBy('slug');
            // Log::info($request->products);
            foreach ($request->products as $item) {
                $src_product = $DB_products[$item['product_slug']];
                $product_variant = collect($src_product['variations'])->firstWhere('id', $item['variant_id']);
                if (empty($product_variant)) {
                    throw new OrderException('Variation does not belong to ordered product item.');
                }
                [
                    'price' => $price,
                    'previous_price' => $original_price,
                    'discount_percent' => $discount_percent,
                    'discount_source' => $discount_source,
                ] = $product_variant->original_price;
                $total_stock_left = $product_variant->vendorProductPrices->sum(fn($q) => $q->stock_left);
                // $product_discount_percent = $src_product['discount_percent'];
                // $price = empty($product_discount_percent) ? $product_variant_price : ($product_variant_price - ($product_variant_price * $product_discount_percent) / 100);
                // Log::info($stock);
                $quantity = $item['quantity'];
                if ($quantity > $total_stock_left) {
                    throw ValidationException::withMessages([
                        'products' => [
                            'Requested quantity (' . $item['quantity'] . ') exceeds available stock (' . $total_stock_left . ') for: ' . $src_product->name
                        ],
                    ]);
                }
                if ($src_product->prescription_required && empty($item['prescription_image'])) {
                    throw ValidationException::withMessages([
                        'products' => [
                            'Prescription is required for product : ' . $src_product->name
                        ],
                    ]);
                }

                $order_item_products[$item['product_slug']][] = [
                    'product_variation_id' => $item['variant_id'],
                    'quantity' => $quantity
                ];

                $products_ordered[] = [
                    'status' => OrderStatusEnum::PENDING->value,
                    'item_type' => Product::class,
                    'item_id' => $src_product->id,
                    'item_name' => $src_product->name,
                    'item_slug' => $item['product_slug'],
                    'item_variant_id' => $item['variant_id'],
                    'variant_name' => $product_variant->name,
                    'variant_size' => $product_variant->size_value . ' ' . $product_variant->size_unit,
                    'quantity' => $quantity,
                    'price' => $price,
                    'original_price' => $original_price ?? $price,
                    'discount_percent' => $discount_percent,
                    'discount_source' => $discount_source,
                    'total' => $price * $quantity,
                    'image' => $src_product->getFirstMediaUrl(Product::PRODUCT_FEATURE),
                    'prescription_image' => array_key_exists('prescription_image', $item) ? $item['prescription_image'] : null
                ];
            }
        }

        // dd($order_item_products);
        $packages_ordered = [];
        if ($request->has('packages')) {
            $product_slug = $request->collect('packages')->pluck('package_slug');
            $DB_packages = Package::select('id', 'slug', 'name', 'price', 'discount_percent')
                ->with(['media', 'packageProducts'])
                ->whereIn('slug', $product_slug)
                ->get()
                ->keyBy('slug');

            // dd($order_item_products);
            foreach ($request->packages as $item) {

                $src_package = $DB_packages[$item['package_slug']];
                $actual_package_price = $src_package['price'];
                $package_discount_percent = $src_package['discount_percent'];
                $package_price = empty($package_discount_percent) ? $actual_package_price : ($actual_package_price - ($actual_package_price * $package_discount_percent) / 100);
                $package_quantity = $item['quantity'];

                $order_item_products[$item['package_slug']] = $src_package->packageProducts->map(fn($PP) => [
                    'product_variation_id' => $PP->product_variation_id,
                    'quantity' => $item['quantity'] * $PP->quantity
                ])->all();
                $packages_ordered[] = [
                    'status' => OrderStatusEnum::PENDING->value,
                    'item_type' => Package::class,
                    'item_id' => $src_package->id,
                    'item_name' => $src_package->name,
                    'item_slug' => $item['package_slug'],
                    'item_variant_id' => null,
                    'variant_name' => null,
                    'variant_size' => null,
                    'quantity' => $package_quantity,
                    'price' => $package_price,
                    'original_price' => $src_package->price,
                    'discount_percent' => $package_discount_percent,
                    'discount_source' => empty($package_discount_percent) ? null : DiscountEnum::PACKAGE_DISCOUNT,
                    'total' => $package_quantity * $package_price,
                    'image' => $src_package->getFirstMediaUrl(Package::PACKAGE_FEATURED),
                    'prescription_image' => null
                ];
            }
        }
        $order_items = [...$products_ordered, ...$packages_ordered];
        $price = collect($order_items)->sum('total'); #this is total final price of a cart

        $previous_price = $price;
        // =====================================================
        // === PROMOCODE SECTION START ====
        // =====================================================
        $promo_discount = 0;

        if (!empty($request->code)) {
            $promocode = CouponCode::where('code', $request->code)->where('is_active', true)->first();
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
            $promo_discount = (float)round($promo_discount,2);
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
                // $previous_price += $gift_wrap_charge;
            }
        }


        $order_detail = [
            'previous_price' => (float)round($previous_price,2),
            'price' => (float)round($price,2),
            'promo_code' => $request->code,          // <-- Added
            'promo_discount' => $promo_discount,        // <-- Added
            // 'used_coupon_code_id' => $promocode?->id,
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

        // $response = null;
        // dd($order_items);
        
        return DB::transaction(function () use ($request, $order_detail, $order_items, &$order_item_products) {
            $order_code = Str::random(6);
            $user = Auth::user();
            // Log::info('$order_item_products');
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
                $user->cart()->whereIn('item_slug', array_keys($order_item_products))->delete();
            } else {
                $order = array_merge(
                    $request->only(['name', 'email', 'mobile', 'address', 'description']),
                    $order_detail,
                    [
                        'user_type' => OrderUserTypeEnum::GUEST->value,
                        'order_code' => $order_code
                    ]
                );
            }

            $order = Order::create($order);

            foreach ($order_items as $item) {
                $created_order_items = $order->orderItems()->create($item);
                foreach ($order_item_products[$created_order_items->item_slug] as &$items) {
                    $items['order_item_id'] = $created_order_items->id;
                    $items['order_id'] = $created_order_items->order_id;
                }
                unset($items);
                if (array_key_exists('prescription_image', $item) && $item['prescription_image']) {
                    $image  = $created_order_items->addMedia($item['prescription_image'])->toMediaCollection(OrderItem::PRESCRIPTION_IMAGE);
                    $item['prescription_image'] = $image->getUrl();
                    // dd($item['prescription_image']);
                }
            }
            DB::table('order_item_products')->insert(array_merge(...array_values($order_item_products)));
            // User::filterByRole(UserTypeEnum::ADMIN)->first()->notify(new UserOrderNotification($order));
            // dd($order_items);
            return [
                'previous_price' => $order_detail['previous_price'], #total cart item total
                'amount' => (float) $order->price,
                'order_number' => $order->order_code,
                'payment_method' => $order->payment_method,
                'date' => $order->created_at->format('Y/m/d'),
                'delivery_address' => $order->address,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'gift_wrap' => $request->gift_wrap,
                'gift_wrap_remarks' => $request->gift_wrap ? $request->gift_wrap_remarks : null,
                'gift_wrap_charge' => (float) $order_detail['gift_wrap_charge'],
                'promo_code' => $order_detail['promo_code'],                 // <-- Added
                'promo_discount' => (float) $order_detail['promo_discount'],       // <-- Added
                'ordered_items' => array_map(fn($OI) => [
                    "item_name" => $OI['item_name'],
                    "variant_name" => $OI['variant_name'],
                    "quantity" => (int)$OI['quantity'],
                    "price" => (float)$OI['price'],
                    "total" => (float)$OI['total'],
                    "prescription_image" => $order->orderItems
                        ->firstWhere('item_slug', $OI['item_slug'])
                        ?->getFirstMediaUrl(OrderItem::PRESCRIPTION_IMAGE) ?: null,
            
                ], $order_items),
            ];
        });
    }

    function getListOfAssignedOrder(Request $request) {
        $per_page = $request->query('per_page', 10);
        $search = $request->query('search');
        $vendor_id = Auth::user()->vendor->id;
        $pagination = Order::whereRelation('orderItems', 'assigned_vendor_id', $vendor_id)
            ->withCount(['orderItems' => fn($qry) => $qry->where('assigned_vendor_id', $vendor_id)])
            ->when($search, function ($qry) use ($request) {
                $qry->where(function ($q) use ($request) {
                    $q->whereLike('name', '%' . $request->search . '%')
                        ->orWhereLike('email', '%' . $request->search . '%')
                        ->orWhereLike('mobile', '%' . $request->search . '%')
                        ->orWhereLike('address', '%' . $request->search . '%');
                });
            })
            ->orderBy('id', 'DESC')
            ->paginate($per_page);
        return $pagination;
    }

    function showOrderDetail(Order $order, bool $only_my_assigned_detail = false) {
        $order->load([
            'orderItems' => fn($qry) => $qry->with([
                'productVariant' => fn($qry) => $qry->with([
                    'product',
                    'vendorProductPrices' => fn($qry) =>
                    $qry->whereRelation('ProductVendor', 'vendor_id', Auth::user()->vendor->id),
                ]),
                'item',
                'orderItemProducts.batchNumbers',
                'orderItemProducts.variation.product',
                'orderItemProducts.variation' => fn($qry) => $qry->with([
                    'vendorProductPrices' => fn($q) =>
                    $q->whereRelation('ProductVendor', 'vendor_id', Auth::user()->vendor->id),
                ]),
            ])->when($only_my_assigned_detail, fn($qry) => $qry->where('assigned_vendor_id', Auth::user()->vendor->id))
        ]);

        /* if ($order->orderItems->isEmpty()) {
            throw new OrderException('No order item has been assigned to you from this order.');
            // return $this->apiError('No order item has been assigned to you from this order.');
        } */
        return $order;
    }

    function assignBatchToOrderItemService(Order $order, $requested_data) {
        if ($order->status == OrderStatusEnum::DELIVERED || $order->status == OrderStatusEnum::SHIPPED) {
            throw new OrderException('This order has already been shipped/delivered.');
        }elseif ($order->status == OrderStatusEnum::CANCELLED->value) {
            throw new OrderException('This order has been cancelled.');
        }
        
        $incoming_order_items = collect($requested_data)
            ->flatMap(function ($item) {
                return collect($item['batch_numbers'])->map(function ($bn) use ($item) {
                    return [
                        'order_item_product_id' => $item['OIP_ID'],
                        'vendor_product_price_id' => $bn['batch_number_id'],
                        'quantity'        => $bn['quantity'],
                    ];
                });
            })
            ->values();
            // Log::info($incoming_order_items);
        $vendor_id = Auth::user()->vendor->id;
        $requested_OIP_ids = $incoming_order_items->pluck('order_item_product_id');
        $VPPs = $incoming_order_items->pluck('quantity', 'vendor_product_price_id')->all();

        $assigned_order_items_of_vendor = $order->load([
            'orderItems' => // grabbing all assigned order items
                fn($qry) => $qry->with([
                    'orderItemProducts' => fn($q) => 
                        $q->with([
                            'vendorProductPrices' => fn($qr) => 
                                $qr->whereRelation('ProductVendor','vendor_id',$vendor_id)
                                    ->whereIn('id', array_keys($VPPs))
                        ])
                        ->whereIn('id', $requested_OIP_ids->all())
                ])
                // load only assigned and oreder_items assigned to that vendor
                ->whereIn('status', [OrderItemStatusEnum::ASSIGNED]) 
                ->where('assigned_vendor_id', $vendor_id)
                // ->whereHas('orderItemProducts', fn($q) => $q->whereIn('id', $requested_OIP_ids->all()))
        ]);

        $result = $incoming_order_items
            ->groupBy('order_item_product_id')
            ->map(fn($items) => $items->pluck('vendor_product_price_id')->all())
            ->all();
        // Log::info($result);
        $assigned_order_items_of_vendor->orderItems->pluck('orderItemProducts')->flatten()->each(function($item) use($result){
            if ($item->vendorProductPrices->pluck('id')->intersect($result[$item->id])->count() != count($result[$item->id])) {
                throw new OrderException("Batch number belongs to different product.");
            }
        });

        if ($incoming_order_items->groupBy('order_item_product_id')->count() > 1) {
            throw new OrderException('Only one order item can be batched at a time.');
        }
        
        # all order items(product items) that has been assigned to this vendor
        $all_order_item_product = $assigned_order_items_of_vendor->orderItems->flatMap(fn($OI) => $OI->orderItemProducts);
        
        $grouping_OIP_by_its_order_id = $all_order_item_product->whereIn('id', $requested_OIP_ids->all())
            ->groupBy('order_item_id'); 

        $not_authorized_to_batch_item_orders = $requested_OIP_ids->diff($all_order_item_product->pluck('id')->toArray())->count() != 0;
        if ($not_authorized_to_batch_item_orders) {
            throw new OrderException('You are not authorized to batch this order item.');
        }


        $quantity_not_enough = !$incoming_order_items->groupBy('order_item_product_id')
            ->every(function($IOI, $order_item_product_id) use($all_order_item_product){
                $OIP = $all_order_item_product->firstWhere('id', $order_item_product_id);
                return $OIP && $OIP['quantity'] == $IOI->sum('quantity'); 
            });
        if ($quantity_not_enough) {
            throw new OrderException('Quantity is not equal.');
        }

        // Log::info($VPPs);
        
        $vendorProducts = VendorProductPrice::with(['orderItemProductBatchNumber', 'orders'])
            ->whereRelation('ProductVendor', 'vendor_id', $vendor_id)
            ->whereIn('id', array_keys($VPPs))
            ->get();

        if ($vendorProducts->isEmpty()) {
            throw new OrderException('No vendor products found.');
        }

        $vendorProducts->every(function ($item) use ($VPPs) {
            if ($item->stock_left < $VPPs[$item->id]) {
                throw new OrderException('Insufficient stock.');
            }
        });


        DB::transaction(function () use (
                $order, 
                $incoming_order_items, 
                $requested_OIP_ids, 
                $all_order_item_product, 
                $assigned_order_items_of_vendor,
                $vendor_id,
                $grouping_OIP_by_its_order_id
            ) {
            $order_item_products_ids = $requested_OIP_ids->toArray();
            $order_item_products_ids = array_unique($order_item_products_ids);
            DB::table('order_item_product_batch_numbers')
                ->whereIn('order_item_product_id', $order_item_products_ids)
                ->delete();

            DB::table('order_item_product_batch_numbers')->insert($incoming_order_items->all());
            // dd($requested_OIP_ids);
            $an_order_item_id = $grouping_OIP_by_its_order_id->keys()->first();
            $vendor_all_order_item_has_been_batched = !OrderItemProduct::doesntHave('batchNumbers')
                ->where('order_item_id', $an_order_item_id)
                ->exists();
            if ($vendor_all_order_item_has_been_batched) {
                $assigned_order_items_of_vendor->orderItems()
                    ->where([
                        ['assigned_vendor_id', $vendor_id],
                        ['id', $an_order_item_id],
                    ])
                    ->update(['batch_assignment_status' => $vendor_all_order_item_has_been_batched]);
            }

            $all_order_item_has_been_batched = !OrderItemProduct::doesntHave('batchNumbers')
                ->where('order_id', $order->id)
                ->exists();
            // dd($all_item_has_been_batched);
            // if ($all_order_item_has_been_batched) {
                $order->update(['is_order_completely_assigned' => $all_order_item_has_been_batched]);
            //}
        });
    }
}
