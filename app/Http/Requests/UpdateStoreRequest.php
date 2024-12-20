<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStoreRequest extends FormRequest
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
    public function rules(): array   //  $this->route('store')->id,
    {
        return [
            'name_en' => 'sometimes|string|min:3|max:100|unique:stores,name_en,' . $this->store->id,
            'name_ar' => 'sometimes|string|min:3|max:100|unique:stores,name_ar,' . $this->store->id,
            'category_id' => 'sometimes|integer|exists:categories,id',
            'image' => 'sometimes|image',
            'location_en' => 'sometimes|string|min:3|max:100',
            'location_ar' => 'sometimes|string|min:3|max:100',
        ];
    }


    public function messages(){
        return[
            'name_en.sometimes'=>'The name_en field is required.',
            'name_ar.sometimes'=>'The name_ar field is required.',
            'category_id.sometimes'=>'The category field is required.',
            'category_id.exists'=>'The category is not exist.',
            'image.sometimes'=>'The image field is required.',
            'location_en.sometimes'=>'The location field is required.',
            'location_ar.sometimes'=>'The location field is required.',
        ];
    }
}
