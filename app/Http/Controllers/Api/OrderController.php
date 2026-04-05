<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Cart;
use App\Models\Address;
use App\Models\UserWalletTransaction;
use App\Services\FinancialService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    protected FinancialService $financialService;

    public function __construct(FinancialService $financialService)
    {
        $this->financialService = $financialService;
    }

    /**
     * Display a listing of orders
     */
    public function index(Request $request): JsonResponse
    {
        $query = $request->user()->orders()->with(['shop', 'items.product', 'deliveryAddress']);

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $orders = $query->latest()->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $orders
        ]);
    }

    /**
     * Store a newly created order
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'shop_id' => 'required|exists:shops,id',
            'delivery_address_id' => 'required|exists:addresses,id',
            'payment_method' => 'required|in:credit_card,e_wallet,cash_on_delivery',
            'coupon_code' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        // Verify address belongs to user
        $address = Address::where('id', $request->delivery_address_id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $carts = $request->user()->carts()->with('product')->get();

        if ($carts->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Cart is empty'
            ], 400);
        }

        DB::beginTransaction();
        try {
            // Calculate totals
            $subtotal = $carts->sum(function ($cart) {
                $product = $cart->product;
                $price = $product->price - ($product->price * $product->discount_percentage / 100);
                return $price * $cart->quantity;
            });

            $discountAmount = 0;
            // TODO: Apply coupon if provided

            $deliveryFee = 50; // Default delivery fee
            $totalAmount = $subtotal - $discountAmount + $deliveryFee;

            // Create order
            $order = Order::create([
                'order_number'               => 'ORD-' . str_pad(Order::count() + 1, 6, '0', STR_PAD_LEFT),
                'user_id'                    => $request->user()->id,
                'shop_id'                    => $request->shop_id,
                'status'                     => 'received',
                'total_amount'               => $totalAmount,
                'discount_amount'            => $discountAmount,
                'delivery_fee'               => $deliveryFee,
                'delivery_address_id'        => $request->delivery_address_id,
                'delivery_address_snapshot'  => [
                    'street'    => $address->street ?? $address->address_line ?? null,
                    'city'      => $address->city ?? null,
                    'state'     => $address->state ?? null,
                    'country'   => $address->country ?? null,
                    'latitude'  => $address->latitude ?? null,
                    'longitude' => $address->longitude ?? null,
                ],
                'payment_method'             => $request->payment_method,
                'payment_status'             => $request->payment_method === 'cash_on_delivery' ? 'pending' : 'paid',
            ]);

            // Create order items
            foreach ($carts as $cart) {
                $product = $cart->product;
                $price = $product->price - ($product->price * $product->discount_percentage / 100);
                
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'quantity' => $cart->quantity,
                    'unit_price' => $price,
                    'total_price' => $price * $cart->quantity,
                ]);

                // Update product stock
                $product->decrement('stock_quantity', $cart->quantity);
            }

            // Clear cart
            $request->user()->carts()->delete();

            // Process payment if not cash on delivery
            if ($request->payment_method !== 'cash_on_delivery') {
                $this->financialService->processOrderPayment($order);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Order created successfully',
                'data' => $order->load(['shop', 'items.product', 'deliveryAddress'])
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create order: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified order
     */
    public function show(Request $request, Order $order): JsonResponse
    {
        if ($order->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $order->load(['shop', 'items.product', 'deliveryAddress', 'statusHistory']);

        return response()->json([
            'success' => true,
            'data' => $order
        ]);
    }

    /**
     * Track order
     */
    public function track(Request $request, Order $order): JsonResponse
    {
        if ($order->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $order->load(['statusHistory', 'delivery']);

        return response()->json([
            'success' => true,
            'data' => [
                'order_number' => $order->order_number,
                'status' => $order->status,
                'status_history' => $order->statusHistory,
                'delivery' => $order->delivery,
            ]
        ]);
    }

    /**
     * Cancel order
     */
    public function cancel(Request $request, Order $order): JsonResponse
    {
        if ($order->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        if (in_array($order->status, ['delivered', 'cancelled'])) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot cancel this order'
            ], 400);
        }

        $order->update(['status' => 'cancelled']);

        // Refund if paid
        if ($order->payment_status === 'paid') {
            DB::transaction(function () use ($request, $order) {
                $user = \App\Models\User::lockForUpdate()->find($request->user()->id);
                $balanceBefore = $user->wallet_balance;
                $user->increment('wallet_balance', $order->total_amount);
                UserWalletTransaction::create([
                    'user_id'        => $user->id,
                    'type'           => 'credit',
                    'amount'         => $order->total_amount,
                    'balance_before' => $balanceBefore,
                    'balance_after'  => $user->fresh()->wallet_balance,
                    'description'    => 'Refund for cancelled order #' . $order->order_number,
                    'reference_type' => Order::class,
                    'reference_id'   => $order->id,
                ]);
            });
        }

        return response()->json([
            'success' => true,
            'message' => 'Order cancelled successfully',
            'data' => $order
        ]);
    }
}
