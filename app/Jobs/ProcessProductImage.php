<?php

namespace App\Jobs;

use App\Models\ProductImage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class ProcessProductImage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $productImageId;
    protected $tempFilePath;
    protected $originalExtension;

    /**
     * Create a new job instance.
     */
    public function __construct($productImageId, $tempFilePath, $originalExtension)
    {
        $this->productImageId = $productImageId;
        $this->tempFilePath = $tempFilePath;
        $this->originalExtension = $originalExtension;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $productImage = ProductImage::findOrFail($this->productImageId);

        // Generate unique string for filename
        $str = $this->generateUniqueString();

        // 1. Save Original Image (just renamed)
        $originalFilename = $str . '.' . $this->originalExtension;
        $originalPath = 'products/original/' . $originalFilename;

        // Copy from temp to original location
        Storage::disk('public')->copy($this->tempFilePath, $originalPath);

        // 2. Process and Convert to WebP
        $manager = new ImageManager(new Driver());
        $tempFullPath = storage_path('app/public/' . $this->tempFilePath);
        $image = $manager->read($tempFullPath);

        // Resize image (adjust dimensions as needed)
        $image->scale(width: 800); // Maintains aspect ratio

        // Convert to WebP and save
        $webpFilename = $str . '.webp';
        $webpPath = storage_path('app/public/products/webp/' . $webpFilename);

        // Ensure directory exists
        if (!file_exists(dirname($webpPath))) {
            mkdir(dirname($webpPath), 0755, true);
        }

        // Save as WebP with quality 80
        $image->toWebp(80)->save($webpPath);

        // 3. Update ProductImage record
        $productImage->update([
            'original_image' => 'products/original/' . $originalFilename,
            'image' => 'products/webp/' . $webpFilename,
        ]);

        // 4. Delete temporary file
        Storage::disk('public')->delete($this->tempFilePath);
    }

    /**
     * Generate unique string for filename
     */
    private function generateUniqueString(): string
    {
        return 'STR' . time() . rand(1000, 9999);
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        // Log the error
        \Log::error('Product image processing failed: ' . $exception->getMessage());

        // Delete temp file if exists
        if (Storage::disk('public')->exists($this->tempFilePath)) {
            Storage::disk('public')->delete($this->tempFilePath);
        }

        // Optionally delete the ProductImage record
        ProductImage::find($this->productImageId)?->delete();
    }
}