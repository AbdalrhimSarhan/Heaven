<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $imageUrl = Storage::url($this->image);
        $data = [
            'id' =>$this->id,
            'name'=>$this->first_name.' '.$this->last_name,
            'image'=>asset($imageUrl) ?? null,
            'location'=>$this->location,
            'mobile'=>$this->mobile
        ];
        return $data;
    }
}
