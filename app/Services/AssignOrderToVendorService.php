<?php

namespace App\Services;

use App\Enums\Purchase\OrderItemStatusEnum;
use App\Enums\Purchase\OrderStatusEnum;
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
    public $vendor_id = null;
    public $search = null;
    public $product_item_variant_id_w_quantity = null;

    /**
     * null = check entire order
     * null = check all vendors
     * false = ANY, true = ALL
    */
    function checkVendorFulfillment(Order $order, array|null $order_item_ids = null, Vendor|null $specificVendor = null, bool $mustFulfillAll = false) {
        /** ---------------------------------------------------
         * STEP 1: Build required variations (required qty per variant)
         * ----------------------------------------------------*/
        $query = $order->orderItemProducts();

        if ($order_item_ids) {
            $query->whereIn('order_item_id', $order_item_ids);
        }

        $ordered_items = $query->with('variation.product')
            ->get()
            ->groupBy('product_variation_id')
            ->map(fn($items) => [
                'variant_id'   => $items->first()->product_variation_id,
                'required_qty' => $items->sum('quantity'),
                'variant_name' => optional($items->first()->variation)->name,
                'product_name' => optional(optional($items->first()->variation)->product)->name,
            ])
            ->values();

        $required_variations = $ordered_items->pluck('variant_id')->all();


        /** ---------------------------------------------------
         * STEP 2: Prepare Vendor Query (all vendors or only one)
         * ----------------------------------------------------*/
        $vendorQuery = Vendor::verifiedAndActive()
            ->whereHas(
                'vendorProductPrices',
                fn($q) =>
                $q->whereIn('product_variation_id', $required_variations)
            )
            ->with([
                'vendorProductPrices' => fn($q) =>
                $q->with(['orderItemProductBatchNumber','orders'])
                    ->whereIn('product_variation_id', $required_variations)
                    ->whereRelation('ProductVendor', 'is_approved', true),

                'orderItemProducts' => fn($q) =>
                $q->whereIn('product_variation_id', $required_variations)
                    ->whereRelation('orderItem', 'assigned_vendor_id', '<>', null)
                    ->whereRelation('order','status', '<>',OrderStatusEnum::CANCELLED),
            ]);

        if ($specificVendor) {
            $vendorQuery->where('id', $specificVendor->id);
        }


        /** ---------------------------------------------------
         * MODE: Single Vendor (must fulfill ALL items)
         * ----------------------------------------------------*/
        if ($specificVendor && $mustFulfillAll) {

            $vendor = $vendorQuery->first();
            if (!$vendor) {
                return ['eligible' => false, 'failed_items' => []];
            }

            $reserved_by_variant = $vendor->orderItemProducts
                ->groupBy('product_variation_id')->map->sum('quantity');

            $failed_items = [];

            foreach ($ordered_items as $order_item) {

                $variant_id   = $order_item['variant_id'];
                $required_qty = $order_item['required_qty'];

                $variant_stock = $vendor->vendorProductPrices
                    ->where('product_variation_id', $variant_id);

                if ($variant_stock->isEmpty()) {
                    $failed_items[] = [
                        'variant_name'    => $order_item['variant_name'],
                        'product_name'    => $order_item['product_name'],
                        'reason'        => 'Vendor does not sell this variant',
                        'required_qty'  => $required_qty,
                        'available_qty' => 0,
                    ];
                    continue;
                }

                $reserved = $reserved_by_variant[$variant_id] ?? 0;
                $actual_units = $variant_stock->sum('units_in_stock') - $reserved;

                if ($actual_units < $required_qty) {
                    $failed_items[] = [
                        'variant_name'    => $order_item['variant_name'],
                        'product_name'    => $order_item['product_name'],
                        'reason'        => 'Insufficient stock',
                        'required_qty'  => $required_qty,
                        'available_qty' => $actual_units,
                    ];
                }
            }

            return [
                'eligible'     => empty($failed_items),
                'failed_items' => $failed_items,
            ];
        }


        /** ---------------------------------------------------
         * MODE: Check all vendors (ANY one variant must be available)
         * ----------------------------------------------------*/
        $passing_vendors = collect();

        $vendorQuery->chunk(200, function ($vendors) use (&$passing_vendors, $ordered_items) {

            foreach ($vendors as $vendor) {

                $reserved_by_variant = $vendor->orderItemProducts
                    ->groupBy('product_variation_id')->map->sum('quantity');

                $can_supply_any = false;

                foreach ($ordered_items as $order_item) {

                    $variant_id   = $order_item['variant_id'];
                    $required_qty = $order_item['required_qty'];

                    $variant_stock = $vendor->vendorProductPrices
                        ->where('product_variation_id', $variant_id);

                    if ($variant_stock->isEmpty()) {
                        continue;
                    }

                    $reserved = $reserved_by_variant[$variant_id] ?? 0;
                    $actual_units = $variant_stock->sum('units_in_stock') - $reserved;
                    /* if ($vendor->id == 2) {                        
                        Log::info([
                            $variant_stock->sum('units_in_stock'),
                            $reserved
                        ]);
                    } */

                    if ($actual_units >= $required_qty) {
                        $can_supply_any = true;
                        break;
                    }
                }

                if ($can_supply_any) {
                    $passing_vendors->push($vendor);
                }
            }
        });

        return $passing_vendors->values();
    }

    function assignOrderToVendor($vendor, $order, $order_items_ids) {
        // $order_items_ids = $request->order_items_ids;
        /* if ($order->is_order_completely_assigned) {
            throw new AssignOrderException('This order has already been assigned');
        } */
        $order_item_not_exists_in_order = $order->orderItems->whereIn('id', $order_items_ids)->count() != count($order_items_ids);
        if ($order_item_not_exists_in_order) {
            throw new AssignOrderException('Order item does not belong to this order.');
        }
        /**
         * verifying that wether all incoming order_items_is belongs to this order
         */
        $order_qry = $order->orderItems();
        $order_items = $order_qry->whereIn('id', $order_items_ids)
            ->get();
        /* if ($order_items->count() != count($order_items_ids)) {
            throw new AssignOrderException('Order items does not exists in this order');
        } */

        // $AAV_service = new AssignOrderToVendorService;
        $this->vendor_id = $vendor->id;
        // return $request->order_items_ids;
        $res = $this->checkVendorFulfillment($order, $order_items_ids, $vendor, true);
        // $product_item_variant_id_w_quantity = $this->product_item_variant_id_w_quantity;
        /**
         * rechecking that this vendor have sufficient stock to meet this order items
         */
        if (count($res) <= 0) {
            throw new AssignOrderException('Assignment failed: vendor inventory is insufficient for these items.');
        }
        DB::transaction(function () use ($order, $order_items_ids, $vendor, /* $product_item_variant_id_w_quantity */) {
            /* $order->orderItems()->whereIn('id', $order_items_ids)->update(['assigned_vendor_id' => $vendor->id, 'status' => OrderItemStatusEnum::ASSIGNED]);
            $order->refresh();
            $all_order_hasBeen_assigned = $order->orderItems->whereNull('assigned_vendor_id')->isEmpty();
            if ($all_order_hasBeen_assigned) {
                $order->update(['is_order_completely_assigned' => true]);
            } */
            $order->orderItems()
                ->whereIn('id', $order_items_ids)
                ->update(['assigned_vendor_id' => $vendor->id, 'status' => OrderItemStatusEnum::ASSIGNED->value]);
            $order->refresh();
            $no_pending_order_item_found = $order->orderItems()->where('status', OrderItemStatusEnum::PENDING->value)->doesntExist();
            if ($no_pending_order_item_found) {
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
