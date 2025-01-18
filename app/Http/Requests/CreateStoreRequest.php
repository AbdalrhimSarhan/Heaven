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
            'category' => 'required|string|in:Restaurant,Perfumes,Clothes,Electronics',
            'image'=>'required|image',
            'location_en'=>'required|string|min:3|max:100',
            'location_ar'=>'required|string|min:3|max:100',
        ];
    }

    public function attributes(): array
    {
        return [
            'name_en' => __('message.name_en'),
            'name_ar' => __('message.name_ar'),
            'category_id' => __('message.category.id'),
            'image' => __('message.image'),
            'location_en' => __('message.location_en'),
            'location_ar' => __('message.location_ar'),
        ];
    }




}
