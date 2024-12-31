<?php

namespace App\Http\Resources;

use App\Models\FavouriteProduct;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StoreProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        $language = app()->getLocale();
        $user = auth()->id();

        return [
            'product_id' => $this->id,
            'product_name' => $this->{"name_{$language}"},
            'image' => $this->image,
            'stores' => $this->stores->map(function ($store) use ( $user,$language) {
                $storeProduct = $store->pivot;
                $favorite = FavouriteProduct::where('stores_product_id', $store->pivot->id)
                    ->where('user_id', $user)
                    ->exists();
                return [
                    'store_id' => $store->id,
                    'store_name' => $store->{"name_{$language}"},
                    'location' => $store->{"location_{$language}"},
                    'image' => $store->image,
                    'price' => $storeProduct->price,
                    'quantity' => $storeProduct->quantity,
                    'favorite' => $favorite,
                    'category' => [
                        'category_id' => $store->category->id,
                        'category_name' => $store->category->{"name_{$language}"},
                    ],
                ];
            }),
        ];
    }
}
