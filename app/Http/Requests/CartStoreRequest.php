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
            'store_id' => [ 'required', 'exists:store_product,id',],
            'product_id' => [ 'required', 'exists:store_product,id',],
            'quantity' => [
                'required',
                'integer',
                'min:1',
                function ($attribute, $value, $fail) {
                    $storeProduct = Store_product::find(request('store_product_id'));

                    if ($storeProduct && $value > $storeProduct->quantity) {
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
