<?php

namespace App\Http\Resources\Admin\Purchase\Kitbag;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminKitbagDetailResource extends JsonResource
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
            'created_at' => $this->created_at->format('Y/m/d'),
            'user'=>[
                'name'=>$this->user->name,
                'email'=>$this->user->email,
            ],
            'items' => $this->kitbagItems->map(function($item){
                ['price' => $price, 'previous_price' => $previous_price] = $item->variation->original_price;

                return [
                    "product_name" => $item->product->name,
                    'image' => $item->product->getFirstMediaUrl(Product::PRODUCT_FEATURE),
                    'quantity' => (integer) $item->quantity,
                    'variant' => [
                        "name" => $item->variation->name,
                        "size_value" => (float) $item->variation->size_value,
                        "size_unit" => $item->variation->size_unit,
                        "price" => $price,
                        "previous_price" => $previous_price,
                    ]
                ];
            })
        ];
    }
}
