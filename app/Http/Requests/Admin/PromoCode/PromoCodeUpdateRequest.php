<?php

namespace App\Http\Requests\Admin\PromoCode;

use Illuminate\Foundation\Http\FormRequest;

class PromoCodeUpdateRequest extends FormRequest
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
            //
            'code'=>'required|string',
            'discount_percent'=>'required|numeric|decimal:0,2|min:0|max:100',
            'start_date'=>'required|date',
            'end_date'=>'required|date',
            'is_active'=>'required|boolean',
            'description'=>'sometimes|nullable|string'
        ];
    }
}
