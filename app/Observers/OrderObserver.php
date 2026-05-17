<?php

namespace App\Observers;

use App\Jobs\GenerateInvoiceJob;
use App\Models\Order;
use Illuminate\Support\Facades\Log;

class OrderObserver
{
    public function created(Order $order): void
    {
        Log::info('[AOP:Order.created]', [
            'order_id'    => $order->id,
            'user_id'     => $order->user_id,
            'total_price' => $order->total_price,
        ]);

        GenerateInvoiceJob::dispatch($order->id);
    }
}
