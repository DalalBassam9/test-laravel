<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\StoreProductRequest;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $products = Product::with('images', 'categories')->paginate(10);
        return ProductResource::collection($products)->additional([
            'current_page' => $products->currentPage(),
            'last_page' => $products->lastPage(),
            'total' => $products->total(),
            'from' => $products->firstItem(),
            'to' => $products->lastItem(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProductRequest $request)
    {

        $data = $request->validated();
        $data['is_featured'] = $request->boolean('is_featured');
        $product = Product::create($data);

        if ($request->has('category_ids')) {
            $product->categories()->sync($request->category_ids);
        }

        $mainImageIndex = $request->input('main_image_index'); // الحصول على الـ index
        $this->uploadImages($product, $request->file('images'), $mainImageIndex); // إرسال الـ index
        return new ProductResource($product->load('images', 'categories'));
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $product = Product::with('categories')->findOrFail($id);
        return new ProductResource($product->load('images', 'categories'));
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(StoreProductRequest $request, $id)
    {
        $product = Product::findOrFail($id);

        // تحديث بيانات المنتج الأساسية
        $data = $request->validated();
        $data['is_featured'] = $request->boolean('is_featured');
        $product->update($data);

        // تحديث التصنيفات
        if ($request->has('category_ids')) {
            $product->categories()->sync($request->category_ids);
        }

        // حذف الصور المحددة
        if ($request->has('deleted_images')) {
            foreach ($request->deleted_images as $imageId) {
                $image = $product->images()->find($imageId);
                if ($image) {
                    // حذف الصورة من التخزين
                    Storage::disk('public')->delete($image->path);
                    // حذف السجل من قاعدة البيانات
                    $image->delete();
                }
            }
        }

        // رفع الصور الجديدة
        if ($request->hasFile('images')) {
            $this->uploadImages($product, $request->file('images'));
        }

        // تعيين الصورة الرئيسية (اختياري)
        if ($request->has('main_image_id')) {
            // ضبط جميع الصور على false
            $product->images()->update(['is_main' => false]);

            // تعيين الصورة المختارة كصورة رئيسية
            $mainImage = $product->images()->find($request->main_image_id);
            if ($mainImage) {
                $mainImage->is_main = true;
                $mainImage->save();
            }
        }

        return new ProductResource($product);
    }


    /**
     * Remove the specified resource from storage.
     */


    public function destroy(Product $product)
    {
        try {
            // حذف الصور المرتبطة
            foreach ($product->images as $image) {
                if (Storage::disk('public')->exists($image->path)) {
                    Storage::disk('public')->delete($image->path);
                }
                $image->delete();
            }

            // حذف المنتج نفسه
            $product->delete();

            return response()->json([
                'message' => 'Product deleted successfully',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete product',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    /**
     * Upload images for the product.
     */
    protected function uploadImages(Product $product, $images)
    {
        // الحصول على رقم الصورة الرئيسية من الطلب
        $mainImageIndex = request()->input('main_image_index');

        // التأكد من أن $mainImageIndex هو قيمة صحيحة
        if ($mainImageIndex !== null) {
            // التنقل عبر الصور
            foreach ($images as $index => $image) {
                $path = $image->store('product_images', 'public');

                $isMain = false;
                // إذا كانت الصورة الحالية هي الصورة الرئيسية
                if ($index == $mainImageIndex) {
                    $isMain = true;
                }

                // لو الصورة الرئيسية الجديدة = true, نخلي الباقي false
                if ($isMain) {
                    $product->images()->update(['is_main' => false]);
                }

                // إضافة الصورة
                $product->images()->create([
                    'path' => $path,
                    'is_main' => $isMain,
                ]);
            }
        }
    }


    /**
     * Delete a specific image.
     */
    public function deleteImage(ProductImage $image)
    {
        Storage::disk('public')->delete($image->path);
        $image->delete();

        return response()->json(['message' => 'Image deleted successfully']);
    }

    /**
     * Search products by name or description
     */
    public function search(Request $request)
    {
        $query = $request->input('query');

        $products = Product::where('name', 'like', "%$query%")
            ->orWhere('description', 'like', "%$query%")
            ->with('images', 'categories')
            ->paginate(10);

        return ProductResource::collection($products);
    }
}
