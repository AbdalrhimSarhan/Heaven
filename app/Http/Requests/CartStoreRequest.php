<?php

namespace App\Http\Requests;

use App\Models\Store_product;
use Illuminate\Foundation\Http\FormRequest;

class CartStoreRequest extends FormRequest
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
            'store_id' => ['required', 'exists:stores,id'],
            'product_id' => ['required', 'exists:products,id'],
            'quantity' => [
                'required',
                'integer',
                'min:1',
                function ($attribute, $value, $fail) {
                    $storeProduct = Store_product::where('product_id', request('product_id'))
                        ->where('store_id', request('store_id'))
                        ->first();

                    if (!$storeProduct) {
                        $fail(__('message.cart.store')); // Store-product relationship not found
                    } elseif ($storeProduct->quantity < $value) {
                        $fail("The requested quantity exceeds the available stock of {$storeProduct->quantity}.");
                    }
                },
            ],
        ];
    }


    public function attributes(): array
    {
        return [
            'store_id' => __('message.store_id'),
            'product_id' => __('message.product_id'),
            'quantity' => __('message.quantity'),
        ];
    }


}
