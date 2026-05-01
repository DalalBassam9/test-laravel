<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\OrderResource;
use App\Models\Order;

class OrderController extends Controller
{
    public function getOrders()
    {
        $orders = Order::with(['items.product.images', 'user', 'address.city'])->orderBy('created_at', 'desc')->paginate(10);
        return OrderResource::collection($orders);
    }

    public function updateStatusOrder($id, Request $request)
    {
        $order = Order::findOrFail($id);
        $order->update([
            'status' => $request->status,
        ]);
        return new OrderResource($order);
    }
    public function show($id)
    {
        $order = Order::with([
            'items.product.images',
            'user',
            'address.city',
            'address.user'
        ])->findOrFail($id);

        return new OrderResource($order);
    }
}
