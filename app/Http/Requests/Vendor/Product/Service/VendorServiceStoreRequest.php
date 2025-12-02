<?php

namespace App\Http\Requests\Vendor\Product\Service;

use Illuminate\Foundation\Http\FormRequest;

class VendorServiceStoreRequest extends FormRequest
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
            'is_available' => 'required|boolean',
            'service_id' => 'required|exists:services,id',
            'price' => 'required|numeric'
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_available' => $this->is_available ? filter_var($this->is_available, FILTER_VALIDATE_BOOLEAN) : true
        ]);
    }
}
