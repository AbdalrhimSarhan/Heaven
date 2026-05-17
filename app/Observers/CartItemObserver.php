<?php

namespace App\Observers;

use App\Models\Cart_item;
use Illuminate\Support\Facades\Log;

class CartItemObserver
{
    public function created(Cart_item $cartItem): void
    {
        Log::info('[AOP:CartItem.created]', [
            'cart_item_id'     => $cartItem->id,
            'user_id'          => $cartItem->user_id,
            'store_product_id' => $cartItem->store_product_id,
            'quantity'         => $cartItem->quantity,
        ]);
    }

    public function deleted(Cart_item $cartItem): void
    {
        Log::info('[AOP:CartItem.deleted]', [
            'cart_item_id' => $cartItem->id,
            'user_id'      => $cartItem->user_id,
        ]);
    }
}
