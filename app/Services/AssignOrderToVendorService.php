<?php

namespace App\Services;

use App\Models\Package;
use App\Models\Product;
use App\Models\Purchase\Order;
use App\Models\Purchase\OrderItem;
use App\Models\Vendor;
use Illuminate\Support\Facades\Log;

class AssignOrderToVendorService
{
    public $order = null;
    public $vendor_id = null; public $search = null;
    public $product_item_variant_id_w_quantity = null;

    function fetchEligibleVendors(array $order_item_ids, Order $order = null)
    {
        $vendor_id = $this->vendor_id;
        $orders = $this->transformOrderItemsIntoProducts($order_item_ids);
        $this->product_item_variant_id_w_quantity = $orders;
        /**
         * finally finding vendors that are eligible to
         * assign above order items
         */
        Log::info($orders);
        Log::info('**************************');

        $matchedVendors = collect(); // final result collection
        Vendor::with(['vendorProductPrices.ProductVendor.associatedVendor', 'user'])
            ->when($vendor_id, fn($qry) => $qry->where('id', $vendor_id))
            ->when($this->search, fn($qry) => $qry->whereLike('store_name', '%'.$this->search.'%'))
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
                            ->sum(function($item){
                                return $item->quantity * $item->item->packageProducts->count();
                            });
                        $tot_used_stock = $product_used_stock + $package_used_stock;
                            
                        if (in_array($vendor->id,[11,10])) {
                            Log::info([
                                'vendor_id' => $vendor->id, 
                                'variant_id' => $item['item_variant_id'], 
                                'tot_stock' => $tot_stock , 
                                'product_used_stock' => $product_used_stock, 
                                'package_used_stock' => $package_used_stock, 
                                'qty' => $item['quantity']
                            ]);
                        }
                        return ($tot_stock - $tot_used_stock) >= $item['quantity'];
                    });
                });
                $matchedVendors = $matchedVendors->merge($filtered);
            });
        return $matchedVendors;
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
}
