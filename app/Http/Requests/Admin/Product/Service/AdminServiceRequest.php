<?php

namespace App\Http\Requests\Admin\Product\Service;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;

class AdminServiceRequest extends FormRequest
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
        if ($this->service) {
            $id = $this->service->id;
        }
        return [
            'is_active' => 'sometimes|nullable|boolean',
            'name' => 'required|string|max:255|unique:services,name,'.$id,
            'description' => 'required|string',
            'test_requirements' => 'required|string',
            'price' => 'required|numeric',
            'discount_percent' => 'sometimes|nullable|numeric|lte:100',
            'category_id' => 'required|array',
            'category_id.*' => 'required|integer|exists:service_categories,id',
            'tag_id' => 'required|array',
            'tag_id.*' => 'required|integer|exists:service_tags,id',
            'image' => 'required|image'
        ];
    }

    function messages() {
        return [
            'category_id.*.exists' => 'The selected category id is invalid',
            'tag_id.*.exists' => 'The selected tag id is invalid.'
        ];
    }


    protected function prepareForValidation(): void
    {
        $data = [];
        if (gettype($this->category_id) == 'string') {
            $data['category_id'] = array_values(array_filter(explode(",", $this->category_id)));
        }
        if (gettype($this->tag_id) == 'string') {
            $data['tag_id'] = array_values(array_filter(explode(",", $this->tag_id)));
        }

        $this->merge([
            'is_active' => $this->is_active ? filter_var($this->is_active, FILTER_VALIDATE_BOOLEAN) : true,
            ...$data
        ]);
    }
}
