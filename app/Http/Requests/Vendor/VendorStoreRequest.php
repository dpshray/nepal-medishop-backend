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
        $user_uuid = $this->vendor; #this is actually user id
        if($user_uuid){
            $user_id = User::where('uuid', $user_uuid)->firstOrFail()->id;
        }
        $rules = [
            'name' => 'required',
            'email' => 'required|email|unique:users,email,'.$user_id,
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
            'is_verified' => 'sometimes|between:0,1'
        ];
        if ($user_id == null) {
            $rules = array_merge($rules, [
                'vendor_citizenship_card' => ['required'],
                'vendor_citizenship_card.*' => ['image'],
                'vendor_business_license' => ['required'],
                'vendor_business_license.*' => ['image'],
                'vendor_tax_certificate' => ['required'],
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
}
