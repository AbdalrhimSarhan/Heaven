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
        // Retrieve the 'lang' parameter passed as additional data
        $language = app()->getLocale();
        $user = auth()->id();

        // Check if store_product data is available
        $store_product = $this->storeProduct??$this->pivot;

        $favorite = FavouriteProduct::where('stores_product_id', $store_product->id)
            ->where('user_id', $user)
            ->first();

        if ($favorite) {
            $favorite = true;
        } else {
            $favorite = false;
        }

        $name = $language === 'ar' ? $this->name_ar : $this->name_en;
        $description = $language === 'ar' ? $this->description_ar : $this->description_en;

        $imageUrl = Storage::url($this->product_image);

        $data = [
            'id' => $this->id,
            'name' => $name,
            'description' => $description,
            'image' => asset($imageUrl) ?? null,
            'price' => $store_product->price, // Comes from store_product table
            'quantity' => $store_product->quantity, // Comes from store_product table
            'favorite' => $favorite,
            'stores_product_id' => $store_product->id,
        ];

        return $data;
    }
}
