<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Address;
use App\Http\Resources\OrderResource;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'address.name' => 'required|string|max:255',
            'address.email' => 'required|email|max:255',
            'address.phone' => 'required|string|max:20',
            'address.city' => 'required|string|max:100',
            'address.street' => 'required|string|max:255',
        ]);

        $cart = session('cart', []);

        if (empty($cart)) {
            return response()->json(['message' => 'السلة فارغة'], 400);
        }

        $userId = Auth::check() ? Auth::id() : null;

        $address = Address::create([
            'userId' => $userId,
            'name' => $request->address['name'],
            'email' => $request->address['email'],
            'phone' => $request->address['phone'],
            'city' => $request->address['city'],
            'street' => $request->address['street'],
            'notes' => $request->address['notes'] ?? null,
        ]);

        $order = Order::create([
            'userId' => $userId,
            'addressId' => $address->addressId,
            'totalPrice' => collect($cart)->sum(fn($item) => $item['price'] * $item['quantity']),
            'status' => 'pending',
        ]);

        foreach ($cart as $item) {
            OrderItem::create([
                'order_id' => $order->orderId,
                'product_id' => $item['id'],
                'quantity' => $item['quantity'],
                'price' => $item['price'],
                'card_message' => $item['card_message'] ?? null,

            ]);
        }

        session()->forget('cart');

        return response()->json(['message' => 'تم إرسال الطلب بنجاح']);
    }
    public function userAddresses()
    {
    }

    public function getUserOrders()
    {
        $user = Auth::id();
        $orders = Order::with(['items.product.images', 'address.city'])->where('user_id', $user)->orderBy('created_at', 'desc')->get();
        return OrderResource::collection($orders);
    }

    public function findByIdUserOrder($id)
    {
        $order = Order::with('address.city')->where('user_id', Auth::id())->with([
            'items.product.images' => function ($query) {
                $query->where('is_main', true);
            }
        ])->findOrFail($id);

        return new OrderResource($order);
    }
    public function cancelOrder($id)
    {
        $order = Order::where('user_id', Auth::id())->findOrFail($id);

        // Check if order can be cancelled
        if (!in_array($order->status, ['pending', 'placed'])) {
            return response()->json([
                'message' => 'لا يمكن إلغاء الطلب في حالته الحالية'
            ], 400);
        }

        $order->update(['status' => 'cancelled']);

        return response()->json([
            'message' => 'تم إلغاء الطلب بنجاح'
        ]);
    }

    public function getOrderCountsByStatus()
    {
        $counts = [
            'all' => Order::where('user_id', Auth::id())->count(),
            'pending' => Order::where('user_id', Auth::id())->where('status', 'pending')->count(),
            'placed' => Order::where('user_id', Auth::id())->where('status', 'placed')->count(),
            'confirmed' => Order::where('user_id', Auth::id())->where('status', 'confirmed')->count(),
            'shipped' => Order::where('user_id', Auth::id())->where('status', 'shipped')->count(),
            'forDelivery' => Order::where('user_id', Auth::id())->where('status', 'forDelivery')->count(),
            'delivered' => Order::where('user_id', Auth::id())->where('status', 'delivered')->count(),
            'completed' => Order::where('user_id', Auth::id())->where('status', 'completed')->count(),
            'cancelled' => Order::where('user_id', Auth::id())->where('status', 'cancelled')->count(),
        ];

        return response()->json($counts);
    }  

}
