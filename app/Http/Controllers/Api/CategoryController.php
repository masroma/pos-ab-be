<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Category;

class CategoryController extends Controller
{
    public function index()
    {
        try {
            // Get categories
            $categories = Category::when(request()->q, function($categories) {
                $categories = $categories->where('name', 'like', '%' . request()->q . '%');
            })->latest()->paginate(5);

            // Return JSON response
            return response()->json([
                'success' => true,
                'data' => $categories,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function create()
    {
        try {
            // No specific data to retrieve, just return success
            return response()->json([
                'success' => true,
                'message' => 'Form to create a category'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            // Validate request
            $request->validate([
                'image' => 'required|image|mimes:jpeg,jpg,png|max:2000',
                'name' => 'required|unique:categories',
                'description' => 'required'
            ]);

            // Upload image
            $image = $request->file('image');
            $image->storeAs('public/categories', $image->hashName());

            // Create category
            $category = Category::create([
                'image' => $image->hashName(),
                'name' => $request->name,
                'description' => $request->description
            ]);

            // Return JSON response
            return response()->json([
                'success' => true,
                'data' => $category,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function edit(Category $category)
    {
        try {
            // Return JSON response
            return response()->json([
                'success' => true,
                'data' => $category,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, Category $category)
    {
        try {
            // Validate request
            $request->validate([
                'name' => 'required|unique:categories,name,' . $category->id,
                'description' => 'required'
            ]);

            // Check if image is being updated
            if ($request->file('image')) {
                // Remove old image
                Storage::disk('local')->delete('public/categories/' . basename($category->image));

                // Upload new image
                $image = $request->file('image');
                $image->storeAs('public/categories', $image->hashName());

                // Update category with new image
                $category->update([
                    'image' => $image->hashName(),
                    'name' => $request->name,
                    'description' => $request->description
                ]);
            } else {
                // Update category without image
                $category->update([
                    'name' => $request->name,
                    'description' => $request->description
                ]);
            }

            // Return JSON response
            return response()->json([
                'success' => true,
                'data' => $category,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            // Find by ID
            $category = Category::findOrFail($id);

            // Remove image
            Storage::disk('local')->delete('public/categories/' . basename($category->image));

            // Delete category
            $category->delete();

            // Return JSON response
            return response()->json([
                'success' => true,
                'message' => 'Category deleted successfully.'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }






}
