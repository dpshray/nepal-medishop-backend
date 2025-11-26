<?php

namespace App\Services;

use App\Enums\Purchase\OrderItemStatusEnum;
use App\Exceptions\AssignOrderException;
use App\Models\Package;
use App\Models\Product;
use App\Models\Purchase\Order;
use App\Models\Purchase\OrderItem;
use App\Models\Vendor;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AssignOrderToVendorService
{
    public $order = null;
    public $vendor_id = null; public $search = null;
    public $product_item_variant_id_w_quantity = null;

    function vendorsThatCanFulfillOneItem(Order $order)
    {
        /** ---------------------------------------------------
         * STEP 1: Build required variations (required_qty per variant)
         * ----------------------------------------------------*/
        $data_to_watch = $order->orderItemProducts
            ->groupBy('product_variation_id')
            ->map(fn($items) => [
                'variant_id'     => $items->first()->product_variation_id,   // which variant
                'required_qty'   => $items->sum('quantity'),                // total qty needed for this order
            ])
            ->values();

        /** Extract just the variant IDs */
        $required_variations = $data_to_watch->pluck('variant_id')->all();



        /** ---------------------------------------------------
         * STEP 2: Select vendors who SELL these variations 
         * (but DO NOT load all — we will process using chunk)
         * ----------------------------------------------------*/
        $vendorQuery = Vendor::verifiedAndActive()
            ->whereHas('vendorProductPrices', function ($q) use ($required_variations) {
                // vendor must sell at least one required variation
                $q->whereIn('product_variation_id', $required_variations);
            })
            ->with([
                // preload vendor's stock data for only required variants
                'vendorProductPrices' => function ($q) use ($required_variations) {
                    $q->whereIn('product_variation_id', $required_variations)->whereRelation('ProductVendor','is_approved',true);
                },

                // preload only relevant reserved items (already assigned to a vendor)
                'orderItemProducts' => function ($q) use ($required_variations) {
                    $q->whereIn('product_variation_id', $required_variations)
                        ->whereRelation('orderItem', 'assigned_vendor_id', '<>', null);
                }
            ])
            ->when(
                $this->search,
                fn($qry) =>
                $qry->whereLike('store_name', '%' . $this->search . '%')
            );



        /** ---------------------------------------------------
         * STEP 3: Chunk and filter (vendor can supply ANY one variant)
         * ----------------------------------------------------*/
        $vendors_any = collect(); // we will collect passing vendors here

        $vendorQuery->chunk(200, function ($vendors) use (&$vendors_any, $data_to_watch) {

            foreach ($vendors as $vendor) {

                /** 
                 * Pre-calculate how much stock is already reserved
                 * Group reserved items by variant, then sum quantities
                 */
                $reserved_by_variant = $vendor->orderItemProducts
                    ->groupBy('product_variation_id')
                    ->map->sum('quantity');

                /** Check if vendor can supply AT LEAST ONE required variant */
                $can_supply_any = false;

                foreach ($data_to_watch as $dtw) {

                    $variant_id   = $dtw['variant_id'];      // required variant
                    $required_qty = $dtw['required_qty'];    // required qty

                    // vendor's pricing + stock for this variant
                    $variant_price = $vendor->vendorProductPrices
                        ->where('product_variation_id', $variant_id);

                    // vendor does not sell this variant
                    if ($variant_price->isEmpty()) {
                        continue;
                    }

                    // total reserved stock for this vendor's variant
                    $reserved = $reserved_by_variant[$variant_id] ?? 0;

                    // actual stock = units_in_stock - reserved
                    $actual_units = $variant_price->sum('units_in_stock') - $reserved;

                    // vendor meets requirement for ONE item → success
                    if ($actual_units >= $required_qty) {
                        $can_supply_any = true;
                        break; // no need to check other variants
                    }
                }

                if ($can_supply_any) {
                    $vendors_any->push($vendor); // add passing vendor to final result
                }
            }
        });

        return $vendors_any->values();
    }

    function canVendorFulfillAllItems(Order $order, array $order_item_ids, Vendor $vendor)
    {
        /** ---------------------------------------------------
         * STEP 1: Build required variations (required qty per variant)
         * ----------------------------------------------------*/
        $data_to_watch = $order->orderItemProducts()
            ->with('variation.product')
            ->whereIn('order_item_id', $order_item_ids)
            ->get()
            ->groupBy('product_variation_id')
            ->map(fn($items) => [
                'variant_id'     => $items->first()->product_variation_id,
                'required_qty'   => $items->sum('quantity'),
                'variant_name' => $items->first()->variation->name,
                'product_name' => $items->first()->variation->product->name,
            ])
            ->values();

        $required_variations = $data_to_watch->pluck('variant_id')->all();


        /** ---------------------------------------------------
         * STEP 2: Load ONLY this vendor’s relevant stock + reserved items
         * ----------------------------------------------------*/
        $vendor->load([
            'vendorProductPrices' => function ($q) use ($required_variations) {
                $q->whereIn('product_variation_id', $required_variations)
                    ->whereRelation('ProductVendor', 'is_approved', true);
            },
            'orderItemProducts' => function ($q) use ($required_variations) {
                $q->whereIn('product_variation_id', $required_variations)
                    ->whereRelation('orderItem', 'assigned_vendor_id', '<>', null);
            }
        ]);


        /** ---------------------------------------------------
         * STEP 3: Pre-calc reserved stock for this vendor
         * ----------------------------------------------------*/
        $reserved_by_variant = $vendor->orderItemProducts
            ->groupBy('product_variation_id')
            ->map->sum('quantity');


        /** ---------------------------------------------------
         * STEP 4: Check if vendor can fulfill ALL required items
         * ----------------------------------------------------*/
        $failed_items = [];

        foreach ($data_to_watch as $dtw) {

            $variant_id   = $dtw['variant_id'];
            $required_qty = $dtw['required_qty'];
            $variant_name = $dtw['variant_name'];
            $product_name = $dtw['product_name'];

            // vendor’s stock entry
            $variant_stock = $vendor->vendorProductPrices
                ->where('product_variation_id', $variant_id);

            // vendor does NOT sell this variant → FAIL
            if ($variant_stock->isEmpty()) {

                $failed_items[] = [
                    'variant_name'    => $variant_name,
                    'product_name'    => $product_name,
                    'reason'        => 'Vendor does not sell this variant',
                    'required_qty'  => $required_qty,
                    'available_qty' => 0,
                ];

                continue;
            }

            // reserved stock
            $reserved = $reserved_by_variant[$variant_id] ?? 0;

            // actual stock = stock - reserved
            $actual_units = $variant_stock->sum('units_in_stock') - $reserved;

            // vendor does NOT have enough → FAIL
            if ($actual_units < $required_qty) {

                $failed_items[] = [
                    'variant_name'    => $variant_name,
                    'product_name'    => $product_name,
                    'reason'        => 'Insufficient stock',
                    'required_qty'  => $required_qty,
                    'available_qty' => $actual_units,
                ];
            }
        }


        /** ---------------------------------------------------
         * STEP 5: Return Result
         * ----------------------------------------------------*/
        return [
            'eligible'      => empty($failed_items),
            'failed_items'  => $failed_items,
        ];
    }




    public function transformOrderItemsIntoProducts($order_item_ids){
        /**
         * exrtacting product from package and
         * transforming them in array into this format...
         * [
         *      'item_variant_id' => '.....',
         *      'quantity' => '.....',
         * ]
         */
        $package = OrderItem::with(['item.packageProducts'])
            ->whereIn('id', $order_item_ids)
            ->where('item_type', Package::class)
            ->get()
            ->map(function ($order_item) {
                return $order_item->item->packageProducts->map(function ($pkg_pdt) use ($order_item) {
                    return [
                        'quantity' => $order_item->quantity * $pkg_pdt->quantity,
                        'item_variant_id' => $pkg_pdt->product_variation_id
                    ];
                });
            })
            ->flatten(1)
            ->groupBy('item_variant_id')
            ->map(function ($group) {
                return [
                    'item_variant_id' => $group->first()['item_variant_id'],
                    'quantity' => $group->sum('quantity'),
                ];
            })
            ->values();
            // Log::info($package);

        /**
         * transforming ordered product item in array into this format...
         * [
         *      'item_variant_id' => '.....',
         *      'quantity' => '.....',
         * ]
         */
        $product = OrderItem::whereIn('id', $order_item_ids)
            ->where('item_type', Product::class)
            ->get()
            ->map(fn($item) => [
                'quantity' => $item->quantity,
                'item_variant_id' => $item->item_variant_id
            ]);
        $combined_products = $package;

        /**
         * finally combining both transformed package and product
         * and returning then in type collection
         */
        if (count($product)) {
            $combined_products = $product->merge($package->toArray())
                ->groupBy('item_variant_id')
                ->map(function ($group) {
                    return [
                        'item_variant_id' => (int)$group->first()['item_variant_id'],
                        'quantity' => $group->sum('quantity'),
                    ];
                })
                ->values();
            
        }
        return $combined_products;
    }

    function fetchEligibleVendors(array $order_item_ids, Order $order = null)
    {
        $vendor_id = $this->vendor_id;
        $orders = $this->transformOrderItemsIntoProducts($order_item_ids);
        $this->product_item_variant_id_w_quantity = $orders;
        /**
         * finally finding vendors that are eligible to
         * assign above order items
         */
        /* Log::info($orders);
        Log::info('**************************'); */

        $matchedVendors = collect(); // final result collection
        Vendor::with(['vendorProductPrices.ProductVendor.associatedVendor', 'user'])
            ->when($vendor_id, fn($qry) => $qry->where('id', $vendor_id))
            ->when($this->search, fn($qry) => $qry->whereLike('store_name', '%' . $this->search . '%'))
            ->verifiedAndActive()
            ->chunk(200, function ($vendors) use ($orders, &$matchedVendors) {
                $filtered = $vendors->filter(function ($vendor) use ($orders) {
                    return $orders->every(function ($item) use ($vendor) {
                        $tot_stock = $vendor->vendorProductPrices
                            ->where('product_variation_id', $item['item_variant_id'])
                            ->sum('units_in_stock');
                        $product_used_stock = $vendor->assignedOrders()
                            ->where('item_type', Product::class)
                            ->where('item_variant_id', $item['item_variant_id'])
                            ->sum('quantity');
                        $package_used_stock = $vendor->assignedOrders()
                            ->with([
                                'item'
                                =>
                                fn($itm)
                                =>
                                $itm->with([
                                    'packageProducts'
                                    =>
                                    fn($i) => $i->where('product_variation_id', $item['item_variant_id'])
                                ])
                            ])
                            ->where('item_type', Package::class)
                            ->get()
                            ->sum(function ($item) {
                                return $item->quantity * $item->item->packageProducts->count();
                            });
                        $tot_used_stock = $product_used_stock + $package_used_stock;

                        /* if (in_array($vendor->id,[11,10])) {
                            Log::info([
                                'vendor_id' => $vendor->id, 
                                'variant_id' => $item['item_variant_id'], 
                                'tot_stock' => $tot_stock , 
                                'product_used_stock' => $product_used_stock, 
                                'package_used_stock' => $package_used_stock, 
                                'qty' => $item['quantity']
                            ]);
                        } */
                        return ($tot_stock - $tot_used_stock) >= $item['quantity'];
                    });
                });
                $matchedVendors = $matchedVendors->merge($filtered);
            });
        return $matchedVendors;
    }
    
    function assignOrderToVendor($vendor, $order, $order_items_ids) {
        // $order_items_ids = $request->order_items_ids;
        /* if ($order->is_order_completely_assigned) {
            throw new AssignOrderException('This order has already been assigned');
        } */

        /**
         * verifying that wether all incoming order_items_is belongs to this order
         */
        $order_qry = $order->orderItems();
        $order_items = $order_qry->whereIn('id', $order_items_ids)
            ->get();
        if ($order_items->count() != count($order_items_ids)) {
            throw new AssignOrderException('Order items does not exists in this order');
        }

        // $AAV_service = new AssignOrderToVendorService;
        $this->vendor_id = $vendor->id;
        // return $request->order_items_ids;
        $res = $this->fetchEligibleVendors($order_items_ids);
        $product_item_variant_id_w_quantity = $this->product_item_variant_id_w_quantity;
        /**
         * rechecking that this vendor have sufficient stock to meet this order items
         */
        if (count($res) <= 0) {
            throw new AssignOrderException('Assignment failed: vendor inventory is insufficient for these items.');
        }
        DB::transaction(function () use ($order, $order_items_ids, $vendor, $product_item_variant_id_w_quantity) {
            $order->orderItems()->whereIn('id', $order_items_ids)->update(['assigned_vendor_id' => $vendor->id]);
            $order->refresh();
            $all_order_hasBeen_assigned = $order->orderItems->whereNull('assigned_vendor_id')->isEmpty();
            if ($all_order_hasBeen_assigned) {
                $order->update(['is_order_completely_assigned' => true]);
            }
        });
        return $order_items->map(function ($item) {
            return [
                'item_name' => $item['item_name'],
                'item_price' => (float)$item['price'],
                'quantity' => (int)$item['quantity'],
                'sub_total' => (float) $item['total']
            ];
        });
    }
}
