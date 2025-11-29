<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description', 'category_id', 'sub_category_id', 'status', 'original_image', 'image'];

    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }

    protected static function booted()
    {

    }
}
