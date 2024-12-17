<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Http\Resources\StoreResource;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index(Request $request) {

        $language = $request->get('lang', 'en');

        $categories = Category::all();

        $data = $categories->map(function($category) use ($language) {
            return [
                'id' => $category->id,
                'name' => $language === 'ar' ? $category->name_ar : $category->name_en,
                'created_at' => $category->created_at,
                'updated_at' => $category->updated_at,
            ];
        });

        return ResponseHelper::jsonResponse($data, 'successfully');
    }

    public function showStores(Request $request, Category $category)
    {
        $language = $request->get('lang', 'en');

        $stores = $category->stores;

        return ResponseHelper::jsonResponse([
            'category' => $language === 'ar' ? $category->name_ar : $category->name_en,
            'stores' => StoreResource::collection($stores)->additional(['lang' => $language]),
        ], 'successfully');
    }
}
