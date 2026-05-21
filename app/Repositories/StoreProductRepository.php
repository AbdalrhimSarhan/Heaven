<?php

namespace App\Repositories;

use App\Models\Store_product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class StoreProductRepository
{
    public function findByProductAndStore(int $productId, int $storeId): Store_product
    {
        return Store_product::where('product_id', $productId)
            ->where('store_id', $storeId)
            ->firstOrFail();
    }

    public function decrementStockAtomic(int $id, int $qty): int
    {
        return DB::table('store_product')
            ->where('id', $id)
            ->where('quantity', '>=', $qty)
            ->decrement('quantity', $qty);
    }

    public function decrementStockWithLock(int $id, int $qty): int
    {
        return DB::table('store_product')
            ->where('id', $id)
            ->lockForUpdate()
            ->where('quantity', '>=', $qty)
            ->decrement('quantity', $qty);
    }

    public function getQuantity(int $id): int
    {
        return (int) Store_product::find($id)->quantity;
    }

    public function incrementStock(int $id, int $qty): void
    {
        DB::table('store_product')->where('id', $id)->increment('quantity', $qty);
    }

    public function stockRedisKey(int $storeProductId): string
    {
        return "stock:store_product:{$storeProductId}";
    }

    public function initRedisStock(int $storeProductId, int $quantity): bool
    {
        $result = Redis::command('SET', [
            $this->stockRedisKey($storeProductId),
            $quantity,
            'NX',
            'EX',
            3600,
        ]);

        return $result !== null;
    }

    public function reserveStockRedis(int $storeProductId, int $qty): int
    {
        $lua = <<<'LUA'
        local stock = tonumber(redis.call('GET', KEYS[1]))
        if stock == nil then
            return -2
        end
        if stock < tonumber(ARGV[1]) then
            return -1
        end
        return redis.call('DECRBY', KEYS[1], ARGV[1])
        LUA;

        return (int) Redis::eval($lua, 1, $this->stockRedisKey($storeProductId), $qty);
    }

    public function restoreStockRedis(int $storeProductId, int $qty): void
    {
        Redis::incrby($this->stockRedisKey($storeProductId), $qty);
    }

    public function getRedisStock(int $storeProductId): ?int
    {
        $val = Redis::get($this->stockRedisKey($storeProductId));
        return $val !== null ? (int) $val : null;
    }
}
