<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = ['name', 'image', 'slug'];

 
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class);
    }

  
}
