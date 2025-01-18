<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class FavouriteProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Retrieve the 'lang' parameter passed as additional data
        $language = $this->additional['lang'] ?? 'en';

        $name = $language === 'ar' ? $this->store_product->product->name_ar : $this->store_product->product->name_en;
        $description = $language === 'ar' ? $this->store_product->product->description_ar : $this->store_product->product->description_en;

        $imageUrl = Storage::url($this->store_product->product->image);

        return [
            'id' => $this->id,
            'product_id'=>$this->store_product->product_id,
            'name' => $name,
            'description' => $description,
            'image' => asset($imageUrl) ?? null,
            'price' => $this->store_product->price, // Comes from store_product table
            'quantity' => $this->store_product->quantity, // Comes from store_product table
            'store_id' => $this->store_product->store_id,
            'category_id' => $this->store_product->store->category_id,
        ];
    }
}
