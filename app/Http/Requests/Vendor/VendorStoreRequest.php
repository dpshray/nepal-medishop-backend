<?php

namespace App\Http\Requests\Vendor;

use Illuminate\Foundation\Http\FormRequest;

class VendorStoreRequest extends FormRequest
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
            'name' => 'required',
            'email' => 'required',
            'mobile_number' => 'required',
            'store_name' => 'required|max:255',
            'store_description' => 'required',
            'store_description' => 'required',
            'location' => 'required',
            'country' => 'required',
            'state' => 'required',
            'district' => 'required',
            'municipality' => 'required',
            'postal_code' => 'required',
            'bank_name' => 'required',
            'bank_account_holder_name' => 'required',
            'bank_account_number' => 'required',
            'vendor_citizenship_card' => ['required'],
            'vendor_citizenship_card.*' => ['image'],
            'vendor_business_license' => ['required'],
            'vendor_business_license.*' => ['image'],
            'vendor_tax_certificate' => ['required'],
            'vendor_tax_certificate.*' => ['image'],
        ];
    }

    public function messages()
    {
        return [
            'vendor_citizenship_card.array' => 'Citizenship card is required',
            'vendor_business_license.array' => 'Business license is required',
            'vendor_tax_certificate.array' => 'Tax certificate is required',
        ];
    }
}
