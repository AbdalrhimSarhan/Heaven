<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;
    protected $fillable = [
        'name_ar',         // Arabic name
        'name_en',         // English name
        'description_ar',  // Arabic description
        'description_en',  // English description
        'image'
    ];
    public function stores()
    {
        return $this->belongsToMany(Store::class, 'store_product')->withPivot('price', 'quantity');
    }
}
