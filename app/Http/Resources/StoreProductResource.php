<?php

namespace App\Http\Resources;

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

        return [
            'product_id' => $this->id,
            'product_name' => $this->{"name_{$language}"},
            'stores' => $this->stores->map(function ($store) use ($language) {
                $storeProduct = $store->pivot;
                return [
                    'store_id' => $store->id,
                    'store_name' => $store->{"name_{$language}"},
                    'location' => $store->{"location_{$language}"},
                    'price' => $storeProduct->price,
                    'quantity' => $storeProduct->quantity,
                    'category' => [
                        'category_id' => $store->category->id,
                        'category_name' => $store->category->{"name_{$language}"},
                    ],
                ];
            }),
        ];
    }
}
