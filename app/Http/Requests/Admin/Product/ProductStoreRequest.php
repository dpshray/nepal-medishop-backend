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
        $product_id = null;
        if ($this->product) {
            $product_id = $this->product->id;
        }
        $rules = [
            'brand_id' => 'required|exists:brands,id',
            'name' => 'required|unique:products,name,' . $product_id,
            'description' => 'sometimes',
            'categories' => 'required|array',
            'categories.*' => 'distinct|exists:categories,id',
            'tags' => 'required|array',
            'tags.*' => 'distinct|exists:tags,id',
            'variations' => 'required|array',
            'variations.*.size_value' => 'required|numeric',
            'variations.*.size_unit' => 'required|string',
            'variations.*.platform_price' => 'required|numeric',
            'variations.*.platform_discount_price' => 'sometimes|nullable|numeric',
        ];
        if ($product_id == null) {
            $rules = array_merge($rules, [
                'featured_image' => 'required|image|exclude',
                'gallery_images' => 'required|array|exclude',
                'gallery_images.*' => 'image'
            ]);
        }
        return $rules;
    }

    function messages(){
        return [
            'categories.*.distinct' => 'Categories has a duplicate values.',
            'tags.*.distinct' => 'Tags has a duplicate values.',
        ];
    }

    function prepareForValidation()
    {
        $converted = array_map(function ($item) {
            return json_decode($item, true);
        }, $this->variations);
        $this->merge(['variations' => $converted]);
    }
}
