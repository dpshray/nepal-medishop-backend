<?php

namespace App\Http\Requests\Admin\Product;

use App\Enums\ProductUnitEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
            'health_condition' => 'required|array',
            'health_condition.*' => 'distinct|exists:health_conditions,id',
            'variations' => 'required|array',
            'variations.*.variant_name' => 'nullable|string',
            'variations.*.variant_stock' => 'required|numeric',
            'variations.*.variant_unit' => ['required', Rule::in(array_column(ProductUnitEnum::cases(), 'value'))],
            'variations.*.variant_price' => 'required|numeric',
            'variations.*.discount_percent' => 'sometimes|nullable|numeric',
            'variations.*.variant_batch_no' => 'required|string|max:255',
            'variations.*.variant_expiry_date' => 'required|date|date_format:Y-m-d',
            'variations.*.variant_form_type' => 'required|string',
            'variations.*.variant_package_type' => 'required|string',
            'variations.*.variant_package_size' => 'required|string',
            'variations.*.variant_strength' => 'required|string',
            'prescription_required' => 'sometimes',
            'discount_percent' => 'sometimes|nullable|numeric|lte:100',
            'generic_product_name_id' => 'required|exists:generic_product_names,id'
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

    function messages()
    {
        return [
            'categories.*.distinct' => 'Categories has a duplicate values.',
            'tags.*.distinct' => 'Tags has a duplicate values.',
            'variations.required' => 'variation is required.',
            'variations.*.variant_name.required' => 'Variant name is required.',
            'variations.*.variant_stock.required' => 'Variant stock value is required.',
            'variations.*.variant_unit.required' => 'Variant size unit is required.',
            'variations.*.variant_unit.in' => 'Variant size unit must be one of: ' . implode(', ', array_column(ProductUnitEnum::cases(), 'value')),
            'variations.*.variant_price.required' => 'Variant platform price is required.',
            'variations.*.variant_expiry_date.date' => 'The variant expiry date field must be a valid date.',
            'variations.*.variant_expiry_date.date_format' => 'Variant expiry date must be in Y-m-d format.',
            'health_condition.required' => 'Health condition is required',
            'health_condition.*.exists' => 'Health condition does not exists',
        ];
    }

    function prepareForValidation()
    {
        $converted = array_map(function ($item) {
            return is_array($item) ? $item : json_decode($item, true);
        }, $this->variations);
        $this->merge(['variations' => $converted]);

        $this->merge([
            'prescription_required' => filter_var($this->prescription_required, FILTER_VALIDATE_BOOLEAN) ? 1 : 0
        ]);
    }
}
