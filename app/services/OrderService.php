<?php

namespace App\Services;

use App\Models\Cart_item;
use App\Models\Order;
use Illuminate\Support\Facades\DB;

class OrderService
{
    public function confirm(int $userId): Order
    {
        $cartItems = Cart_item::with('store_product')
            ->where('user_id', $userId)
            ->whereNull('order_id')
            ->get();

        if ($cartItems->isEmpty()) {
            throw new \RuntimeException(__('message.order.empty'), 400);
        }

        return DB::transaction(function () use ($userId, $cartItems) {
            $totalPrice = $cartItems->sum(
                fn($item) => $item->quantity * $item->store_product->price
            );

            $order = Order::create([
                'user_id'     => $userId,
                'total_price' => $totalPrice,
            ]);

            Cart_item::whereIn('id', $cartItems->pluck('id'))
                ->update(['order_id' => $order->id]);

            return $order;
        });
    }
}
