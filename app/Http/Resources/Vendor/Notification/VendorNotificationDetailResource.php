<?php

namespace App\Http\Resources\Vendor\Notification;

use App\Models\ProductVendor;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VendorNotificationDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // return parent::toArray($request);
        return [

        ];
    }

    private function vendorProductApproval($notification)
    {
        $product_vendor_id = $notification->data['product_vendor_id'];
        $product_vendor = ProductVendor::with(['vendor.user', 'product', 'vendorPrices.variation'])->findOrFail($product_vendor_id);
        $product = $product_vendor->product;
        $variant_prices = $product_vendor->vendorPrices;
        return [
            'product_name' => $product->name,
            'product_added_on' => $product_vendor->created_at->format('Y/m/d'),
            'vendor_name' => $product_vendor->vendor->user->name,
            'vendor_store_name' => $product_vendor->vendor->store_name,
            'is_product_approved' => (bool)$product_vendor->is_approved,
            'variants' => $variant_prices->map(function ($VP) {
                return [
                    'variant_name' => $VP->variation->name,
                    'price' => (float)$VP->price
                ];
            })
        ];
    }
}
