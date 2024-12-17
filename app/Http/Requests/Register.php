<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class Register extends FormRequest
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
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'mobile' => [
                'required',
                'numeric',
                'digits:10',
                'regex:/^09[0-9]{8}$/', // Mobile must start with "09" and have 10 digits
                'unique:users,mobile'
            ],
            'image' => 'sometimes|nullable|image',
            'location' => 'required|string|max:100',
        ];
    }

    /**
     * Get custom error messages for validation rules.
     */
    public function messages()
    {
        return [
            'first_name.required' => 'First Name is required',
            'last_name.required' => 'Last Name is required',
            'mobile.numeric' => 'Mobile number must be numeric.',
            'mobile.digits' => 'Mobile number must be exactly 10 digits.',
            'mobile.regex' => 'Mobile number must start with "09" and be followed by 8 digits.',
            'mobile.unique' => 'The provided mobile number is already registered.',
            'location.required' => 'Location is required',
        ];
    }
}
