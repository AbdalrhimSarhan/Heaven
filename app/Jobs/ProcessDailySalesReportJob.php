<?php

namespace App\Jobs;

use App\Models\Order;
use App\Models\DailySalesReport;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessDailySalesReportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 120;

    public function __construct(
        public string $date,
        public int $chunkSize = 500
    ) {}

    public function handle(): void
    {
        $startTime = microtime(true);

        $totalOrders = 0;
        $totalRevenue = 0;
        $processedChunks = 0;

        Log::info('📊 [BATCH] Daily sales report job START', [
            'date' => $this->date,
            'chunk_size' => $this->chunkSize,
            'timestamp' => now()->toDateTimeString(),
        ]);

        /*
         |------------------------------------------------------------
         | Batch Processing with chunkById
         |------------------------------------------------------------
         | We do NOT use get() here because it loads all orders into memory.
         | chunkById() loads a small number of orders, processes them,
         | then loads the next chunk.
         */
        Order::whereDate('created_at', $this->date)
            ->orderBy('id')
            ->chunkById($this->chunkSize, function ($orders) use (&$totalOrders, &$totalRevenue, &$processedChunks) {
                $processedChunks++;

                foreach ($orders as $order) {
                    $totalOrders++;
                    $totalRevenue += $order->total_price;
                }

                Log::info('📦 [BATCH] Chunk processed', [
                    'date' => $this->date,
                    'chunk_number' => $processedChunks,
                    'orders_in_chunk' => $orders->count(),
                    'partial_total_orders' => $totalOrders,
                    'partial_total_revenue' => round($totalRevenue, 2),
                ]);
            });

        $averageOrderValue = $totalOrders > 0
            ? $totalRevenue / $totalOrders
            : 0;

        $executionTime = (microtime(true) - $startTime) * 1000;

        DailySalesReport::updateOrCreate(
            [
                'report_date' => $this->date,
            ],
            [
                'total_orders' => $totalOrders,
                'total_revenue' => round($totalRevenue, 2),
                'average_order_value' => round($averageOrderValue, 2),
                'processed_chunks' => $processedChunks,
                'chunk_size' => $this->chunkSize,
                'processing_time_ms' => round($executionTime, 2),
            ]
        );

        Log::info('✅ [BATCH] Daily sales report job DONE', [
            'date' => $this->date,
            'total_orders' => $totalOrders,
            'total_revenue' => round($totalRevenue, 2),
            'average_order_value' => round($averageOrderValue, 2),
            'processed_chunks' => $processedChunks,
            'processing_time_ms' => round($executionTime, 2),
        ]);
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('❌ [BATCH] Daily sales report job FAILED', [
            'date' => $this->date,
            'error' => $exception->getMessage(),
        ]);
    }
}