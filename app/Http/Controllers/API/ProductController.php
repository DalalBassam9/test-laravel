<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Models\Rating;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Response;


use App\Enums\ProductTypeEnum;

class ProductController extends Controller
{
    /**
     * Display a listing of the products with filters.
     */
    public function featuredProducts(Request $request)
    {
        $query = Product::query()
            ->where('quantity', '>', 0)
            ->where('is_featured', true)
            ->with('images')
            ->when($request->has('sort'), function ($query) use ($request) {
                match ($request->sort) {
                    'price_asc' => $query->orderBy('price'),
                    'price_desc' => $query->orderByDesc('price'),
                    'newest' => $query->latest(),
                    default => $query->inRandomOrder(),
                };
            });

        return ProductResource::collection($query->latest()->take(8)->get());
    }
    /**
     * Display the specified product.
     */
    public function show(string $id)
    {
        $product = Product::with('images')->findOrFail($id);
        return new ProductResource($product);
    }
    public function getProductBoxs(Request $request)
    {
        try {
            $boxes = Product::where('quantity', '>', 0)->ofType(ProductTypeEnum::Box)
                ->with(['images'])
                ->take(8)
                ->get();

            if ($boxes->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'message' => 'لا توجد صناديق متاحة حالياً',
                    'data' => []
                ], Response::HTTP_OK);
            }

            return ProductResource::collection($boxes);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ تقني',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    public function getProductRegulars()
    {


        $products =     Product::ofType(ProductTypeEnum::Regular)
            ->with(['images'])
            ->orderBy('order', 'asc')
            ->where('quantity', '>', 0)  // ← هذا مهم
            ->get(); // ← هذا مهم
        return ProductResource::collection($products);
    }
}
