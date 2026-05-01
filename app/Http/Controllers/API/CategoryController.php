<?php

namespace App\Http\Controllers\API;

use App\Http\Resources\CategoryResource;
use App\Http\Resources\ProductResource;
use App\Models\Category;
use App\Models\Product;
use App\Models\Rating;
use App\Enums\ProductTypeEnum;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class CategoryController extends Controller
{

    /**
     * Get all categories with pagination.
     */
    public function index()
    {
        $categories = Category::get();
        return CategoryResource::collection($categories);
    }


    public function tree()
    {
        $categories = Category::with(['children' => function ($q) {
            $q->orderBy('name', 'asc'); // ترتيب الأبناء بالاسم
        }])
            ->whereNull('parent_id')
            ->orderBy('name', 'asc') // ترتيب الآباء بالاسم
            ->get();

        return CategoryResource::collection($categories);
    }


    public function getCategory(string $id)
    {
        $category = Category::findOrFail($id);
        return new CategoryResource($category);
    }

    /**
     * Get products that belong to a specific category, with filters and sorting.
     */
    public function getProductsByCategory($id, Request $request)
    {
        $category = Category::findOrFail($id);

        $query = Product::where('quantity', '>', 0)->where('category_id', $id);

        // Filter by product status
        if ($request->filled('productStatus')) {
            match ($request->productStatus) {
                'stock' => $query->where('quantity', '>', 0),
                'out of stock' => $query->where('quantity', '=', 0),
                default => null,
            };
        }

        // Apply sorting
        match ($request->sort) {
            'price_asc' => $query->orderBy('price', 'asc'),
            'price_desc' => $query->orderBy('price', 'desc'),
            'rating' => $query
                ->addSelect([
                    'average_rating' => Rating::select(DB::raw('AVG(rating)'))
                        ->whereColumn('productId', 'products.productId')
                ])
                ->orderBy('average_rating', 'desc'),
            'newest', null => $query->orderBy('created_at', 'desc'),
            default => null,
        };

        // Paginate the results
        $products = $query->paginate(3);

        return ProductResource::collection($products);
    }
    public function getCategoryProducts($slug)
    {
        $category = Category::where('slug', $slug)->firstOrFail();


        // جلب المنتجات التي تنتمي لهذه الفئة (عبر العلاقة many-to-many)
        $productsQuery = Product::where('quantity', '>', 0)->with('images', 'categories')
            ->whereHas('categories', function ($q) use ($category) {
                $q->where('categories.id', $category->id);
            });

        $products = $productsQuery->get();

        return response()->json([
            'category' => new CategoryResource($category),
            'products' => ProductResource::collection($products)
        ]);
    }
}
