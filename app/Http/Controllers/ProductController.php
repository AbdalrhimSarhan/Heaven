<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Http\Resources\ProductResource;
use App\Http\Resources\StoreProductResource;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Lcobucci\JWT\Signer\Rsa;

class ProductController extends Controller
{

    public function show($categoryId, $storeId, $productId)
    {
        try {
            $language = request()->get('lang', 'en');

            $category = Category::where('id', $categoryId)->firstOrFail();

            $store = $category->stores()->where('stores.id', $storeId)->firstOrFail();

            $product = $store->products()->where('products.id', $productId)->firstOrFail();

            $storeProductId = DB::table('store_product')
                ->where('store_id', $storeId)
                ->where('product_id', $productId)
                ->value('id');

            if (!$storeProductId) {
                return ResponseHelper::jsonResponse(null, __('message.product_not_found'), 404, false);
            }

            $response = ProductResource::make($product)->additional(['lang' => $language])->toArray(request());


            return ResponseHelper::jsonResponse($response, __('message.success'), 200, true);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return ResponseHelper::jsonResponse(null, __('message.ModelNotFoundException'), 404, false);
        }
    }

    public function search($name)
    {
        $language = app()->getLocale();
        $product = Product::where("name_{$language}", 'like', '%' . $name . '%')
            ->with(['stores.category', 'stores' => function ($query) {
            $query->withPivot('id','price', 'quantity');
        }])->get();;

        if($product->isEmpty()){
            return ResponseHelper::jsonResponse(null,
                __('message.product_not_found'), 404, false);
        }

        $response = StoreProductResource::collection($product);

        return ResponseHelper::jsonResponse(
            $response,
        __('message.success_search'));

    }
}
