<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $cartItemsData = [];
        $language = $request->get('lang', 'en');

        foreach ($this->Cart_items as $cartItem) {
            // Dynamically fetch the product name based on the language
            $productName = optional($cartItem->store_product->product)->{"name_{$language}"} ?? 'N/A';

            $cartItemsData[] = [
                'product_name' => $productName,
                'quantity' => $cartItem->quantity,
                'price' => optional($cartItem->store_product)->price ?? 0,
            ];
        }

        return [
            'id' => $this->id,
            'total_price' => $this->total_price,
            'cart_items' => $cartItemsData,
        ];
    }

}
