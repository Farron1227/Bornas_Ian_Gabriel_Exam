<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\CartItem;
use App\Models\Product;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
    use ApiResponse;

    /**
     * Get user's orders
     */
    public function index(Request $request)
    {
        try {
            $orders = Order::with(['items.product.category'])
                ->where('user_id', $request->user()->id)
                ->orderBy('created_at', 'desc')
                ->get();

            return $this->successResponse($orders, 'Orders retrieved successfully');
        } catch (\Exception $e) {
            Log::error('Error fetching orders: ' . $e->getMessage());
            return $this->errorResponse('Failed to fetch orders', 500);
        }
    }

    /**
     * Get all orders (Admin only)
     */
    public function adminIndex(Request $request)
    {
        try {
            $orders = Order::with(['user', 'items.product.category'])
                ->orderBy('created_at', 'desc')
                ->get();

            return $this->successResponse($orders, 'Orders retrieved successfully');
        } catch (\Exception $e) {
            Log::error('Error fetching admin orders: ' . $e->getMessage());
            return $this->errorResponse('Failed to fetch orders', 500);
        }
    }

    /**
     * Get single order
     */
    public function show(Request $request, $id)
    {
        try {
            $order = Order::with(['items.product.category'])
                ->where('id', $id)
                ->where('user_id', $request->user()->id)
                ->firstOrFail();

            return $this->successResponse($order, 'Order retrieved successfully');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->errorResponse('Order not found', 404);
        } catch (\Exception $e) {
            Log::error('Error fetching order: ' . $e->getMessage());
            return $this->errorResponse('Failed to fetch order', 500);
        }
    }

    /**
     * Create order from cart
     */
    public function store(Request $request)
    {
        try {
            return DB::transaction(function () use ($request) {
                // Get cart items
                $cartItems = CartItem::with('product')
                    ->where('user_id', $request->user()->id)
                    ->get();

                if ($cartItems->isEmpty()) {
                    return $this->errorResponse('Cart is empty', 400);
                }

                // Calculate total and check stock
                $total = 0;
                foreach ($cartItems as $cartItem) {
                    if ($cartItem->product->stock < $cartItem->quantity) {
                        return $this->errorResponse(
                            "Insufficient stock for {$cartItem->product->name}",
                            400
                        );
                    }
                    $total += $cartItem->product->price * $cartItem->quantity;
                }

                // Create order
                $order = Order::create([
                    'user_id' => $request->user()->id,
                    'total' => round($total, 2),
                    'status' => 'pending',
                ]);

                // Create order items and update product stock
                foreach ($cartItems as $cartItem) {
                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $cartItem->product_id,
                        'quantity' => $cartItem->quantity,
                        'price' => $cartItem->product->price,
                    ]);

                    // Decrease product stock
                    $cartItem->product->decrement('stock', $cartItem->quantity);
                }

                // Clear cart
                CartItem::where('user_id', $request->user()->id)->delete();

                $order->load(['items.product.category']);

                return $this->successResponse($order, 'Order created successfully', 201);
            });
        } catch (\Exception $e) {
            Log::error('Error creating order: ' . $e->getMessage());
            return $this->errorResponse('Failed to create order', 500);
        }
    }

    /**
     * Update order status
     */
    public function update(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'status' => 'required|in:pending,for_delivery,delivered,canceled',
            ]);

            $order = Order::findOrFail($id);
            $order->update($validated);

            $order->load(['user', 'items.product.category']);

            return $this->successResponse($order, 'Order updated successfully');
        } catch (ValidationException $e) {
            return $this->errorResponse('Validation failed', 422, $e->errors());
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->errorResponse('Order not found', 404);
        } catch (\Exception $e) {
            Log::error('Error updating order: ' . $e->getMessage());
            return $this->errorResponse('Failed to update order', 500);
        }
    }

    /**
     * Delete order
     */
    public function destroy($id)
    {
        try {
            $order = Order::findOrFail($id);
            $order->delete();

            return $this->successResponse(null, 'Order deleted successfully');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->errorResponse('Order not found', 404);
        } catch (\Exception $e) {
            Log::error('Error deleting order: ' . $e->getMessage());
            return $this->errorResponse('Failed to delete order', 500);
        }
    }
}
