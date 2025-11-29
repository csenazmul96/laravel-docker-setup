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
            'category'=> new ProductCategoryResource($this->whenLoaded('category')),
            'sub_category_id' => $this->sub_category_id,
            'sub_category' => new ProductSubCategoryResource($this->whenLoaded('subCategory')),
            'status' => $this->status,
            'original_image' => $this->original_image ? Storage::url($this->original_image) : null,
            'image' => $this->image ? Storage::url($this->image) : null,
            'created_at'=> $this->created_at ? $this->created_at->format('m-d-Y h:i:s A') : '',
        ];
    }
}
