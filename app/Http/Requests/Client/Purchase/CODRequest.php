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
            'payment_method' => ['required', Rule::enum(PaymentMethodEnum::class)],
            'address' => 'required',
            'description' => 'nullable',
            'products.*' => 'sometimes|nullable|array',
            'products.*.product_slug' => 'required|exists:products,slug',
            'products.*.variant_id' => 'required|exists:product_variations,id',
            'products.*.quantity' => 'required|numeric',
            'packages.*' => 'sometimes|nullable|array',
            'packages.*.package_slug' => 'required|exists:packages,slug',
            'packages.*.quantity' => 'required|numeric',
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
}
