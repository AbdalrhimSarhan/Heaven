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

        foreach ($this->Cart_items as $cartItem) {
            $cartItemsData[] = [
                'product_name' => optional($cartItem->store_product->product)->name ?? 'N/A',
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
