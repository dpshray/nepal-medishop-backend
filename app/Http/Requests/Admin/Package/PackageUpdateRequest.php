<?php

namespace App\Http\Requests\Admin\Package;

use Illuminate\Foundation\Http\FormRequest;

class PackageUpdateRequest extends FormRequest
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
            'name' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'price' => 'sometimes|required|numeric|min:0',
            'discount_percent' => 'nullable|numeric|min:0|max:100',
            'start_timestamps' => 'nullable|date',
            'end_timestamps' => 'nullable|date|after_or_equal:start_timestamps',
            'status' => 'sometimes|required|boolean',

            // For many-to-many relation (product_variation_id + quantity)
            'products' => 'nullable|array',
            'products.*.product_variation_id' => 'required_with:products|integer|exists:product_variations,id',
            'products.*.quantity' => 'required_with:products|integer|min:1',

            // Media files
            'featured_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'gallery_images' => 'nullable|array',
            'gallery_images.*' => 'image|mimes:jpg,jpeg,png,webp|max:2048',
        ];
    }
    protected function prepareForValidation(): void
    {
        // Decode JSON string for products (if sent as text in multipart/form-data)
        if ($this->has('products') && is_string($this->products)) {
            $this->merge([
                'products' => json_decode($this->products, true) ?? [],
            ]);
        }

        // Convert status to boolean if sent as "true"/"false"/"1"/"0"
        if ($this->has('status')) {
            $this->merge([
                'status' => filter_var($this->status, FILTER_VALIDATE_BOOLEAN),
            ]);
        }
    }
}
