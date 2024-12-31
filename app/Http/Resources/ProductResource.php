<?php

namespace App\Http\Resources;

use App\Models\FavouriteProduct;
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

        // Check if store_product data is available
        $store_product = $this->storeProduct ?? $this->pivot;

        // Ensure $store_product is an object before accessing its properties
        if (!is_object($store_product)) {
            $store_product = (object) ['price' => null, 'quantity' => null, 'id' => null];
        }

        $favorite = FavouriteProduct::where('stores_product_id', $store_product->id)
            ->where('user_id', $user)
            ->exists(); // Simplified the favorite check

        $name = $language === 'ar' ? $this->name_ar : $this->name_en;
        $description = $language === 'ar' ? $this->description_ar : $this->description_en;

        $imageUrl = Storage::url($this->product_image);

        return [
            'id' => $this->id,
            'name' => $name,
            'description' => $description,
            'image' => asset($imageUrl) ?? null,
            'price' => $store_product->price, // Safely access price
            'quantity' => $store_product->quantity, // Safely access quantity
            'favorite' => $favorite,
            'stores_product_id' => $store_product->id,
        ];
    }

}
