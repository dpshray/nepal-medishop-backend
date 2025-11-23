<?php

namespace App\Services;

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

    function fetchEligibleVendors(Order $order)
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
