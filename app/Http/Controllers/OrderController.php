<?php

// namespace App\Http\Controllers;

// use App\Helpers\ResponseHelper;
// use App\Http\Resources\OrderResource;
// use App\Models\Cart_item;
// use App\Models\Order;
// use Illuminate\Http\Request;

// class OrderController extends Controller
// {
//     public function confirmOrder()
//     {
//         $userId = auth()->id();
//         // Check if there are items in the cart without an order_id
//         $cartItems = Cart_item::where('user_id', $userId)->whereNull('order_id')->get();

//         if ($cartItems->isEmpty()) {
//             return ResponseHelper::jsonResponse(null,__('message.order.empty'),400,false);
//         }

//         // Create a new order
//         $order = Order::create([
//             'user_id' => $userId,
//             'total_price' => $cartItems->sum(function ($item) {
//                 return $item->quantity * $item->store_product->price; // Assuming storeProduct has price
//             }),
//         ]);
//       //  $order=
//         // Update cart items to associate them with the new order
//         Cart_item::where('user_id', $userId)->whereNull('order_id')->update([
//             'order_id' => $order->id,
//         ]);
//         return ResponseHelper::jsonResponse($order,__('message.order.success'),200,true);
//     }

//     public function getClientOrders()
//     {
//         $language = request()->get('lang', 'en');
//         // Fetch the user's orders with relations
//         $orders = Order::with(['Cart_items.store_product.product'])
//             ->where('user_id', auth()->id())
//             ->get();

//         // Check if the user has any orders
//         if ($orders->isEmpty()) {
//             return ResponseHelper::jsonResponse([], __('message.order.not found'), 404,false);
//         }

//         // Return the orders using OrderResource
//         return ResponseHelper::jsonResponse([
//             'orders' => OrderResource::collection($orders)->additional(['lang' => $language])->toArray(request()),
//         ], __('message.order.getClientOrders'), 200);
//     }
// }
namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Http\Resources\OrderResource;
use App\Models\Cart_item;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Invoice;
use App\Mail\OrderConfirmedMail;
use Illuminate\Support\Facades\Mail;
use App\Jobs\GenerateInvoiceJob;
class OrderController extends Controller
{
    public function confirmOrder()
    {
        $startTime = microtime(true);
        $userId = auth()->id();

        try {
            /*
             |------------------------------------------------------------
             | 1. Fetch current user's open cart items
             |------------------------------------------------------------
             | These are cart items that are not yet attached to any order.
             */
            $cartItems = Cart_item::with('store_product')
                ->where('user_id', $userId)
                ->whereNull('order_id')
                ->get();

            if ($cartItems->isEmpty()) {
                return ResponseHelper::jsonResponse(
                    null,
                    __('message.order.empty'),
                    400,
                    false
                );
            }

            /*
             |------------------------------------------------------------
             | 2. Create order inside transaction
             |------------------------------------------------------------
             | Creating the order and attaching cart items must succeed together.
             */
            $order = DB::transaction(function () use ($userId, $cartItems) {
                $totalPrice = $cartItems->sum(function ($item) {
                    return $item->quantity * $item->store_product->price;
                });

                $order = Order::create([
                    'user_id' => $userId,
                    'total_price' => $totalPrice,
                ]);

                $cartItemIds = $cartItems->pluck('id');

                Cart_item::whereIn('id', $cartItemIds)->update([
                    'order_id' => $order->id,
                ]);

                return $order;
            });

            //             /*
            // |------------------------------------------------------------
            // | Temporary synchronous invoice generation
            // |------------------------------------------------------------
            // | This will later be moved to a Queue Job.
            // */
            // $invoice = Invoice::create([
            //     'order_id' => $order->id,
            //     'invoice_number' => 'INV-' . now()->format('Ymd') . '-' . $order->id,
            //     'total_price' => $order->total_price,
            //     'status' => 'generated',
            //     'generated_at' => now(),
            // ]);

            // // Simulate heavy invoice generation
            // sleep(2);

            
            // Mail::to('customer@example.com')->send(
            //     new OrderConfirmedMail($order, $invoice)
            // );

            // // Simulate slow email sending for benchmarking before moving to queue.
            // sleep(2);
            GenerateInvoiceJob::dispatch($order->id);

            $executionTime = (microtime(true) - $startTime) * 1000;

            Log::info('✅ [ORDER] Confirm order completed', [
                'order_id' => $order->id,
                'user_id' => $userId,
                'execution_time_ms' => round($executionTime, 2),
                'queue_enabled' => true,
            ]);

            return ResponseHelper::jsonResponse(
                $order,
                __('message.order.success'),
                200,
                true
            );

        } catch (\Throwable $e) {
            Log::error('❌ [ORDER] Confirm order failed', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);

            return ResponseHelper::jsonResponse(
                null,
                $e->getMessage(),
                500,
                false
            );
        }
    }

    public function confirmOrderWithoutQueue()
{
    $startTime = microtime(true);
    $userId = auth()->id();

    try {
        /*
         |------------------------------------------------------------
         | 1. Fetch current user's open cart items
         |------------------------------------------------------------
         | These are cart items that are not yet attached to any order.
         */
        $cartItems = Cart_item::with('store_product')
            ->where('user_id', $userId)
            ->whereNull('order_id')
            ->get();

        if ($cartItems->isEmpty()) {
            return ResponseHelper::jsonResponse(
                null,
                __('message.order.empty'),
                400,
                false
            );
        }

        /*
         |------------------------------------------------------------
         | 2. Create order inside transaction
         |------------------------------------------------------------
         | This part is still correct because order creation and cart item
         | attachment must succeed together.
         */
        $order = DB::transaction(function () use ($userId, $cartItems) {
            $totalPrice = $cartItems->sum(function ($item) {
                return $item->quantity * $item->store_product->price;
            });

            $order = Order::create([
                'user_id' => $userId,
                'total_price' => $totalPrice,
            ]);

            $cartItemIds = $cartItems->pluck('id');

            Cart_item::whereIn('id', $cartItemIds)->update([
                'order_id' => $order->id,
            ]);

            return $order;
        });

        /*
         |------------------------------------------------------------
         | BAD PRACTICE: Synchronous invoice generation
         |------------------------------------------------------------
         | The user waits for this heavy task before receiving the response.
         | This is intentionally kept here for benchmarking before Queue.
         */
        $invoice = Invoice::create([
            'order_id' => $order->id,
            'invoice_number' => 'INV-SYNC-' . now()->format('YmdHis') . '-' . $order->id,
            'total_price' => $order->total_price,
            'status' => 'generated',
            'generated_at' => now(),
        ]);

        // Simulate heavy invoice generation.
        sleep(3);

        /*
         |------------------------------------------------------------
         | BAD PRACTICE: Synchronous email sending
         |------------------------------------------------------------
         | The user also waits for email sending to finish.
         | MAIL_MAILER=log is used, so this writes the email to laravel.log.
         */
        Mail::to('customer@example.com')->send(
            new OrderConfirmedMail($order, $invoice)
        );

        // Simulate slow email sending.
        sleep(3);

        $executionTime = (microtime(true) - $startTime) * 1000;

        Log::warning('⚠️ [ORDER SYNC - BAD] Confirm order completed slowly', [
            'order_id' => $order->id,
            'user_id' => $userId,
            'execution_time_ms' => round($executionTime, 2),
            'queue_enabled' => false,
            'problem' => 'Invoice generation and email sending executed inside the request lifecycle.',
        ]);

        return ResponseHelper::jsonResponse(
            [
                'order' => $order,
                'invoice' => $invoice,
                'execution_time_ms' => round($executionTime, 2),
                'queue_enabled' => false,
                'note' => 'Bad version: invoice and email were processed synchronously inside the request.',
            ],
            __('message.order.success'),
            200,
            true
        );

    } catch (\Throwable $e) {
        Log::error('❌ [ORDER SYNC - BAD] Confirm order failed', [
            'user_id' => $userId,
            'error' => $e->getMessage(),
        ]);

        return ResponseHelper::jsonResponse(
            null,
            $e->getMessage(),
            500,
            false
        );
    }
}

    public function getClientOrders()
    {
        $language = request()->get('lang', 'en');

        $orders = Order::with(['Cart_items.store_product.product'])
            ->where('user_id', auth()->id())
            ->get();

        if ($orders->isEmpty()) {
            return ResponseHelper::jsonResponse(
                [],
                __('message.order.not found'),
                404,
                false
            );
        }

        return ResponseHelper::jsonResponse([
            'orders' => OrderResource::collection($orders)
                ->additional(['lang' => $language])
                ->toArray(request()),
        ], __('message.order.getClientOrders'), 200);
    }
}