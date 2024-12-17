<?php

namespace App\Http\Requests;

use App\Models\Cart_item;
use Illuminate\Foundation\Http\FormRequest;

class UpdateQuantityRequest extends FormRequest
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
        $cartItemId = $this->route('cartItemId'); // Get the cart item ID from the route
        $cartItem = Cart_item::find($cartItemId);

        $storeProduct = optional($cartItem)->store_product;

        $maxStock = optional($storeProduct)->quantity ?? 0;

        return [
            'quantity' => [
                'required',
                'integer',
                'min:1', // Quantity cannot be less than 1
                function ($attribute, $value, $fail) use ($maxStock) {
                    if ($value > $maxStock) {
                        $fail("The requested quantity exceeds the available stock of {$maxStock}.");
                    }
                },
            ],
        ];
    }

    /**
     * Custom messages for validation errors.
     */
    public function messages()
    {
        return [
            'quantity.required' => 'The quantity field is required.',
            'quantity.integer' => 'The quantity must be a valid integer.',
            'quantity.min' => 'The quantity must be at least 1.',
        ];
    }
}
