<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class TagStoreRequest extends FormRequest
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
        $tag_id = null;
        $rule = [];
        if ($this->tag) { #edit request
            $tag_id = $this->tag->id;
            $rule = [
                'name' => 'required|max:255|unique:tags,name,' . $tag_id,
            ];
        } else { #create
            $rule = [
                'name' => 'required|max:255|unique:tags,name',
            ];
        }
        return $rule;
    }
}
