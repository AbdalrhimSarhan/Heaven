<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Store_product extends Model
{
    use HasFactory;

    protected $table = 'store_product';
    protected $fillable = ['id','store_id', 'product_id', 'quantity', 'price'];

    public function cart_items()
    {
        return $this->hasMany(Cart_item::class, 'store_product_id', 'id');
    }

    /**
     * Relationship with the Store (belongsTo)
     */
    public function store()
    {
        return $this->belongsTo(Store::class, 'store_id');
    }

    /**
     * Relationship with the Product (belongsTo)
     */
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }


}
