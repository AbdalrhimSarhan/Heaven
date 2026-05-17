<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Http\Resources\OrderResource;
use App\Models\Cart_item;
use App\Models\Invoice;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\OrderConfirmedMail;

class OrderController extends Controller
{
    public function __construct(private OrderService $orderService) {}

    public function confirmOrder()
    {
        $userId = auth()->id();

        try {
            $order = $this->orderService->confirm($userId);

            return ResponseHelper::jsonResponse($order, __('message.order.success'), 200, true);

        } catch (\RuntimeException $e) {
            return ResponseHelper::jsonResponse(null, $e->getMessage(), $e->getCode() ?: 400, false);
        } catch (\Throwable $e) {
            Log::error('Order confirm error', ['user_id' => $userId, 'error' => $e->getMessage()]);
            return ResponseHelper::jsonResponse(null, $e->getMessage(), 500, false);
        }
    }

    public function confirmOrderWithoutQueue()
    {
        $startTime = microtime(true);
        $userId = auth()->id();

        try {
            $cartItems = Cart_item::with('store_product')
                ->where('user_id', $userId)
                ->whereNull('order_id')
                ->get();

            if ($cartItems->isEmpty()) {
                return ResponseHelper::jsonResponse(null, __('message.order.empty'), 400, false);
            }

            $order = DB::transaction(function () use ($userId, $cartItems) {
                $totalPrice = $cartItems->sum(fn($item) => $item->quantity * $item->store_product->price);

                $order = Order::create([
                    'user_id'     => $userId,
                    'total_price' => $totalPrice,
                ]);

                Cart_item::whereIn('id', $cartItems->pluck('id'))->update(['order_id' => $order->id]);

                return $order;
            });

            $invoice = Invoice::create([
                'order_id'        => $order->id,
                'invoice_number'  => 'INV-SYNC-' . now()->format('YmdHis') . '-' . $order->id,
                'total_price'     => $order->total_price,
                'status'          => 'generated',
                'generated_at'    => now(),
            ]);

            sleep(3);

            Mail::to('customer@example.com')->send(new OrderConfirmedMail($order, $invoice));

            sleep(3);

            return ResponseHelper::jsonResponse([
                'order'             => $order,
                'invoice'           => $invoice,
                'execution_time_ms' => round((microtime(true) - $startTime) * 1000, 2),
                'queue_enabled'     => false,
                'note'              => 'Bad version: invoice and email processed synchronously inside the request.',
            ], __('message.order.success'), 200, true);

        } catch (\Throwable $e) {
            Log::error('Order sync confirm error', ['user_id' => $userId, 'error' => $e->getMessage()]);
            return ResponseHelper::jsonResponse(null, $e->getMessage(), 500, false);
        }
    }

    public function getClientOrders()
    {
        $language = request()->get('lang', 'en');

        $orders = Order::with(['Cart_items.store_product.product'])
            ->where('user_id', auth()->id())
            ->get();

        if ($orders->isEmpty()) {
            return ResponseHelper::jsonResponse([], __('message.order.not found'), 404, false);
        }

        return ResponseHelper::jsonResponse([
            'orders' => OrderResource::collection($orders)
                ->additional(['lang' => $language])
                ->toArray(request()),
        ], __('message.order.getClientOrders'), 200);
    }
}
