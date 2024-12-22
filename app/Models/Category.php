<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;
    protected $fillable = ['name_ar', 'name_en','image'];

//    public function getRouteKeyName()
//    {
//        return 'name'; // Use the 'name' column for route model binding
//    }
    public function stores()
    {
        return $this->hasMany(Store::class);
    }

}
