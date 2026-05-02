<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\ResponseHelper;
use App\Jobs\ProcessDailySalesReportJob;
use Illuminate\Support\Facades\Log;
use App\Models\Order;
use App\Models\DailySalesReport;
class ReportController extends Controller
{


    // this is bad version that processes the report synchronously without chunking or queueing
    //    WARN  PHP Fatal error: Allowed memory size of 536870912 bytes exhausted (tried to allocate 20480 bytes) in C:\xampp\htdocs\Heaven\Heaven\vendor\laravel\framework\src\Illuminate\Database\Eloquent\Model.php on line 602.  


//    WARN  PHP Fatal error: Allowed memory size of 536870912 bytes exhausted (tried to allocate 20480 bytes) in C:\xampp\htdocs\Heaven\Heaven\vendor\symfony\error-handler\Error\FatalError.php on line 14.  


    public function processDailySalesSync(Request $request)
    {
        $startTime = microtime(true);
        $startMemory = memory_get_usage(true);

        $date = $request->input('date', now()->toDateString());

        try {
            Log::warning('⚠️ [REPORT SYNC - BAD] Daily sales report started', [
                'date' => $date,
                'user_id' => auth()->id(),
                'queue_enabled' => false,
                'chunking_enabled' => false,
                'problem' => 'This version loads all orders into memory using get().',
            ]);

            /*
            |------------------------------------------------------------
            | BAD PRACTICE: Load all orders at once
            |------------------------------------------------------------
            | This is intentionally bad for benchmarking.
            | If there are 200,000 orders, all 200,000 Eloquent models
            | are loaded into memory before processing starts.
            */
            $orders = Order::whereDate('created_at', $date)->get();

            $totalOrders = $orders->count();
            $totalRevenue = 0;

            foreach ($orders as $order) {
                $totalRevenue += $order->total_price;
            }

            $averageOrderValue = $totalOrders > 0
                ? $totalRevenue / $totalOrders
                : 0;

            $executionTime = (microtime(true) - $startTime) * 1000;
            $endMemory = memory_get_usage(true);
            $peakMemory = memory_get_peak_usage(true);

            /*
            |------------------------------------------------------------
            | Save report result
            |------------------------------------------------------------
            | processed_chunks = 1 because this bad version treats all
            | records as one huge batch.
            */
            DailySalesReport::updateOrCreate(
                [
                    'report_date' => $date,
                ],
                [
                    'total_orders' => $totalOrders,
                    'total_revenue' => round($totalRevenue, 2),
                    'average_order_value' => round($averageOrderValue, 2),
                    'processed_chunks' => 1,
                    'chunk_size' => $totalOrders,
                    'processing_time_ms' => round($executionTime, 2),
                ]
            );

            Log::warning('⚠️ [REPORT SYNC - BAD] Daily sales report completed', [
                'date' => $date,
                'total_orders' => $totalOrders,
                'total_revenue' => round($totalRevenue, 2),
                'average_order_value' => round($averageOrderValue, 2),
                'processed_chunks' => 1,
                'execution_time_ms' => round($executionTime, 2),
                'memory_start_mb' => round($startMemory / 1024 / 1024, 2),
                'memory_end_mb' => round($endMemory / 1024 / 1024, 2),
                'memory_peak_mb' => round($peakMemory / 1024 / 1024, 2),
                'queue_enabled' => false,
                'chunking_enabled' => false,
            ]);

            return ResponseHelper::jsonResponse(
                [
                    'date' => $date,
                    'total_orders' => $totalOrders,
                    'total_revenue' => round($totalRevenue, 2),
                    'average_order_value' => round($averageOrderValue, 2),
                    'processed_chunks' => 1,
                    'chunk_size' => $totalOrders,
                    'execution_time_ms' => round($executionTime, 2),
                    'memory_peak_mb' => round($peakMemory / 1024 / 1024, 2),
                    'queue_enabled' => false,
                    'chunking_enabled' => false,
                    'note' => 'Bad version: all orders were loaded into memory using get() and processed inside the request.',
                ],
                'Daily sales report processed synchronously.',
                200,
                true
            );

        } catch (\Throwable $e) {
            Log::error('❌ [REPORT SYNC - BAD] Daily sales report failed', [
                'date' => $date,
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



    // this is the good version that uses chunking and queues the processing to avoid memory issues and long request times
    
    public function processDailySales(Request $request)
    {
        $date = $request->input('date', now()->toDateString());
        $chunkSize = (int) $request->input('chunk_size', 500);

        ProcessDailySalesReportJob::dispatch($date, $chunkSize);

        Log::info('📊 [REPORT] Daily sales report job dispatched', [
            'date' => $date,
            'chunk_size' => $chunkSize,
            'user_id' => auth()->id(),
        ]);

        return ResponseHelper::jsonResponse(
            [
                'date' => $date,
                'chunk_size' => $chunkSize,
                'queue_enabled' => true,
                'job' => 'ProcessDailySalesReportJob',
                'message' => 'Daily sales report is being processed in background.',
            ],
            'Daily sales report job dispatched successfully.',
            202,
            true
        );
    }
}
