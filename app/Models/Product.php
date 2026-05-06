<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;


class Product extends Model
{
    protected $fillable = ['name', 'slug', 'description',
    'max_quantity', 'price', 'quantity', 'category_id', 'is_featured'];
    
   
    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class);
    }
    public function mainImage()
    {
        return $this->hasOne(ProductImage::class)->where('is_main', true);
    }

   public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
       

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }




}
