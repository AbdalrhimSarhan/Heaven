<?php

namespace App\Http\Resources;

use App\Models\FavouriteProduct;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class ProductResource extends JsonResource
{
    protected $storeProduct;

    public function __construct($resource, $storeProduct = null)
    {
        parent::__construct($resource);
        $this->storeProduct = $storeProduct;
    }

    public function toArray(Request $request): array
    {
        $language = app()->getLocale();
        $user = auth()->id();

        $store_product = $this->storeProduct ?? $this->pivot;

        if (!is_object($store_product)) {
            $store_product = (object) ['price' => null, 'quantity' => null, 'id' => null];
        }

        $favorite = FavouriteProduct::where('stores_product_id', $store_product->id)
            ->where('user_id', $user)
            ->exists();
        $store = Store::where('id', $store_product->store_id)->first();

        $name = $language === 'ar' ? $this->name_ar : $this->name_en;
        $description = $language === 'ar' ? $this->description_ar : $this->description_en;

        $imageUrl = Storage::url($this->product_image);

        return [
            'id' => $this->id,
            'name' => $name,
            'description' => $description,
            'image' => asset($imageUrl) ?? null,
            'price' => $store_product->price,
            'quantity' => $store_product->quantity,
            'favorite' => $favorite,
            'stores_product_id' => $store_product->id,
            'name_of_store'=> $store->{"name_{$language}"},
        ];
    }

}
