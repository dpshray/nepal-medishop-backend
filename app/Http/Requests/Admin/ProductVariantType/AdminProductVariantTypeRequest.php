<?php

namespace App\Http\Requests\Admin\ProductVariantType;

use Illuminate\Foundation\Http\FormRequest;

class AdminProductVariantTypeRequest extends FormRequest
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
        if ($this->isMethod('POST')) {
            return [
                'name' => 'required',
                'package_types' => 'required|array',
                'package_types.*.name' => 'required',
                'package_types.*.unit_types' => 'required|array',
                'package_types.*.unit_types.*.name' => 'required',
            ];
        } else {
            return [
                'name' => 'sometimes',
                'package_types' => 'sometimes|array',
                'package_types.*.uuid' => 'sometimes|exists:package_types,uuid',
                'package_types.*.name' => 'sometimes',
                'package_types.*.unit_types' => 'sometimes|array',
                'package_types.*.unit_types.*.uuid' => 'sometimes|exists:unit_types,uuid',
                'package_types.*.unit_types.*.name' => 'sometimes',
            ];
        }
    }
}
