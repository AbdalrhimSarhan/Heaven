<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Http\Resources\ProductResource;
use App\Http\Resources\StoreResource;
use App\Models\Category;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class StoreController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        Store::where('id');
    }

    public function showProducts(Request $request, $categoryId, $storeId)
    {
    $language = $request->get('lang', 'en');
    
    // مفتاح فريد للتخزين يعتمد على القسم والمتجر واللغة
    $cacheKey = "products_cat_{$categoryId}_store_{$storeId}_{$language}";

    $data = Cache::remember($cacheKey, 3600, function () use ($categoryId, $storeId, $language) {
        $category = Category::where('id', $categoryId)->firstOrFail();
        $store = $category->stores()->where('id', $storeId)->firstOrFail();
        $products = $store->products()->withPivot('price', 'quantity')->get();

        return StoreResource::collection($products)->additional(['lang' => $language]);
    });

    return ResponseHelper::jsonResponse($data, __('message.success'));
    }

}
