<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Http\Resources\ProductResource;
use App\Http\Resources\StoreProductResource;
use App\Models\Category;
use App\Models\Product;
use App\Services\CacheService;
use Illuminate\Support\Facades\DB;

/**
 * CacheService injected — Cache Aspect applies to both show() and search().
 */
class ProductController extends Controller
{
    public function __construct(private CacheService $cache) {}

    public function show(string $categoryId, string $storeId, string $productId)
    {
        try {
            $language = request()->get('lang', 'en');

            $category = Category::where('id', $categoryId)->firstOrFail();
            $store    = $category->stores()->where('stores.id', $storeId)->firstOrFail();
            $product  = $store->products()->where('products.id', $productId)->firstOrFail();

            $storeProductId = DB::table('store_product')
                ->where('store_id', $storeId)
                ->where('product_id', $productId)
                ->value('id');

            if (!$storeProductId) {
                return ResponseHelper::jsonResponse(null, __('message.product_not_found'), 404, false);
            }

            $response = ProductResource::make($product)
                ->additional(['lang' => $language])
                ->toArray(request());

            return ResponseHelper::jsonResponse($response, __('message.success'), 200, true);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            return ResponseHelper::jsonResponse(null, __('message.ModelNotFoundException'), 404, false);
        }
    }

    /**
     * Requirement #6 (extended) - search results are cached in Redis for 60 seconds.
     * Shorter TTL than product lists because search results change more frequently.
     */
    public function search(string $name)
    {
        $language = app()->getLocale();
        $cacheKey = $this->cache->searchKey($name, $language);

        $products = $this->cache->remember($cacheKey, 60, function () use ($name, $language) {
            return Product::where("name_{$language}", 'like', '%' . $name . '%')
                ->with(['stores.category', 'stores' => function ($query) {
                    $query->withPivot('id', 'price', 'quantity');
                }])
                ->get();
        });

        if ($products->isEmpty()) {
            return ResponseHelper::jsonResponse(null, __('message.product_not_found'), 404, false);
        }

        return ResponseHelper::jsonResponse(
            StoreProductResource::collection($products),
            __('message.success_search')
        );
    }
}
