<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class AdminBannerRequest extends FormRequest
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
        $rules =  [
            'order' => 'sometimes|nullable|integer',
            'title' => 'sometimes|nullable|max:255',
            'description' => 'sometimes|nullable',
            'url' => 'sometimes|nullable|max:255',
        ];

        $create = [
            'image' => 'required|image'
        ];
        
        $update = [
            'image' => 'sometimes|nullable|image'
        ];
        return $this->banner ? [...$rules, ...$update] : [...$rules, ...$create];
    }

    protected function prepareForValidation(): void
    {
        if (empty($this->banner) && empty($this->order)) { # if create request and no order present
            $this->merge([
                'order' => 1,
            ]);
        }
    }
}
