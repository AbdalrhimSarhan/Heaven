<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cart_item extends Model
{
    use HasFactory;
    protected $table = 'cart_items';
    protected $fillable =[
        'store_product_id',
        'order_id',
        'quantity',
        'user_id',
    ];

    public function store_product()
    {
        return $this->belongsTo(Store_product::class, 'store_product_id', 'id');
    }


}
