<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use App\Http\Requests\CategoryRequest;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class CategoryController extends Controller
{
    // Display a listing of categories
    public function index()
    {
        $query = Category::with(['parent', 'children'])
            ->withCount('products')
            ->orderBy('created_at', 'desc');

        if (request()->has('parent_only') && request('parent_only')) {
            $query->whereNull('parent_id');
        }

        $categories = $query->paginate(request('per_page', 8));

        return CategoryResource::collection($categories);
    }
    public function getParents()
    {
        $parents = Category::whereNull('parent_id')->get();
        return response()->json($parents);
    }

    // Store a newly created category
    public function store(CategoryRequest $request)
    {
        $data = $request->validated();

        // استخدم الـ slug الذي يدخله المستخدم مباشرة
        $data['slug'] = $request->slug;

        $data['parent_id'] = $data['parent_id'] ?? null;

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('categories', 'public');
        }

        $category = Category::create($data);

        return new CategoryResource($category);
    }


    // Display the specified category
    public function show($id)
    {
        $category = Category::find($id);

        if (!$category) {
            return response()->json(['message' => 'Category not found'], Response::HTTP_NOT_FOUND);
        }

        return new CategoryResource($category);
    }

    // Update the specified category
    public function update(CategoryRequest $request, $id)
    {
        $category = Category::find($id);

        if (!$category) {
            return response()->json(['message' => 'Category not found'], Response::HTTP_NOT_FOUND);
        }

        $data = $request->validated();

        $data['slug'] = $request->slug;

        $data['parent_id'] = $data['parent_id'] ?? null;

        // Handle image removal if remove_image is true
        if ($request->boolean('remove_image')) {
            if ($category->image) {
                Storage::disk('public')->delete($category->image);
                $data['image'] = null;
            }
        }
        // Handle new image upload
        elseif ($request->hasFile('image')) {
            if ($category->image) {
                Storage::disk('public')->delete($category->image);
            }
            $data['image'] = $request->file('image')->store('categories', 'public');
        }

        $category->update($data);

        return new CategoryResource($category);
    }

    // Remove the specified category
    public function destroy($id)
    {
        $category = Category::find($id);

        if (!$category) {
            return response()->json(['message' => 'Category not found'], Response::HTTP_NOT_FOUND);
        }

        if ($category->image) {
            Storage::disk('public')->delete($category->image);
        }

        $category->delete();

        return response()->json(['message' => 'Category deleted successfully']);
    }
}
