<?php

namespace App\Http\Requests\Vendor;

use Illuminate\Foundation\Http\FormRequest;

class VendorProductStockStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'product_uuid' => 'required|exists:products,uuid',
            'variations' => 'required|array',
            'variations.*.product_variation_id' => 'required|exists:product_variations,id',
            'variations.*.units_in_stock' => 'required|numeric',
            'variations.*.price' => 'required|numeric',
        ];
    }
}
