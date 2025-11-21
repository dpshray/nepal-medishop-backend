<?php

namespace App\Http\Controllers\Api\V1\Vendor\Order\BatchAssign;

use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Models\Product;
use App\Models\ProductVariation;
use App\Models\Purchase\OrderItem;
use App\Models\purchase\OrderItemBatchNumber;
use Illuminate\Http\Request;

class VendorBatchAssignController extends Controller
{
    //
    function assignbatch(OrderItem $orderItem, Request $request)
    {
        $request->validate([
            'batchs' => 'array|required|min:1',
            'batch.*.id' => 'required|exists:vendorproductprices,id',
            'batch.*.quantity' => 'required|integer'
        ]);
        if ($orderItem->item_type == Product::class) {
            foreach ($request->batchs as $batch) {
                $order_batch = OrderItemBatchNumber::create([
                    'order_item_id' => $orderItem->id,
                    'vendor_product_price_id' => $batch['id'],
                    'product_variation_id' => $orderItem->item_variant_id,
                    'quantity' => $batch['quantity'],
                ]);
            }
        } else if ($orderItem->item_type == Package::class) {
            $package = Package::with(['packageProducts'])->where('slug', $orderItem->item_slug)->first();

            foreach ($package->packageProducts as $Product) {
                foreach ($request->batchs as $batch) {
                    // Multiply batch quantity × items inside package
                    $calculatedQty = $batch['quantity'] + $Product->quantity;

                    OrderItemBatchNumber::create([
                        'order_item_id'           => $orderItem->id,
                        'vendor_product_price_id' => $batch['id'],
                        'product_variation_id'    => $Product->product_variation_id,
                        'quantity'                => $calculatedQty,
                    ]);
                }
            }
        }
    }
}
