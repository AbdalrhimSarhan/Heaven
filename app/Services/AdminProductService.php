<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Store;
use App\Repositories\StoreProductRepository;
use Illuminate\Support\Facades\DB;

class AdminProductService
{
    public function __construct(private StoreProductRepository $stockRepo) {}

    /**
     * Update product metadata and/or store stock with a Pessimistic Write Lock.
     *
     * Lock choice — lockForUpdate (X lock):
     *   Admin replaces the ABSOLUTE stock quantity, not a delta.
     *   During the update window, any concurrent DB decrement
     *   (cart/safe uses lockForUpdate, cart/integrity uses WHERE quantity>=qty UPDATE)
     *   must wait, otherwise the decrement would land on a value that is about to be
     *   replaced, and the subsequent Redis sync would reflect the wrong number.
     *   Flash-sale requests bypass DB entirely (Redis Lua), so they are not blocked.
     *
     * AOP layer — StoreProductObserver:
     *   When $storeProduct->update() fires the Eloquent `updated` event,
     *   the Observer intercepts it and calls Redis::set(key, newQty).
     *   The Service never touches Redis directly; cache sync is a cross-cutting
     *   concern handled transparently by the Observer.
     */
    public function updateProduct(int $storeId, int $productId, array $validated): array
    {
        $store = Store::findOrFail($storeId);
        $product     = Product::findOrFail($productId);
        $storeProduct = null;

        DB::transaction(function () use ($storeId, $productId, $validated, $product, &$storeProduct) {

            $product->update([
                'name_en'        => $validated['name_en']        ?? $product->name_en,
                'name_ar'        => $validated['name_ar']        ?? $product->name_ar,
                'description_en' => $validated['description_en'] ?? $product->description_en,
                'description_ar' => $validated['description_ar'] ?? $product->description_ar,
                'product_image'  => $validated['product_image']  ?? $product->product_image,
            ]);

            if (isset($validated['price']) || isset($validated['quantity'])) {
                $storeProduct = $this->stockRepo->findByProductAndStoreWithLock($productId, $storeId);

                $storeProduct->update([
                    'price'    => $validated['price']    ?? $storeProduct->price,
                    'quantity' => $validated['quantity'] ?? $storeProduct->quantity,
                ]);
                // ↑ Eloquent `updated` event fires here →
                //   StoreProductObserver::updated() intercepts →
                //   Redis::set(stock:store_product:{id}, newQty)   [AOP]
            } else {
                $storeProduct = $this->stockRepo->findByProductAndStore($productId, $storeId);
            }
        });

        return ['product' => $product, 'store_product' => $storeProduct];
    }
}
