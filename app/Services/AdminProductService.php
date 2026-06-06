<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Store;
use App\Repositories\StoreProductRepository;
use Illuminate\Support\Facades\DB;

class AdminProductService
{
    public function __construct(private StoreProductRepository $stockRepo) {}

   
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
