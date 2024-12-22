<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Store extends Model
{
    use HasFactory;
    protected $fillable= [
        'name_ar',    // Arabic name
        'name_en',    // English name
        'location_ar', // Arabic location
        'location_en', // English location
        'image','category_id'
    ];
//    public function getRouteKeyName()
//    {
//        return 'name'; // Use the 'name' column for route model binding
//    }
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function products(){

        return $this->belongsToMany(Product::class,'store_product')->withPivot('id','price', 'quantity');
    }
}
