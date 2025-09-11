<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;

class CategoryStoreRequest extends FormRequest
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
        $category_id = null;
        $rule = [];
        if ($this->category) { #edit request
            $category_id = $this->category->id;
            $rule = [
                'name' => 'required|max:255|unique:categories,name,' . $category_id,
                'image' => 'sometimes|nullable|image|exclude'
            ];
        } else { #create
            $rule = [
                'name' => 'required|max:255|unique:categories,name',
                'image' => 'required|image|exclude'
            ];
        }
        return $rule;
    }
}
