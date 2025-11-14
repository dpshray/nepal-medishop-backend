<?php

namespace App\Http\Requests\Admin\Product;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;

class AdminProductGenericNameRequest extends FormRequest
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
        $generic_product_name = $this->generic_product_name;
        $id = null;
        if ($generic_product_name) {
            $id = $generic_product_name->id;
        }
        return [
            'status' => 'sometimes|nullable|boolean',
            'name' => 'required|string|max:255|unique:generic_product_names,name,'.$id,
        ];
    }
}
