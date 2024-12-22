<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;
class FavouriteProduct extends Pivot
{
    use HasFactory;

    protected $table = 'favourite_products';

    // Disable timestamps since we don't have created_at/updated_at fields
    public $timestamps = false;

    // Fillable attributes for mass assignment
    protected $fillable = [
        'id',
        'stores_product_id',
        'user_id',
    ];

    /**
     * Relationship with the StoreProduct model
     */
    public function store_product()
    {
        return $this->belongsTo(Store_product::class, 'stores_product_id');
    }

    /**
     * Relationship with the User model
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
