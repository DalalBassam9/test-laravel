<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WishlistController extends Controller
{
    /**
     * Get all wishlist items
     */
    public function getWishlist()
    {
        $wishlistItems = Auth::user()->wishlists()->get();
        return ProductResource::collection($wishlistItems);
    }

    /**
     * Get authenticated user's wishlist
     */
    public function getUserWishlist()
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $wishlistItems = $user->wishlists()
                            ->with(['product.category', 'product.ratings'])
                            ->get()
                            ->pluck('product');

        return ProductResource::collection($wishlistItems);
    }

    /**
     * Remove product from wishlist
     */
    public function removeFromWishlist($productId)
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $product = Product::findOrFail($productId);
        $user->wishlists()->detach($product->id);

        return response()->json([
            'message' => 'Product removed successfully',
            'product' => new ProductResource($product)
        ]);
    }

    /**
     * Add product to wishlist
     */
    public function addToWishlist(Request $request)
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $request->validate([
            'product_id' => 'required|exists:products,id'
        ]);

        $product = Product::findOrFail($request->product_id);

        // Check if product already exists in wishlist
        if ($user->wishlists()->where('product_id', $product->id)->exists()) {
            return response()->json([
                'message' => 'Product already in wishlist'
            ], 409);
        }

        $user->wishlists()->attach($product->id);

        return new ProductResource($product);
    }
}