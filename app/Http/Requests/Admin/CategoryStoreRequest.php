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
        // Log::info($this->category);
        if ($this->category) { #edit request
            $category_id = $this->category->id;
            $rule = [
                'name' => 'required|max:255|unique:categories,name,' . $category_id,
                'menu_order'=>'sometimes|nullable|integer',
                'image' => 'sometimes|nullable|image|exclude',
                'discount_percent' => 'sometimes|nullable|between:0,100'
            ];
        } else { #create
            $rule = [
                'name' => 'required|max:255|unique:categories,name',
                'menu_order'=>'sometimes|nullable|integer',
                'image' => 'required|image|exclude',
                'discount_percent' => 'sometimes|nullable|between:0,100'
            ];
        }
        return $rule;
    }
}
