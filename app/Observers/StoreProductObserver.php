<?php

namespace App\Observers;

use App\Models\Store_product;
use App\Repositories\StoreProductRepository;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class StoreProductObserver
{
    public function __construct(private StoreProductRepository $stockRepo) {}

    public function updated(Store_product $storeProduct): void
    {
        if (! $storeProduct->wasChanged('quantity')) {
            return;
        }

        $newQty = (int) $storeProduct->quantity;
        $key    = $this->stockRepo->stockRedisKey($storeProduct->id);

        Redis::set($key, $newQty);
    }

    public function created(Store_product $storeProduct): void
    {
        $this->stockRepo->initRedisStock($storeProduct->id, (int) $storeProduct->quantity);

        Log::info('[Cache:Init] stock initialized in Redis', [
            'store_product_id' => $storeProduct->id,
            'quantity'         => $storeProduct->quantity,
        ]);
    }

    public function deleted(Store_product $storeProduct): void
    {
        Redis::del(
            $this->stockRepo->stockRedisKey($storeProduct->id)
        );
    }
}
