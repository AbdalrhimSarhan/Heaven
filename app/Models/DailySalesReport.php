<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailySalesReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'report_date',
        'total_orders',
        'total_revenue',
        'average_order_value',
        'processed_chunks',
        'chunk_size',
        'processing_time_ms',
    ];
}