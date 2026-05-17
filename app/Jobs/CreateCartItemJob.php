<?php

namespace App\Jobs;

use App\Models\Cart_item;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

/**
 * Flash Sale Strategy - Async DB write after Redis reservation.
 *
 * Flow:
 *   1. CartService::addFlashSale() reserves stock in Redis (atomic, <1ms)
 *   2. This job is dispatched immediately → user gets 200 OK
 *   3. This job writes the cart item to DB in the background
 *
 * On permanent failure (all retries exhausted):
 *   failed() restores the Redis stock so the slot is released back to the pool.
 */
class CreateCartItemJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(
        public readonly int $userId,
        public readonly int $storeProductId,
        public readonly int $quantity,
    ) {}

    public function handle(): void
    {
        $cartItem = Cart_item::create([
            'user_id'          => $this->userId,
            'store_product_id' => $this->storeProductId,
            'quantity'         => $this->quantity,
            'order_id'         => null,
        ]);

        Log::info('[FLASH_SALE] Cart item written to DB', [
            'cart_item_id'     => $cartItem->id,
            'user_id'          => $this->userId,
            'store_product_id' => $this->storeProductId,
            'quantity'         => $this->quantity,
        ]);
    }

    /**
     * Called only after ALL retries are exhausted.
     * Restores the Redis stock so the slot is returned to the pool.
     * Without this, a failed job would silently "eat" stock quota.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('[FLASH_SALE] CreateCartItemJob failed — restoring Redis stock', [
            'user_id'          => $this->userId,
            'store_product_id' => $this->storeProductId,
            'quantity'         => $this->quantity,
            'error'            => $exception->getMessage(),
        ]);

        Redis::incrby("stock:store_product:{$this->storeProductId}", $this->quantity);
    }
}
