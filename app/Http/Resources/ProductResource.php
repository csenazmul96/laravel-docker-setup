<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'category_id' => $this->category_id,
            'sub_category_id' => $this->sub_category_id,
            'status' => $this->status,
            'images' => $this->images->map(function ($image) {
                return [
                    'id' => $image->id,
                    'url' => Storage::url($image->image),
                ];
            }),
            'created_at' => $this->created_at ? $this->created_at->format('m-d-Y h:i:s A') : '',
        ];
    }
}
