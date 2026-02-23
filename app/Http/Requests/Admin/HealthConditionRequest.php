<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class HealthConditionRequest extends FormRequest
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

        $health_condition_id = null;
        $rule = [];
        if ($this->health_condition) { #edit request
            $health_condition_id = $this->health_condition->id;
            $rule = [
                'name' => 'required|string|max:255|unique:health_conditions,name,' . $health_condition_id,
                'description' => 'sometimes|nullable|string',
                'image' => 'sometimes|nullable|image|exclude'
            ];
        } else { #create
            $rule = [
                'name' => 'required|string|max:255|unique:health_conditions,name',
                'description' => 'sometimes|nullable|string',
                'image' => 'sometimes|nullable|image|exclude'
            ];
        }
        return $rule;
    }
}
