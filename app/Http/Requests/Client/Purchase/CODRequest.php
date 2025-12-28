<?php

namespace App\Http\Requests\Client\Purchase;

use App\Enums\Purchase\PaymentMethodEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class CODRequest extends FormRequest
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
        $rules = [
            'payment_method' => ['required'],
            'address' => 'required',
            'latitude' => 'sometimes|nullable',
            'longitude' => 'sometimes|nullable',
            'description' => 'nullable',
            'products.*' => 'sometimes|nullable|array',
            'products.*.product_slug' => 'required|exists:products,slug',
            'products.*.variant_id' => 'required|exists:product_variations,id',
            'products.*.quantity' => 'required|numeric',
            'packages.*' => 'sometimes|nullable|array',
            'packages.*.package_slug' => 'required|exists:packages,slug',
            'packages.*.quantity' => 'required|numeric',
            'packages.*.prescription_image' => 'sometimes|nullable|image',
            'gift_wrap' => 'required|boolean',
            'gift_wrap_remarks' => 'nullable',
            'code' => 'sometimes|nullable|exists:coupon_codes,code'
        ];
        if (!Auth::user()) {
            $rules = [...$rules, ...[
                'name' => 'required',
                'email' => 'required',
                'mobile' => 'required',
            ]];
        }
        return $rules;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'gift_wrap' => filter_var($this->gift_wrap, FILTER_VALIDATE_BOOLEAN)
        ]);
    }
}
