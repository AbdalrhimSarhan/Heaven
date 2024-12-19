<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Http\Resources\OrderResource;
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
            return ResponseHelper::jsonResponse(null,__('message.order.empty'),400,false);
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
        return ResponseHelper::jsonResponse($order,__('message.order.success'),200,false);
    }

    public function getClientOrders()
    {
        $language = request()->get('lang', 'en');
        // Fetch the user's orders with relations
        $orders = Order::with(['Cart_items.store_product.product'])
            ->where('user_id', auth()->id())
            ->get();

        // Check if the user has any orders
        if ($orders->isEmpty()) {
            return ResponseHelper::jsonResponse([], __('message.order.not found'), 404,false);
        }

        // Return the orders using OrderResource
        return ResponseHelper::jsonResponse([
            'orders' => OrderResource::collection($orders)->additional(['lang' => $language])->toArray(request()),
        ], __('message.getClientOrders'), 200);
    }
}
