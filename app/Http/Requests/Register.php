<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class  Register extends FormRequest
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

    public function attributes(){
        return[
            'first_name'=>__('message.first_name'),
            'last_name'=>__('message.last_name'),
            'image' => __('message.image_profile'),
            'mobile'=>__('message.mobile'),
            'location'=>__('message.location'),
        ];
    }


}
