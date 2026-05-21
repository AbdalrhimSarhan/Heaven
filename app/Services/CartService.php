<?php

namespace App\Services;

use App\Jobs\CreateCartItemJob;
use App\Models\Cart_item;
use App\Repositories\StoreProductRepository;
use Illuminate\Support\Facades\DB;

class CartService
{
    public function __construct(private StoreProductRepository $stockRepo) {}

    public function addBasic(int $userId, int $productId, int $storeId, int $quantity): array
    {
        $storeProduct = $this->stockRepo->findByProductAndStore($productId, $storeId);

        if ($quantity > $storeProduct->quantity) {
            throw new \RuntimeException(
                "The requested quantity exceeds the available stock of {$storeProduct->quantity}.",
                400
            );
        }

        $storeProduct->decrement('quantity', $quantity);

        $cartItem = Cart_item::create([
            'user_id'          => $userId,
            'store_product_id' => $storeProduct->id,
            'quantity'         => $quantity,
            'order_id'         => null,
        ]);

        return [
            'cart_item'   => $cartItem,
            'total_price' => $storeProduct->price * $quantity,
        ];
    }

    public function addWithIntegrity(int $userId, int $productId, int $storeId, int $quantity): array
    {
        $storeProduct = $this->stockRepo->findByProductAndStore($productId, $storeId);

        $affected = $this->stockRepo->decrementStockAtomic($storeProduct->id, $quantity);

        if ($affected === 0) {
            throw new \RuntimeException('Insufficient stock or concurrent conflict detected.', 409);
        }

        $remaining = $this->stockRepo->getQuantity($storeProduct->id);

        // Write-through: keep Redis in sync so /flash reads the same stock as DB
        \Illuminate\Support\Facades\Redis::set(
            $this->stockRepo->stockRedisKey($storeProduct->id),
            $remaining
        );

        $cartItem = Cart_item::create([
            'user_id'          => $userId,
            'store_product_id' => $storeProduct->id,
            'quantity'         => $quantity,
            'order_id'         => null,
        ]);

        return [
            'cart_item'       => $cartItem,
            'remaining_stock' => $remaining,
        ];
    }

    public function addSafe(int $userId, int $productId, int $storeId, int $quantity): array
    {
        return DB::transaction(function () use ($userId, $productId, $storeId, $quantity) {
            $storeProduct = $this->stockRepo->findByProductAndStore($productId, $storeId);

            $affected = $this->stockRepo->decrementStockWithLock($storeProduct->id, $quantity);

            if ($affected === 0) {
                throw new \RuntimeException('Insufficient stock or concurrent conflict detected.', 409);
            }

            $cartItem = Cart_item::create([
                'user_id'          => $userId,
                'store_product_id' => $storeProduct->id,
                'quantity'         => $quantity,
                'order_id'         => null,
            ]);

            return [
                'cart_item'       => $cartItem,
                'remaining_stock' => $this->stockRepo->getQuantity($storeProduct->id),
            ];
        });
    }

    public function addFlashSale(int $userId, int $productId, int $storeId, int $quantity): array
    {
        $storeProduct = $this->stockRepo->findByProductAndStore($productId, $storeId);

        $this->stockRepo->initRedisStock($storeProduct->id, $storeProduct->quantity);

        $remaining = $this->stockRepo->reserveStockRedis($storeProduct->id, $quantity);

        if ($remaining === -2) {
            throw new \RuntimeException('Flash sale stock not initialized or expired.', 503);
        }

        if ($remaining === -1) {
            throw new \RuntimeException('Out of stock.', 409);
        }

        CreateCartItemJob::dispatch($userId, $storeProduct->id, $quantity);

        return [
            'reserved'        => true,
            'remaining_stock' => $remaining,
        ];
    }
}
