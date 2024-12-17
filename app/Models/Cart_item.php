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
    /**
     * Relationship with the Order (belongsTo)
     */
    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    /**
     * Relationship with the User (belongsTo)
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
