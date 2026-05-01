<?php

namespace App\Http\Controllers\API;

use App\Models\Rating;
use App\Models\Product;
use App\Http\Requests\RatingRequest;
use App\Http\Resources\RatingResource;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class RatingController extends Controller
{
    
    public function getRatingsProduct($productId)
    {
        $product  = Product::findOrFail($productId);
        $ratings = Rating::with('user')->where('product_id',$productId)->orderBy('created_at', 'desc')->get();
        return RatingResource::collection($ratings);
    }

    public function getUserRatings()
    {
        $ratings = Rating::where('user_id', Auth::user()->id)->orderBy('created_at', 'desc')->get();
        return RatingResource::collection($ratings);
    }

    public function storeRatingOnProduct(RatingRequest $request,$product)
    {
        $product  = Product::findOrFail($product);


        $rating = Rating::create([
            'product_id'=> $product->product_id,
            'user_id'=>Auth::user()->id,
            'rating'   => $request->rating,
            'review'    => $request->review,
        ]);

        return new RatingResource($rating);
    }
}
