<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Http\Resources\StoreResource;
use App\Models\Category;
use App\Services\CacheService;
use Illuminate\Http\Request;

/**
 * CacheService is injected — this controller never calls Cache:: directly.
 * AOP Cache Aspect: showProducts() is a Join Point where the Cache Advice applies.
 */
class StoreController extends Controller
{
    public function __construct(private CacheService $cache) {}

    /**
     * Requirement #6 - Distributed Caching.
     * Products are cached in Redis for 1 hour per (category, store, language) combination.
     * DB is only queried on cache miss.
     */
    public function showProducts(Request $request, string $categoryId, string $storeId)
    {
        $language = $request->get('lang', 'en');
        $cacheKey = $this->cache->productListKey((int) $categoryId, (int) $storeId, $language);

        $data = $this->cache->remember($cacheKey, 3600, function () use ($categoryId, $storeId, $language) {
            $category = Category::where('id', $categoryId)->firstOrFail();
            $store    = $category->stores()->where('id', $storeId)->firstOrFail();
            $products = $store->products()->withPivot('price', 'quantity')->get();

            return StoreResource::collection($products)->additional(['lang' => $language]);
        });

        return ResponseHelper::jsonResponse($data, __('message.success'));
    }
}
