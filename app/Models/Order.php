<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;
    protected $table = 'orders';
    protected $fillable = ['user_id','total_price'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    /**
     * Relationship with Cart Items (hasMany)
     */
    public function Cart_items()
    {
        return $this->hasMany(Cart_item::class, 'order_id');
    }
}
