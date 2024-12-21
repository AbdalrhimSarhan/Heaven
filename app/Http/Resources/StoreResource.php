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
    public function toArray(Request $request): array
    {
        $language = app()->getLocale();
        $imageUrl = Storage::url($this->image);
        $data = [
            'id' => $this->id,
            'name' => $language === 'ar' ? $this->name_ar : $this->name_en,
            'location' => $language === 'ar' ? $this->location_ar : $this->location_en,
            'image' => asset($imageUrl) ?? null,
        ];
        return $data;
    }

}
