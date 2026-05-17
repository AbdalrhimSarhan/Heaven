<?php

namespace App\Services;

use Closure;
use Illuminate\Support\Facades\Cache;

class CacheService
{
    public function remember(string $key, int $ttl, Closure $callback): mixed
    {
        return Cache::remember($key, $ttl, $callback);
    }

    public function invalidate(string ...$keys): void
    {
        foreach ($keys as $key) {
            Cache::forget($key);
        }
    }

    public function productListKey(int $catId, int $storeId, string $lang): string
    {
        return "products_cat_{$catId}_store_{$storeId}_{$lang}";
    }

    public function searchKey(string $name, string $lang): string
    {
        return "search_{$lang}_" . md5($name);
    }
}
