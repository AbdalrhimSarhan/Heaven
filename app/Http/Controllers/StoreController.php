<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Http\Resources\ProductResource;
use App\Http\Resources\StoreResource;
use App\Models\Category;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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

        $category = Category::where('id', $categoryId)->firstOrFail();

        $store = $category->stores()->where('id', $storeId)->firstOrFail();

        $products = $store->products()->withPivot('price', 'quantity')->get();

        return ResponseHelper::jsonResponse(
            StoreResource::collection($products)->additional(['lang' => $language]),
            __('message.success')
        );
    }

}
