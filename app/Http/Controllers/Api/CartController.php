<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class CartController extends Controller
{
    /**
     * Get user cart
     */
    public function index(Request $request): JsonResponse
    {
        $carts = $request->user()->carts()->with('product')->get();
        
        $total = $carts->sum(function ($cart) {
            $product = $cart->product;
            $price = $product->price - ($product->price * $product->discount_percentage / 100);
            return $price * $cart->quantity;
        });

        return response()->json([
            'success' => true,
            'data' => [
                'items' => $carts,
                'total' => $total,
                'items_count' => $carts->count()
            ]
        ]);
    }

    /**
     * Add product to cart
     */
    public function add(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $product = Product::findOrFail($request->product_id);

        if (!$product->is_active || $product->stock_quantity < $request->quantity) {
            return response()->json([
                'success' => false,
                'message' => 'Product not available or insufficient stock'
            ], 400);
        }

        $cart = Cart::updateOrCreate(
            [
                'user_id' => $request->user()->id,
                'product_id' => $request->product_id,
            ],
            [
                'quantity' => DB::raw('quantity + ' . $request->quantity)
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Product added to cart',
            'data' => $cart->load('product')
        ], 201);
    }

    /**
     * Update cart item
     */
    public function update(Request $request, Cart $cart): JsonResponse
    {
        if ($cart->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'quantity' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        if ($cart->product->stock_quantity < $request->quantity) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient stock'
            ], 400);
        }

        $cart->update(['quantity' => $request->quantity]);

        return response()->json([
            'success' => true,
            'message' => 'Cart updated successfully',
            'data' => $cart->load('product')
        ]);
    }

    /**
     * Remove cart item
     */
    public function destroy(Cart $cart): JsonResponse
    {
        $cart->delete();

        return response()->json([
            'success' => true,
            'message' => 'Item removed from cart'
        ]);
    }

    /**
     * Clear cart
     */
    public function clear(Request $request): JsonResponse
    {
        $request->user()->carts()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Cart cleared successfully'
        ]);
    }
}
