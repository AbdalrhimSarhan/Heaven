<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

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
        return [
            'product_name' =>  $language === 'ar' ? $this->store_product->product->name_ar : $this->store_product->product->name_en,
            'product_image' => $this->store_product->product->image, // Assuming "image" is a column in the products table
            'quantity' => $this->quantity,
        ];
    }
}
