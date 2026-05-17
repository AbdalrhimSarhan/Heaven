<?php

namespace App\Jobs;

use App\Models\Order;
use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\OrderConfirmedMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GenerateInvoiceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(public int $orderId)
    {
        $this->afterCommit = true;
    }

    public function handle(): void
    {
        $order = Order::find($this->orderId);

        if (!$order) {
            return;
        }

        $invoice = Invoice::create([
            'order_id'       => $order->id,
            'invoice_number' => 'INV-' . now()->format('Ymd') . '-' . $order->id,
            'total_price'    => $order->total_price,
            'status'         => 'generated',
            'generated_at'   => now(),
        ]);

        sleep(3);

        Mail::to('customer@example.com')->send(new OrderConfirmedMail($order, $invoice));

        sleep(3);
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('GenerateInvoiceJob failed', [
            'order_id' => $this->orderId,
            'error'    => $exception->getMessage(),
        ]);
    }
}
