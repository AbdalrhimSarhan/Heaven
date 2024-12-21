<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfile extends FormRequest
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
            'first_name' => 'sometimes|string|max:100',
            'last_name' => 'sometimes|string|max:100',
            'mobile'=>'sometimes|numeric|digits:10',
            'image'=>'sometimes|nullable|image',
            'location'=>'sometimes|string|max:100',
        ];
    }

    public function attributes(): array
    {
        return [
            'first_name' => __('message.first_name'),
            'last_name' => __('message.last_name'),
            'mobile' => __('message.mobile'),
            'image' => __('message.image_profile'),
            'location' => __('message.location'),
        ];
    }

}
