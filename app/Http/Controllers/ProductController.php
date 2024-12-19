<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Http\Resources\ProductResource;
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

            // Get store_product ID from the pivot table
            $storeProductId = DB::table('store_product')
                ->where('store_id', $storeId)
                ->where('product_id', $productId)
                ->value('id'); // Assuming 'id' is the primary key

            if (!$storeProductId) {
                return ResponseHelper::jsonResponse(null, 'Product not found in this store', 404, false);
            }

            $response = ProductResource::make($product)->additional(['lang' => $language])->toArray(request());

            $response['store_product_id'] = $storeProductId;

            return ResponseHelper::jsonResponse($response, 'successfully');

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return ResponseHelper::jsonResponse(null, 'Resource not found', 404, false);
        }
    }

    public function search($name)
    {
        $language = request()->get('lang', 'en');
        $product = Product::where("name_{$language}", 'like', '%' . $name . '%')->get();

        if($product->isEmpty()){
            return ResponseHelper::jsonResponse(null, 'Product not found', 404, false);
        }

        $response = ProductResource::collection($product)->additional(['lang' => $language])->toArray(request());

        return ResponseHelper::jsonResponse(
            $response,
        'successfully search');

    }


}
