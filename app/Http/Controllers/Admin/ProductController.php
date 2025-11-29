<?php

namespace App\Http\Controllers\Admin;

use App\Jobs\ProcessProductImage;
use App\Models\Banner;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Support\Str;
use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use Intervention\Image\Facades\Image;

use App\Http\Resources\ProductResource;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Product::query();

        // filter by category_id if provided
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->query('category_id'));
        }
        if ($request->filled('sub_category_id')) {
            $query->where('sub_category_id', $request->query('sub_category_id'));
        }

        return ProductResource::collection(executeQuery($query));
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string',
            'description' => 'nullable',
            'category_id' => 'required',
            'sub_category_id' => 'nullable|integer',
            'status' => 'nullable',
            'images' => 'required ',
        ]);

        $product = Product::create($data);
        // Process images
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                // Save uploaded file to temporary location first
                $tempFilename = 'temp/' . uniqid() . '.' . $image->getClientOriginalExtension();
                $tempPath = Storage::disk('public')->putFileAs('temp', $image, basename($tempFilename));

                // Create ProductImage record
                $productImage = ProductImage::create([
                    'product_id' => $product->id,
                    'original_image' => null, // Will be updated by job
                    'image' => null, // Will be updated by job
                ]);

                // Dispatch job with ProductImage ID and temp file path
                ProcessProductImage::dispatch(
                    $productImage->id,
                    $tempPath,
                    $image->getClientOriginalExtension()
                );
            }
        }

        return new ProductResource($product);
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        $product->load('images');

        return new ProductResource($product);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product)
    {
        $data = $request->validate([
            'name' => 'required|string',
            'description' => 'nullable',
            'category_id' => 'required',
            'sub_category_id' => 'nullable|integer',
            'status' => 'nullable',
        ]);

        // Process images
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                // Create ProductImage record first (with null paths)
                $productImage = ProductImage::create([
                    'product_id' => $product->id,
                    'original_image' => null, // Will be updated by job
                    'image' => null, // Will be updated by job
                ]);

                // Dispatch job to process image in background
                ProcessProductImage::dispatch($productImage, $image);
            }
        }


        return $product->update($data);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        return $product->delete();
    }

    public function imageResize($file, $width, $height = null, $store_path = '')
    {
        $resizeImage = Image::make($file)->encode('webp', 70);

        $resizeImageName = $store_path . '/' . Str::uuid() . '.webp';

        Storage::put($resizeImageName, $resizeImage);

        return $resizeImageName;
    }

    public function getBanners()
    {
        return BannerResource::collection(executeQuery(Banner::query()->where('type', BannerTypes::$PRODUCT_BANNER)->orderBy('sort')));
    }

    public function storeBanners(Request $request)
    {
        $data = $request->validate([
            'image' => 'required|mimes:jpg,webp,jpeg,mp4,gif,png',
            'description' => 'nullable|string',
            'title' => 'nullable|string'
        ]);

        $bannerWidth = 1900;
        $sort = Banner::where('type', BannerTypes::$PRODUCT_BANNER)->max('sort');

        $data['original_image'] = $request->file('image')->store('product_banner/original');
        if ($request->file('image')->getClientOriginalExtension() == 'mp4') {
            $data['image'] = $request->file('image')->store('product_banner');
        } else {
            $data['image'] = $this->imageResize($request->file('image'), $bannerWidth, $height = null, $folderName = 'product_banner');
        }

        if ($sort == null || $sort == '')
            $sort = 0;

        $data['type'] = BannerTypes::$PRODUCT_BANNER;
        $data['description'] = $request->description;
        $data['sort'] = $sort++;
        $data['title'] = $request->title;

        $banner = Banner::create($data);

        return new BannerResource($banner);
    }

    public function updateBanners(Request $request, Banner $banner)
    {
        $data = $request->validate([
            'description' => 'nullable|string',
            'title' => 'nullable|string'
        ]);

        if ($request->hasFile('image')) {
            $request->validate([
                'image' => 'required|mimes:jpg,webp,jpeg,mp4,gif,png'
            ]);

            $data['original_image'] = $request->file('image')->store('product_banner/original');
            if ($request->file('image')->getClientOriginalExtension() == 'mp4') {
                $data['image'] = $request->file('image')->store('product_banner');
            } else {
                $bannerWidth = 1900;
                $data['image'] = $this->imageResize($request->file('image'), $bannerWidth, $height = null, $folderName = 'product_banner');
            }

            if ($banner->original_image && Storage::exists($banner->original_image)) {
                Storage::delete($banner->original_image);
            }

            if ($banner->image && Storage::exists($banner->image)) {
                Storage::delete($banner->image);
            }
        }

        $data['type'] = BannerTypes::$PRODUCT_BANNER;

        return $banner->update($data);
    }

    public function bannerSort(Request $request)
    {
        $sort = 1;
        foreach ($request->ids as $id) {
            Banner::where('id', $id)->update(['sort' => $sort]);
            $sort++;
        }

        return true;
    }

    public function getAdditionalProducts()
    {
        return BannerResource::collection(executeQuery(Banner::query()->where('type', BannerTypes::$ADDITIONAL_PRODUCT)->orderBy('sort')));
    }

    public function storeAdditionalProducts(Request $request)
    {
        $data = $request->validate([
            'image' => 'required|mimes:jpg,webp,jpeg,mp4,gif,png',
            'description' => 'nullable|string',
            'title' => 'nullable|string'
        ]);

        $bannerWidth = 950;
        $sort = Banner::where('type', BannerTypes::$ADDITIONAL_PRODUCT)->max('sort');

        $data['original_image'] = $request->file('image')->store('additional_products/original');
        if ($request->file('image')->getClientOriginalExtension() == 'mp4') {
            $data['image'] = $request->file('image')->store('additional_products');
        } else {
            $data['image'] = $this->imageResize($request->file('image'), $bannerWidth, $height = null, $folderName = 'additional_products');
        }

        if ($sort == null || $sort == '')
            $sort = 0;

        $data['type'] = BannerTypes::$ADDITIONAL_PRODUCT;
        $data['description'] = $request->description;
        $data['sort'] = $sort++;
        $data['title'] = $request->title;

        $banner = Banner::create($data);

        return new BannerResource($banner);
    }

    public function updateAdditionalProducts(Request $request, Banner $banner)
    {
        $data = $request->validate([
            'description' => 'nullable|string',
            'title' => 'nullable|string'
        ]);

        if ($request->hasFile('image')) {
            $request->validate([
                'image' => 'required|mimes:jpg,webp,jpeg,mp4,gif,png'
            ]);

            $data['original_image'] = $request->file('image')->store('additional_products/original');
            if ($request->file('image')->getClientOriginalExtension() == 'mp4') {
                $data['image'] = $request->file('image')->store('additional_products');
            } else {
                $bannerWidth = 1900;
                $data['image'] = $this->imageResize($request->file('image'), $bannerWidth, $height = null, $folderName = 'additional_products');
            }

            if ($banner->original_image && Storage::exists($banner->original_image)) {
                Storage::delete($banner->original_image);
            }

            if ($banner->image && Storage::exists($banner->image)) {
                Storage::delete($banner->image);
            }
        }

        $data['type'] = BannerTypes::$ADDITIONAL_PRODUCT;

        return $banner->update($data);
    }

    public function additionalProductSort(Request $request)
    {
        $sort = 1;
        foreach ($request->ids as $id) {
            Banner::where('id', $id)->update(['sort' => $sort]);
            $sort++;
        }

        return true;
    }

    public function getProductBottomBanner()
    {
        return BannerResource::collection(executeQuery(Banner::query()->where('type', BannerTypes::$PRODUCT_BOTTOM_BANNER)->orderBy('sort')));
    }

    public function storeProductBottomBanner(Request $request)
    {
        $data = $request->validate([
            'image' => 'required|mimes:jpg,webp,jpeg,mp4,gif,png',
            'description' => 'nullable|string',
            'title' => 'nullable|string'
        ]);

        $bannerWidth = 1900;
        $sort = Banner::where('type', BannerTypes::$PRODUCT_BOTTOM_BANNER)->max('sort');

        $data['original_image'] = $request->file('image')->store('product_bottom_banner/original');
        if ($request->file('image')->getClientOriginalExtension() == 'mp4') {
            $data['image'] = $request->file('image')->store('product_bottom_banner');
        } else {
            $data['image'] = $this->imageResize($request->file('image'), $bannerWidth, $height = null, $folderName = 'product_bottom_banner');
        }

        if ($sort == null || $sort == '')
            $sort = 0;

        $data['type'] = BannerTypes::$PRODUCT_BOTTOM_BANNER;
        $data['description'] = $request->description;
        $data['sort'] = $sort++;
        $data['title'] = $request->title;

        $banner = Banner::create($data);

        return new BannerResource($banner);
    }

    public function updateProductBottomBanner(Request $request, Banner $banner)
    {
        $data = $request->validate([
            'description' => 'nullable|string',
            'title' => 'nullable|string'
        ]);

        if ($request->hasFile('image')) {
            $request->validate([
                'image' => 'required|mimes:jpg,webp,jpeg,mp4,gif,png'
            ]);

            $data['original_image'] = $request->file('image')->store('product_bottom_banner/original');
            if ($request->file('image')->getClientOriginalExtension() == 'mp4') {
                $data['image'] = $request->file('image')->store('product_bottom_banner');
            } else {
                $bannerWidth = 1900;
                $data['image'] = $this->imageResize($request->file('image'), $bannerWidth, $height = null, $folderName = 'product_bottom_banner');
            }

            if ($banner->original_image && Storage::exists($banner->original_image)) {
                Storage::delete($banner->original_image);
            }

            if ($banner->image && Storage::exists($banner->image)) {
                Storage::delete($banner->image);
            }
        }

        $data['type'] = BannerTypes::$PRODUCT_BOTTOM_BANNER;

        return $banner->update($data);
    }

    public function productBottomBannerSort(Request $request)
    {
        $sort = 1;
        foreach ($request->ids as $id) {
            Banner::where('id', $id)->update(['sort' => $sort]);
            $sort++;
        }

        return true;
    }
}
