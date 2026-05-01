<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Http\Resources\Lookups\CategoryResource;
use App\Http\Resources\Lookups\CityResource;
use App\Models\City;
use Illuminate\Http\Request;

class LookupsController extends Controller
{
    public function getCategories()
    {
        return CategoryResource::collection(Category::all());
    }

    public function getCities()
    {
      
        return CityResource::collection(City::all()); 
    }
}
