<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Models\Cart_item;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function confirmOrder()
    {
        $userId = auth()->id();
        // Check if there are items in the cart without an order_id
        $cartItems = Cart_item::where('user_id', $userId)->whereNull('order_id')->get();

        if ($cartItems->isEmpty()) {
            return ResponseHelper::jsonResponse(null,'No items in cart to confirm.',400,false);
        }

        // Create a new order
        $order = Order::create([
            'user_id' => $userId,
            'total_price' => $cartItems->sum(function ($item) {
                return $item->quantity * $item->store_product->price; // Assuming storeProduct has price
            }),
        ]);

        // Update cart items to associate them with the new order
        Cart_item::where('user_id', $userId)->whereNull('order_id')->update([
            'order_id' => $order->id,
        ]);
        return ResponseHelper::jsonResponse($order,'your order has been confirmed.');

    }
}
