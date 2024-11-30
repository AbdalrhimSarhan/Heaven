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
            'mobile'=>'required|numeric|digits:10',
            'image'=>'sometimes|nullable|image',
            'location'=>'required|string|max:100',
        ];
    }
    public function messages(){
        return [
            'first_name.required' => 'First Name is required',
            'last_name.required' => 'Last Name is required',
            'mobile.numeric' => 'Mobile number is required',
            'location.required' => 'Location is required',
        ];
    }
}
