<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index()
    {
        try {
            // Get products
            $products = Product::when(request()->q, function($products) {
                $products = $products->where('title', 'like', '%' . request()->q . '%');
            })->latest()->paginate(5);

            // Return JSON response
            return response()->json([
                'success' => true,
                'data' => $products,
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
            // Get categories
            $categories = Category::all();

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

    public function store(Request $request)
    {
        try {
            // Validate request
            $request->validate([
                'image' => 'required|image|mimes:jpeg,jpg,png|max:2000',
                'barcode' => 'required|unique:products',
                'title' => 'required',
                'description' => 'required',
                'category_id' => 'required',
                'buy_price' => 'required',
                'sell_price' => 'required',
                'stock' => 'required',
            ]);

            // Upload image
            $image = $request->file('image');
            $image->storeAs('public/products', $image->hashName());

            // Create product
            $product = Product::create([
                'image' => $image->hashName(),
                'barcode' => $request->barcode,
                'title' => $request->title,
                'slug' =>  Str::slug($request->title, '-'),
                'description' => $request->description,
                'category_id' => $request->category_id,
                'buy_price' => $request->buy_price,
                'sell_price' => $request->sell_price,
                'discount_price' => $request->discount_price ?? 0,
                'stock' => $request->stock,
            ]);

            // Return JSON response
            return response()->json([
                'success' => true,
                'data' => $product,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function edit(Product $product)
    {
        try {
            // Get categories
            $categories = Category::all();

            // Return JSON response
            return response()->json([
                'success' => true,
                'data' => [
                    'product' => $product,
                    'categories' => $categories,
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, Product $product)
    {
        try {
            // Validate request
            $request->validate([
                'barcode' => 'required|unique:products,barcode,' . $product->id,
                'title' => 'required',
                'description' => 'required',
                'category_id' => 'required',
                'buy_price' => 'required',
                'sell_price' => 'required',
                'stock' => 'required',
            ]);

            // Check if image is being updated
            if ($request->file('image')) {
                // Remove old image
                Storage::disk('local')->delete('public/products/' . basename($product->image));

                // Upload new image
                $image = $request->file('image');
                $image->storeAs('public/products', $image->hashName());

                // Update product with new image
                $product->update([
                    'image' => $image->hashName(),
                    'barcode' => $request->barcode,
                    'title' => $request->title,
                    'slug' =>  Str::slug($request->title, '-'),
                    'description' => $request->description,
                    'category_id' => $request->category_id,
                    'buy_price' => $request->buy_price,
                    'sell_price' => $request->sell_price,
                    'discount_price' => $request->discount_price ?? 0,
                    'stock' => $request->stock,
                ]);
            } else {
                // Update product without image
                $product->update([
                    'barcode' => $request->barcode,
                    'title' => $request->title,
                    'slug' =>  Str::slug($request->title, '-'),
                    'description' => $request->description,
                    'category_id' => $request->category_id,
                    'buy_price' => $request->buy_price,
                    'sell_price' => $request->sell_price,
                    'stock' => $request->stock,
                    'discount_price' => $request->discount_price ?? 0,
                ]);
            }

            // Return JSON response
            return response()->json([
                'success' => true,
                'data' => $product,
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
            $product = Product::findOrFail($id);

            // Remove image
            Storage::disk('local')->delete('public/products/' . basename($product->image));

            // Delete product
            $product->delete();

            // Return JSON response
            return response()->json([
                'success' => true,
                'message' => 'Product deleted successfully.'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }






}
