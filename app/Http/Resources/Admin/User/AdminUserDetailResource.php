<?php

namespace App\Http\Resources\Admin\User;

use App\Models\Package;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminUserDetailResource extends JsonResource
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
            'id' => $this->id,
            'uuid' => $this->uuid,
            'name' => $this->name,
            'email' => $this->email,
            'mobile_number' => $this->mobile_number,
            'status' => (bool) $this->status,
            'email_verified' => $this->email_verified_at ? 'Verified' : 'Not Verified',

            'orders' => $this->whenLoaded('orders', function () {
                return $this->orders->map(function ($order) {
                    return [
                        'order_id' => $order->id,
                        'order_code' => $order->order_code,
                        'price' => (float) $order->price,
                        'payment_method' => $order->payment_method,
                        'payment_status' => $order->payment_status,
                        'order_address' => $order->address,
                        'status' => $order->status,
                        'created_at' => $order->created_at->format('Y-m-d H:i:s'),
                        'order_items_detail' => $order->orderItems->map(function ($item) {
                            return [
                                'item_type' => class_basename($item->item_type),
                                'product_name' => $item->product?->name,
                                'variant_name' => $item->productVariant?->name,
                                'quantity' => (int) $item->quantity,
                                'price' => (float) $item->price,
                                'total' => (float) $item->total,
                                'featured_image' => $item->product?->getFirstMediaUrl(Product::PRODUCT_FEATURE),
                                'gallery_images' => $item->product?->getFirstMediaUrl(Product::PRODUCT_GALLERY)
                            ];
                        }),
                    ];
                });
            }),

            'user_favourite' => $this->whenLoaded('userlikes', function () {
                return $this->userlikes->map(function ($like) {
                    $item = $like->likable;
                    return [
                        'type' => class_basename($like->likable_type), // Product or Package
                        'id' => $like->likable_id,
                        'name' => $item?->name,
                        'slug'=>$item?->slug,
                        'description'=>$item?->description,
                        'featured_image' => $item instanceof Product
                            ? $item->getFirstMediaUrl(Product::PRODUCT_FEATURE)
                            : ($item instanceof Package
                                ? $item->getFirstMediaUrl(Package::PACKAGE_FEATURED)
                                : null),
                    ];
                });
            }),

            'user_wishlist' => $this->whenLoaded('wishlist', function () {
                return $this->wishlist->map(function ($wish) {
                    $item = $wish->wishable;
                    return [
                        'type' => class_basename($wish->wishable_type), // Product or Package
                        'id' => $wish->wishable_id,
                        'name' => $item?->name,
                        'slug'=>$item?->slug,
                        'description'=>$item?->description,
                        'featured_image' => $item instanceof Product
                            ? $item->getFirstMediaUrl(Product::PRODUCT_FEATURE)
                            : ($item instanceof Package
                                ? $item->getFirstMediaUrl(Package::PACKAGE_FEATURED)
                                : null),
                    ];
                });
            }),
            'User_cart' => $this->whenLoaded('cart', function () {
                return $this->cart->map(function ($cartItem) {
                    return [
                        'cart_id' => $cartItem->id,
                        'item_name' => $cartItem->item_name,
                        'item_slug' => $cartItem->item_slug,
                        'brand_name' => $cartItem->brand_name,
                        'variant_name' => $cartItem->variant_name,
                        'image' => $cartItem->image,
                        'quantity' => (int) $cartItem->quantity,
                        'price' => (float) $cartItem->price,
                        'subtotal' => (float) $cartItem->subtotal,
                    ];
                });
            })
        ];
    }
}
