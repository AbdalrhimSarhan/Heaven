<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateStoreRequest extends FormRequest
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
            'name_en'=>'required|string|min:3|max:100',
            'name_ar'=>'required|string|min:3|max:100',
            'category_id'=>'required|integer|exists:categories,id',
            'image'=>'required|image',
            'location_en'=>'required|string|min:3|max:100',
            'location_ar'=>'required|string|min:3|max:100',
        ];
    }

    public function messages(){
        return[
            'name_en.required'=>'The name_en field is required.',
            'name_ar.required'=>'The name_ar field is required.',
            'category_id.required'=>'The category field is required.',
            'category_id.exists'=>'The category is not exist.',
            'image.required'=>'The image field is required.',
            'location_en.required'=>'The location field is required.',
            'location_ar.required'=>'The location field is required.',
        ];
    }
}
