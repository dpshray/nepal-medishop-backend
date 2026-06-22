<?php

namespace App\Http\Requests\Vendor;

use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;

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
        $user_id = null;
        if ($this->user) {
            $user_id = $this->user->id;
        }
        $rules = [
            'name' => 'required',
            'password' => 'sometimes|nullable|min:6',
            'mobile_number' => 'required',
            'store_name' => 'required|max:255',
            'store_description' => 'sometimes|nullable',
            'location' => 'sometimes|nullable',
            'country' => 'sometimes|nullable',
            'state' => 'sometimes|nullable',
            'district' => 'sometimes|nullable',
            'municipality' => 'sometimes|nullable',
            'postal_code' => 'sometimes|nullable',
            'bank_name' => 'sometimes|nullable',
            'bank_account_holder_name' => 'sometimes|nullable',
            'bank_account_number' => 'sometimes|nullable',
            'account_status' => 'sometimes|nullable|boolean',
            'commission_percentage' => 'sometimes|nullable|numeric'
        ];
        if ($user_id == null) {
            $rules = array_merge($rules, [
                'email' => 'required|email|unique:users,email',
                'vendor_citizenship_card' => ['sometimes|nullable'],
                'vendor_citizenship_card.*' => ['image'],
                'vendor_business_license' => ['sometimes|nullable'],
                'vendor_business_license.*' => ['image'],
                'vendor_tax_certificate' => ['sometimes|nullable'],
                'vendor_tax_certificate.*' => ['image'],
            ]);
        }
        return $rules;
    }

    public function messages()
    {
        return [
            'vendor_citizenship_card.*.required' => 'Citizenship card is required',
            'vendor_citizenship_card.*.image' => 'Citizenship card is must be image',
            'vendor_business_license.*.required' => 'Business license is required',
            'vendor_business_license.*.image' => 'Business license is must be image',
            'vendor_tax_certificate.*.required' => 'Tax certificate is required',
            'vendor_tax_certificate.*.image' => 'Tax certificate is must be image',
        ];
    }

    function prepareForValidation()
    {
        return $this->merge([
            'account_status' => filter_var($this->account_status, FILTER_VALIDATE_BOOLEAN) ? 1 : 0
        ]);
    }
}
