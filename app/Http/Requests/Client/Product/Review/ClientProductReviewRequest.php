<?php

namespace App\Http\Requests\Client\Product\Review;

use Illuminate\Foundation\Http\FormRequest;

class ClientProductReviewRequest extends FormRequest
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
            'review' => 'required',
            'rating' => 'required|numeric|between:1,5'
        ];
    }
}
