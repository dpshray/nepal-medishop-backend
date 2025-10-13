<?php

namespace App\Http\Requests\Admin\Package;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductToPackageRequest extends FormRequest
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
            //product
            'products' => 'required|array|min:1',
            'products.*.product_variation_id' => 'required|integer|exists:product_variations,id',
            'products.*.quantity' => 'required|integer|min:1',
        ];
    }
}
