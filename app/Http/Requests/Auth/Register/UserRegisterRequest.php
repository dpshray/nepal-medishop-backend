<?php

namespace App\Http\Requests\Auth\Register;

use Illuminate\Foundation\Http\FormRequest;

class UserRegisterRequest extends FormRequest
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
            'name' => 'required|string|max:255|max:255',
            'mobile_number'=>'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email|max:255',
            'password' => 'required|string|min:8|confirmed|max:255',
            'image'=>'sometimes|file|mimes:png,jpg',
        ];
    }

    public function messages(){
        return [
            // 'email.unique' => 'Email has already been taken.'
        ];
    }
}
