<?php

namespace App\Models;

use App\Enums\RatingStatusEnum as RatingStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rating extends Model
{
    
    use HasFactory;

    protected $fillable = [
        'review',
        'rating',
        'user_id',
        'product_id',
        'status',
    ];

    protected $casts = [
        'rating' => 'integer',  
        'status' => RatingStatus::class,
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

  
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function scopePublished($query)
    {
        return $query->where('status', RatingStatus::PUBLISHED);
    }
    
    public function publish(): void
    {
        $this->status = RatingStatus::PUBLISHED;
        $this->save();
    }

    public function unpublish(): void
    {
        $this->status = RatingStatus::NOT_PUBLISHED;
        $this->save();
    }
   
}
