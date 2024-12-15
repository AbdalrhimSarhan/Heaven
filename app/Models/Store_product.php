<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Store_product extends Model
{
    use HasFactory;
    protected $table = 'store_product';
    protected $fillable = ['store_id', 'product_id', 'quantity', 'price'];

    public function cart_items()
    {
        return $this->hasMany(Cart_item::class, 'store_product_id', 'id');
    }
}
