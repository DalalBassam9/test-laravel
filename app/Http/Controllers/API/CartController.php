<?php

namespace App\Http\Controllers\API;

use App\Models\Cart;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Support\Str;
use App\Http\Resources\CartResource;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class CartController extends Controller
{
    public function addToCart(Request $request, Product $product)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
      
        ]);

        $userId = Auth::id();
        $sessionId = $userId ? null : $request->header('X-Session-ID');

        if (!$userId && !$sessionId) {
            return response()->json(['message' => 'Session ID is required for guest users'], 400);
        }

        // دمج السلة إذا كان مستخدم مسجل ولديه sessionId
        if ($userId && $sessionId) {
            $this->mergeGuestCart($userId, $sessionId);
        }

        // مفتاح البحث
        $key = [
            'product_id' => $product->id,
            $userId ? 'user_id' : 'session_id' => $userId ?: $sessionId
        ];

        $cartItem = Cart::where($key)->first();

        if ($cartItem) {
            $cartItem->increment('quantity', $request->quantity);

            if ($cartItem->quantity > 10) {
                $cartItem->quantity = 10;
                $cartItem->save();

                return response()->json([
                    'success' => false,
                    'message' => 'تم الوصول إلى الحد الأقصى للكمية (10)'
                ], 422);
            }
        } else {
            $cartItem = Cart::create([
                'product_id' => $product->id,
                'quantity' => min($request->quantity, 10),
                'user_id' => $userId,
                'session_id' => $userId ? null : $sessionId
            ]);
        }

        $cartCount = Auth::check()
            ? Cart::where('user_id', Auth::id())->count()
            : Cart::where('session_id', $sessionId)->count();


        return response()->json([
            'success' => true,
            'cart_count' => $cartCount,
            'data' => $cartItem->fresh()->load('product')
        ]);
    }

    protected function mergeGuestCart($userId, $sessionId)
    {
        Cart::where('session_id', $sessionId)
            ->whereNull('user_id')
            ->each(function ($guestItem) use ($userId) {
                $existingItem = Cart::where('user_id', $userId)
                    ->where('product_id', $guestItem->product_id)
                    ->first();

                if ($existingItem) {
                    $existingItem->increment('quantity', $guestItem->quantity);
                    $guestItem->delete();
                } else {
                    $guestItem->update(['user_id' => $userId, 'session_id' => null]);
                }
            });
    }

    public function getUserCart(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $cartItems = Cart::with(['product.images'])
            ->where('user_id', Auth::id())
            ->get();

        return response()->json([
            'success' => true,
            'items' => CartResource::collection($cartItems),
            'cart_count' => $cartItems->count(),
            'total' => $cartItems->sum(function ($item) {
                return $item->product->price * $item->quantity;
            })
        ]);
    }
public function getCart(Request $request)
{
    $sessionId = $request->header('X-Session-ID');

    if (Auth::check()) {
        $cartItems = Cart::with(['product.images'])
            ->where('user_id', Auth::id())
            ->get();
        $cartCount = $cartItems->sum('quantity');
    } else {
        if (!$sessionId) {
            return response()->json(['message' => 'Session ID is required for guest users'], 400);
        }

        $cartItems = Cart::with(['product.images'])
            ->where('session_id', $sessionId)
            ->get();
        $cartCount = $cartItems->sum('quantity');
    }

    return response()->json([
        'success' => true,
        'items' => CartResource::collection($cartItems),
        'cart_count' => $cartCount,
        'total' => $cartItems->sum(function ($item) {
            return $item->product->price * $item->quantity;
        })
    ]);
}


    public function getSessionId(Request $request)
    {
        // تأكد أن السيشن بدأ
        if (!Session::has('cart_session_id')) {
            $sessionId = Str::uuid()->toString(); // أو يمكنك استخدام session()->getId()
            Session::put('cart_session_id', $sessionId);
            Log::info("🔧 New session_id generated: $sessionId");
        } else {
            $sessionId = Session::get('cart_session_id');
            Log::info("🔁 Existing session_id returned: $sessionId");
        }

        return response()->json([
            'session_id' => $sessionId,
        ]);
    }

    // تحديث كمية المنتج في السلة
    public function updateCartItem(Request $request, Cart $cartItem)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1'
        ]);

        $cartItem->update(['quantity' => $request->quantity]);
        $cartCount = Auth::check()
            ? Cart::where('user_id', Auth::id())->count()
            : Cart::where('session_id', $cartItem->session_id)->count();

        return response()->json([
            'success' => true,
            'cart_count' => $cartCount,
            'total' => Cart::totalPrice()
        ]);
    }

    // حذف منتج من السلة
    public function removeCartItem(Cart $cartItem)
    {
        $userId = $cartItem->user_id;
        $sessionId = $cartItem->session_id;

        $cartItem->delete();

        $cartCount = $userId
            ? Cart::where('user_id', $userId)->count()
            : Cart::where('session_id', $sessionId)->count();
        return response()->json([
            'success' => true,
            'cart_count' => Cart::totalItems(),
            'total' => Cart::totalPrice()
        ]);
    }
    public function clearCart(Request $request)
    {
        $userId = Auth::id();
        $sessionId = $request->header('X-Session-ID');

        if ($userId) {
            Cart::where('user_id', $userId)->delete();
        } elseif ($sessionId) {
            Cart::where('session_id', $sessionId)->delete();
        } else {
            return response()->json(['message' => 'Session ID is required for guest users'], 400);
        }

        return response()->json([
            'success' => true,
            'message' => 'تم تفريغ السلة بنجاح',
            'cart_count' => 0,
            'total' => 0
        ]);
    }
}
