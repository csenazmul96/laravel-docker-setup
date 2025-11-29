<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description', 'category_id', 'sub_category_id', 'status', 'original_image', 'image'];

    public function category(){
        return $this->hasOne(ProductCategory::class, 'id', 'category_id');
    }

    public function subCategory(){
        return $this->hasOne(ProductSubCategory::class, 'id', 'sub_category_id');
    }

    protected static function booted()
    {
        static::updating(function ($product) {
            if ($product->image && $product->image != $product->getOriginal('image')) {
                if ($product->getOriginal('image') && Storage::exists($product->getOriginal('image')))
                    Storage::delete($product->getOriginal('image'));
            }
            if ($product->original_image && $product->original_image != $product->getOriginal('original_image')) {
                if ($product->getOriginal('original_image') && Storage::exists($product->getOriginal('original_image')))
                    Storage::delete($product->getOriginal('original_image'));
            }
        });

        static::deleting(function ($product) {
            if ($product->image && Storage::exists($product->image))
                Storage::delete($product->image);
            if ($product->original_image && Storage::exists($product->original_image))
                Storage::delete($product->original_image);
        });
    }
}
