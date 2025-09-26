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
            'stock' => 'required|array',
            'stock.*.product_variation_id' => 'required|exists:product_variations,id',
            'stock.*.units_in_stock' => 'required|numeric',
            'stock.*.price' => 'required|numeric',
        ];
    }
}
