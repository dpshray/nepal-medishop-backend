<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class BrandStoreRequest extends FormRequest
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
        $brand_id = null;
        $rule = [];
        if ($this->brand) { #edit request
            $brand_id = $this->brand->id;
            $rule = [
                'name' => 'required|max:255|unique:brands,name,' . $brand_id,
                'is_featured' => 'sometimes|boolean',
                'is_popular' => 'sometimes|boolean',
                'image' => 'sometimes|nullable|image|exclude'
            ];
        } else{ #create
            $rule = [
                'name' => 'required|max:255|unique:brands,name',
                'is_featured' => 'sometimes|boolean',
                'is_popular' => 'sometimes|boolean',
                'image' => 'required|image|exclude'
            ];
        }
        return $rule;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_popular' => filter_var($this->is_popular, FILTER_VALIDATE_BOOLEAN),
            'is_featured' => filter_var($this->is_featured, FILTER_VALIDATE_BOOLEAN),
        ]);
    }
}
