<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class AdminCategoryMenuRequest extends FormRequest
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
            'menu.*' => 'required|array',
            'menu.*.category_id' => 'required|distinct|exists:categories,id',
            'menu.*.menu_order' => 'required|distinct|integer'
        ];
    }

    function messages() {
        return [
            'menu.*.category_id.distinct' => 'Category has duplicate id.',
            'menu.*.category_id.exists' => 'Category does not exists.',
            'menu.*.menu_order.distinct' => 'Menu order has duplicate values.',
            'menu.*.menu_order.required' => 'Menu order is missing'
        ];
    }
}
