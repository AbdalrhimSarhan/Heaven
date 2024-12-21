<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Retrieve the 'lang' parameter passed as additional data
        $language = app()->getLocale();

        $name = $language === 'ar' ? $this->name_ar : $this->name_en;
        $description = $language === 'ar' ? $this->description_ar : $this->description_en;

        $imageUrl = Storage::url($this->image);

        return [
            'id' => $this->id,
            'name' => $name,
            'description' => $description,
            'image' => asset($imageUrl) ?? null,
            'price' => $this->pivot->price, // Comes from store_product table
            'quantity' => $this->pivot->quantity, // Comes from store_product table
        ];
    }
}
