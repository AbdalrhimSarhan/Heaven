<?php

namespace App\Http\Resources;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class StoreResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array  // 'name','location','category_id','image'
    {
        $imageUrl = Storage::url($this->image);
        $data = [
            'id' =>$this->id,
            'name'=>$this->name,
            'image'=>asset($imageUrl) ?? null,
            'location'=>$this->location,
        ];
        return $data;
    }

}
