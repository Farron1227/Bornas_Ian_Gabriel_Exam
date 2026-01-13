<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class ProductController extends Controller
{
    use ApiResponse;

    /**
     * Get all products with search and sorting
     */
    public function index(Request $request)
    {
        try {
            $query = Product::with('category');

            // Search
            if ($request->has('search') && $request->search) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
            }

            // Sort by price
            if ($request->has('sort')) {
                $sort = $request->sort;
                if ($sort === 'price_asc') {
                    $query->orderBy('price', 'desc');
                } elseif ($sort === 'price_desc') {
                    $query->orderBy('price', 'asc');
                } else {
                    $query->orderBy('created_at', 'desc');
                }
            } else {
                $query->orderBy('created_at', 'desc');
            }

            $perPage = $request->get('per_page', 8);
            $products = $query->paginate($perPage);

            return response()->json($products);
        } catch (\Exception $e) {
            Log::error('Error fetching products: ' . $e->getMessage());
            return $this->errorResponse('Failed to fetch products', 500);
        }
    }

    /**
     * Get single product
     */
    public function show($id)
    {
        try {
            $product = Product::with('category')->findOrFail($id);
            return $this->successResponse($product, 'Product retrieved successfully');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->errorResponse('Product not found', 404);
        } catch (\Exception $e) {
            Log::error('Error fetching product: ' . $e->getMessage());
            return $this->errorResponse('Failed to fetch product', 500);
        }
    }

    /**
     * Create new product
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'price' => 'required|numeric|min:0',
                'stock' => 'required|integer|min:0',
                'category_id' => 'required|exists:categories,id',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);

            if ($request->hasFile('image')) {
                $validated['image'] = $request->file('image')->store('products', 'public');
            }

            $product = Product::create($validated);
            $product->load('category');

            return $this->successResponse($product, 'Product created successfully', 201);
        } catch (ValidationException $e) {
            return $this->errorResponse('Validation failed', 422, $e->errors());
        } catch (\Exception $e) {
            Log::error('Error creating product: ' . $e->getMessage());
            return $this->errorResponse('Failed to create product', 500);
        }
    }

    /**
     * Update product
     */
    public function update(Request $request, $id)
    {
        try {
            $product = Product::findOrFail($id);

            $validated = $request->validate([
                'name' => 'sometimes|required|string|max:255',
                'description' => 'nullable|string',
                'price' => 'sometimes|required|numeric|min:0',
                'stock' => 'sometimes|required|integer|min:0',
                'category_id' => 'sometimes|required|exists:categories,id',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);

            if ($request->hasFile('image')) {
                // Delete old image if exists
                if ($product->image && Storage::disk('public')->exists($product->image)) {
                    Storage::disk('public')->delete($product->image);
                }
                $validated['image'] = $request->file('image')->store('products', 'public');
            }

            $product->update($validated);
            $product->load('category');

            return $this->successResponse($product, 'Product updated successfully');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->errorResponse('Product not found', 404);
        } catch (ValidationException $e) {
            return $this->errorResponse('Validation failed', 422, $e->errors());
        } catch (\Exception $e) {
            Log::error('Error updating product: ' . $e->getMessage());
            return $this->errorResponse('Failed to update product', 500);
        }
    }

    /**
     * Delete product
     */
    public function destroy($id)
    {
        try {
            $product = Product::findOrFail($id);
            
            // Delete image if exists
            if ($product->image && Storage::disk('public')->exists($product->image)) {
                Storage::disk('public')->delete($product->image);
            }

            $product->delete();

            return $this->successResponse(null, 'Product deleted successfully');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->errorResponse('Product not found', 404);
        } catch (\Exception $e) {
            Log::error('Error deleting product: ' . $e->getMessage());
            return $this->errorResponse('Failed to delete product', 500);
        }
    }
}
