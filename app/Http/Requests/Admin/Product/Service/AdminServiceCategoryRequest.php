<?php

namespace App\Http\Requests\Admin\Product\Service;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;

class AdminServiceCategoryRequest extends FormRequest
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
        $id = null;
        if ($this->service_category) {
            $id = $this->service_category->id;
        }
        // Log::info($this->service_category);
        return [
            'name' => 'required|string|max:255|unique:service_categories,name,'.$id,
        ];
    }
}
