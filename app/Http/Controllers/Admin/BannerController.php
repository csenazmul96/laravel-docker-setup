<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Imagick\Driver;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;


class BannerController extends Controller
{
    public function store(Request $request)
    {
        // Validate the uploaded image
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
        ]);

        // Get the uploaded file
        $image = $request->file('image');

        // Create ImageManager with Imagick driver
        $driver = new Driver();  // Explicit Imagick driver instance
        $manager = new ImageManager($driver);

        // Create an Intervention Image instance from the uploaded file
        $imageInstance = $manager->read($image);

        // Perform any image manipulation (scale to 300x300 as an example)
        $imageInstance->scale(300, 300);

        // Create a new image name using Str::slug() to ensure a clean name
        $imageName = Str::slug(pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME)) . '-' . uniqid() . '.' . $image->getClientOriginalExtension();

        // Store the image in the 'public' disk (storage/app/public)
        $path = Storage::disk('public')->put($imageName, (string) $imageInstance->encode());

        // Optionally, return the file URL
        $imageUrl = Storage::url($imageName);

        return response()->json([
            'message' => 'Image uploaded successfully.',
            'image_url' => $imageUrl,
        ]);
    }
}
