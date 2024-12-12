<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Http\Resources\StoreResource;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index(){
    $category = category::all();
    return ResponseHelper::jsonResponse($category,'successfully');
    }

    public function showStores(Category $category)
    {
        $stores = $category->stores;
        return ResponseHelper::jsonResponse([
            'category' => $category->name,
            'stores' => StoreResource::collection($stores), ],
            'successfully');
    }
}
