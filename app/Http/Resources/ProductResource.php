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
    public function toArray(Request $request): array //    'name', 'description', 'image'
    {
        $imageUrl = Storage::url($this->image);
        $data = [
            'id'=>$this->id,
            'name'=>$this->name,
            'description'=>$this->description,
            'image'=>asset($imageUrl) ?? null,
            'price' => $this->pivot->price, // Comes from store_product table
            'quantity' => $this->pivot->quantity, // Comes from store_product table
        ];
        return $data;
    }
}
