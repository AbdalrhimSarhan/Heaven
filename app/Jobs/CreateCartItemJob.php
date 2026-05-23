<?php

namespace App\Jobs;

use App\Models\Cart_item;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

/**
 * Flash Sale Strategy - Async DB write after Redis reservation.
 *
 * Flow:
 *   1. CartService::addFlashSale() reserves stock in Redis (atomic, <1ms)
 *   2. User gets 200 OK immediately — no DB wait
 *   3. This job runs in background: creates cart_item AND decrements store_product.quantity
 *
 * Why DB::table (not Eloquent) for the decrement:
 *   Eloquent update() fires StoreProductObserver → Redis::set(key, newQty)
 *   which would overwrite the correct Redis counter mid-flash-sale.
 *   Query builder bypasses Observer, keeping Redis untouched (already correct).
 *
 * On permanent failure: failed() restores BOTH Redis and DB stock.
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
        DB::transaction(function () {

             DB::table('store_product')
                ->where('id', $this->storeProductId)
                ->decrement('quantity', $this->quantity);
                
            $cartItem = Cart_item::create([
                'user_id'          => $this->userId,
                'store_product_id' => $this->storeProductId,
                'quantity'         => $this->quantity,
                'order_id'         => null,
            ]);


            Log::info('[FLASH_SALE] Cart item written and stock decremented', [
                'cart_item_id'     => $cartItem->id,
                'user_id'          => $this->userId,
                'store_product_id' => $this->storeProductId,
                'quantity'         => $this->quantity,
            ]);
        });
    }

    /**
     * Called only after ALL retries are exhausted.
     * Restores BOTH Redis stock and DB quantity so the slot is fully released.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('[FLASH_SALE] CreateCartItemJob failed — restoring Redis and DB stock', [
            'user_id'          => $this->userId,
            'store_product_id' => $this->storeProductId,
            'quantity'         => $this->quantity,
            'error'            => $exception->getMessage(),
        ]);

        Redis::incrby("stock:store_product:{$this->storeProductId}", $this->quantity);

        DB::table('store_product')
            ->where('id', $this->storeProductId)
            ->increment('quantity', $this->quantity);
    }
}
