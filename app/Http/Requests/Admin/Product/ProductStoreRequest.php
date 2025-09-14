<?php

namespace App\Http\Requests\Admin\Product;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;

class ProductStoreRequest extends FormRequest
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
            'brand_id' => 'required|exists:brands,id',
            'name' => 'required',
            'description' => 'sometimes',
            'categories' => 'required|array',
            'categories.*' => 'distinct|exists:categories,id',
            'tags' => 'required|array',
            'tags.*' => 'distinct|exists:tags,id',
            'variations' => 'required|array',
            'variations.*.size_value' => 'required|numeric',
            'variations.*.size_unit' => 'required|string',
            'variations.*.price' => 'required|numeric',
            'variations.*.discount_price' => 'nullable|numeric|lt:variations.*.price',
        ];
    }

    function messages(){
        return [
            'categories.*.distinct' => 'Categories has a duplicate values.',
            'tags.*.distinct' => 'Tags has a duplicate values.',
            'variations.*.discount.lt' => 'Discount price must be lower than actual price.',
            'variations.*.price.numeric' => 'Price must be in numeric.'
        ];
    }
}
