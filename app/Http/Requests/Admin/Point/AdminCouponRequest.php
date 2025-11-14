<?php

namespace App\Http\Requests\Admin\Point;

use Illuminate\Foundation\Http\FormRequest;

class AdminCouponRequest extends FormRequest
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
            "coupon_code" => 'required',
            "description" => 'required',
            "discount_percent" => 'required',
            "is_active" => 'required',
            "start_date" => 'required',
            "end_date" => 'required'
        ];
    }
}
