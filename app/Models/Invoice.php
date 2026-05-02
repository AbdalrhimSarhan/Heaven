<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $fillable = [
        'order_id',
        'invoice_number',
        'total_price',
        'status',
        'generated_at',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}