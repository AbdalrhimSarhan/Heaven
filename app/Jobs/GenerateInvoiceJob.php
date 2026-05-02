<?php

// namespace App\Jobs;

// use Illuminate\Bus\Queueable;
// use Illuminate\Contracts\Queue\ShouldQueue;
// use Illuminate\Foundation\Bus\Dispatchable;
// use Illuminate\Queue\InteractsWithQueue;
// use Illuminate\Queue\SerializesModels;

// class GenerateInvoiceJob implements ShouldQueue
// {
//     use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

//     /**
//      * Create a new job instance.
//      */
//     public function __construct()
//     {
//         //
//     }

//     /**
//      * Execute the job.
//      */
//     public function handle(): void
//     {
//         //
//     }
// }

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

    public function __construct(
        public int $orderId
    ) {}

    public function handle(): void
    {
        $startTime = microtime(true);

        Log::info('🧾 [QUEUE] GenerateInvoiceJob START', [
            'order_id' => $this->orderId,
            'timestamp' => now()->toDateTimeString(),
        ]);

        $order = Order::find($this->orderId);

        if (!$order) {
            Log::warning('⚠️ [QUEUE] Order not found while generating invoice', [
                'order_id' => $this->orderId,
            ]);
            return;
        }

        /*
         |------------------------------------------------------------
         | Generate invoice in background
         |------------------------------------------------------------
         | This simulates a heavy task that the user should not wait for.
         */
        $invoice = Invoice::create([
            'order_id' => $order->id,
            'invoice_number' => 'INV-' . now()->format('Ymd') . '-' . $order->id,
            'total_price' => $order->total_price,
            'status' => 'generated',
            'generated_at' => now(),
        ]);

        // Simulate heavy invoice generation.
        sleep(2);

        /*
         |------------------------------------------------------------
         | Send order confirmation email in background
         |------------------------------------------------------------
         | We use a demo email because the users table has no email column.
         | With MAIL_MAILER=log, the email is written to laravel.log.
         */
        Mail::to('customer@example.com')->send(
            new OrderConfirmedMail($order, $invoice)
        );

        // Simulate slow email sending.
        sleep(2);

        $executionTime = (microtime(true) - $startTime) * 1000;

        Log::info('✅ [QUEUE] GenerateInvoiceJob DONE', [
            'order_id' => $order->id,
            'invoice_id' => $invoice->id,
            'execution_time_ms' => round($executionTime, 2),
        ]);
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('❌ [QUEUE] GenerateInvoiceJob FAILED', [
            'order_id' => $this->orderId,
            'error' => $exception->getMessage(),
        ]);
    }
}