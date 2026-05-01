<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Cart extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'user_id',
        'product_id',
        'quantity',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
    public function scopeCurrentCart($query)
    {
        $userId = Auth::id();
        $sessionId = session()->getId();
    
        return $query->with(['product' => function ($query) {
            $query->select('id', 'name', 'price', 'image');
        }])->where(function ($query) use ($userId, $sessionId) {
            if ($userId) {
                $query->where('user_id', $userId);
            } else {
                $query->where('session_id', $sessionId);
            }
        });
    }
    
    public static function totalItems()
    {
        return self::currentCart()->sum('quantity');
    }

    public static function totalPrice()
    {
        $items = self::currentCart()->get();
        return $items->sum(function ($item) {
            return $item->quantity * $item->product->price;
        });
    }
}
