<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class CartItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $language = app()->getLocale();
        $imageUrl = Storage::url($this->store_product->product->image);
        return [
            'product_name' =>  $language === 'ar' ? $this->store_product->product->name_ar : $this->store_product->product->name_en,
            'product_image' => asset($imageUrl),
            'quantity' => $this->quantity,
            'price' => $this->quantity * $this->store_product->price,
        ];
    }
}
