<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\City;
use App\Http\Requests\StoreCityRequest;
use App\Http\Resources\CityResource;

class CityController extends Controller
{
    public function index()
    {
        return CityResource::collection(City::all());
    }

    public function store(StoreCityRequest  $request)
    {
       
        $city = City::create($request->validated());
        return new CityResource($city);
    }

    public function show($id)
    {
        $city = City::findOrFail($id);
        return new CityResource($city);
    }

    public function update(Request $request, $id)
    {
        $city = City::findOrFail($id);
        $city->update($request->validated());
        return new CityResource($city);
    }

    public function destroy($id)
    {
        $city = City::findOrFail($id);
        $city->delete();

        return response()->json(null, 204);
    }
}
