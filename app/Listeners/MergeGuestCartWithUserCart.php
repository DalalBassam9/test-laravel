<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use App\Models\Cart;

class MergeGuestCartWithUserCart
{
    public function handle(Login $event)
    {
        $sessionId = Session::getId();
        $userId = $event->user->id;
        Cart::where('session_id', $sessionId)
            ->whereNull('user_id')
            ->update([
                'user_id' => $userId,
                'session_id' => null
            ]);
            
        // دمج العناصر المكررة
        $duplicates = Cart::select('product_id')
            ->where('user_id', $userId)
            ->groupBy('product_id')
            ->havingRaw('COUNT(*) > 1')
            ->get();
            
        foreach ($duplicates as $duplicate) {
            $items = Cart::where('user_id', $userId)
                ->where('product_id', $duplicate->product_id)
                ->orderBy('created_at')
                ->get();
                
            $first = $items->first();
            $items->shift()->each(function($item) use ($first) {
                $first->quantity += $item->quantity;
                $item->delete();
            });
            
            $first->save();
        }
    }
}