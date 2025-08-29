<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
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
            'name' => 'required|string|max:30',
            'description' => 'required|string|max:250',
            'price' => 'required|numeric|min:0',
            'discount_price' => 'nullable|numeric|min:0|lt:price',
            'pattern' => 'required|string|max:20',
            'fabric' => 'required|string|max:20',
            'material' => 'required|string|max:20',

            // Category
            'categories' => 'required',

            // Product images
            'images' => 'sometimes|required|array|min:1',
            'images.*' => 'required|image|mimes:jpeg,png,jpg',

            // Variants
            'variant' => 'required|array|min:1',
            'variant.*.size' => 'required|string|max:10',
            'variant.*.color' => 'required|string|max:20',
            'variant.*.price' => 'required|numeric|min:0',
            'variant.*.discount_price' => 'nullable|numeric|min:0|lt:variant.*.price',
            'variant.*.stock' => 'required|integer|min:0',
            'variant.*.images' => 'sometimes|required|array|min:1',
            'variant.*.images.*' => 'required|image|mimes:jpeg,png,jpg',
        ];
    }
}
