<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\Product;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class CartController extends Controller
{
    use ApiResponse;

    /**
     * Get user's cart items
     */
    public function index(Request $request)
    {
        try {
            $cartItems = CartItem::with('product.category')
                ->where('user_id', $request->user()->id)
                ->get();

            $total = $cartItems->sum(function ($item) {
                return $item->product->price * $item->quantity;
            });

            return $this->successResponse([
                'items' => $cartItems,
                'total' => round($total, 2),
            ], 'Cart retrieved successfully');
        } catch (\Exception $e) {
            Log::error('Error fetching cart: ' . $e->getMessage());
            return $this->errorResponse('Failed to fetch cart', 500);
        }
    }

    /**
     * Add item to cart
     */
    public function add(Request $request)
    {
        try {
            $validated = $request->validate([
                'product_id' => 'required|exists:products,id',
                'quantity' => 'required|integer|min:1',
            ]);

            $product = Product::findOrFail($validated['product_id']);

            // Check if product has sufficient stock
            if ($product->stock < $validated['quantity']) {
                return $this->errorResponse('Insufficient stock available', 400);
            }

            $cartItem = CartItem::where('user_id', $request->user()->id)
                ->where('product_id', $validated['product_id'])
                ->first();

            if ($cartItem) {
                // Update existing cart item
                $newQuantity = $cartItem->quantity + $validated['quantity'];
                
                // Check stock for new quantity
                if ($product->stock < $newQuantity) {
                    return $this->errorResponse('Insufficient stock for requested quantity', 400);
                }
                
                $cartItem->quantity = $newQuantity;
                $cartItem->save();
            } else {
                // Create new cart item
                $cartItem = CartItem::create([
                    'user_id' => $request->user()->id,
                    'product_id' => $validated['product_id'],
                    'quantity' => $validated['quantity'],
                ]);
            }

            $cartItem->load('product.category');

            return $this->successResponse($cartItem, 'Item added to cart', 201);
        } catch (ValidationException $e) {
            return $this->errorResponse('Validation failed', 422, $e->errors());
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->errorResponse('Product not found', 404);
        } catch (\Exception $e) {
            Log::error('Error adding to cart: ' . $e->getMessage());
            return $this->errorResponse('Failed to add item to cart', 500);
        }
    }

    /**
     * Update cart item quantity
     */
    public function update(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'quantity' => 'required|integer|min:1',
            ]);

            $cartItem = CartItem::where('user_id', $request->user()->id)
                ->where('id', $id)
                ->firstOrFail();

            // Check stock availability
            if ($cartItem->product->stock < $validated['quantity']) {
                return $this->errorResponse('Insufficient stock available', 400);
            }

            $cartItem->update($validated);
            $cartItem->load('product.category');

            return $this->successResponse($cartItem, 'Cart item updated successfully');
        } catch (ValidationException $e) {
            return $this->errorResponse('Validation failed', 422, $e->errors());
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->errorResponse('Cart item not found', 404);
        } catch (\Exception $e) {
            Log::error('Error updating cart item: ' . $e->getMessage());
            return $this->errorResponse('Failed to update cart item', 500);
        }
    }

    /**
     * Remove item from cart
     */
    public function remove(Request $request, $id)
    {
        try {
            $cartItem = CartItem::where('user_id', $request->user()->id)
                ->where('id', $id)
                ->firstOrFail();

            $cartItem->delete();

            return $this->successResponse(null, 'Item removed from cart');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->errorResponse('Cart item not found', 404);
        } catch (\Exception $e) {
            Log::error('Error removing cart item: ' . $e->getMessage());
            return $this->errorResponse('Failed to remove cart item', 500);
        }
    }

    /**
     * Clear all cart items for user
     */
    public function clear(Request $request)
    {
        try {
            $deletedCount = CartItem::where('user_id', $request->user()->id)->delete();

            return $this->successResponse(
                ['deleted_count' => $deletedCount],
                'Cart cleared successfully'
            );
        } catch (\Exception $e) {
            Log::error('Error clearing cart: ' . $e->getMessage());
            return $this->errorResponse('Failed to clear cart', 500);
        }
    }
}
