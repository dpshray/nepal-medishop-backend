<?php

namespace App\Http\Requests\Admin\Package;

use Illuminate\Foundation\Http\FormRequest;

class PackageStoreRequest extends FormRequest
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
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'discount_percent' => 'nullable|numeric|min:0|max:100',
            'start_timestamps' => 'nullable|date',
            'end_timestamps' => 'nullable|date|after_or_equal:start_timestamps',
            'status' => 'required|boolean',

            //product
            'products' => 'required|array|min:1',
            'products.*.product_variation_id' => 'required|integer|exists:product_variations,id',
            'products.*.quantity' => 'required|integer|min:1',

            //image
            'featured_image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'gallery_images' => 'nullable|array',
            'gallery_images.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:2048',

        ];
    }
    protected function prepareForValidation()
    {
        $this->merge([
            'status' => filter_var($this->status, FILTER_VALIDATE_BOOLEAN),
        ]);
        if ($this->has('products') && is_string($this->products)) {
            $decodedProducts = json_decode($this->products, true);

            if (!is_array($decodedProducts)) {
                $decodedProducts = [];
            }

            $this->merge([
                'products' => $decodedProducts,
            ]);
        }
        if ($this->has('gallery_images') && is_string($this->gallery_images)) {
            $decodedGallery = json_decode($this->gallery_images, true);
            if (!is_array($decodedGallery)) $decodedGallery = [];
            $this->merge([
                'gallery_images' => $decodedGallery,
            ]);
        }
    }
}
