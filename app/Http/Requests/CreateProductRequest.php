<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateProductRequest extends FormRequest
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
            'name_ar' => 'nullable|string|max:255',
            'name_en' => 'nullable|string|max:255',
            'description_ar' => 'nullable|string',
            'description_en' => 'nullable|string',
            'product_image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048', // Limit image size to 2MB
            'price' => 'nullable|numeric|min:0', // Ensure price is a positive number
            'quantity' => 'nullable|integer|min:0', // Ensure quantity is a non-negative integer
        ];
    }

    public function attributes(): array
    {
        return [
            'name_en' => __('message.name_en'),
            'name_ar' => __('message.name_ar'),
            'description_en' => __('message.description_en'),
            'description_ar' => __('message.description_ar'),
            'price' => __('message.price'),
            'quantity' => __('message.quantity'),
            'product_image' => __('message.image'),
        ];
    }
}
