<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Coupon;
use App\Models\CouponUsage;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class PaymentController extends Controller
{
    /**
     * Process payment
     */
    public function process(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'nullable|exists:orders,id',
            'booking_id' => 'nullable|exists:bookings,id',
            'payment_method' => 'required|in:credit_card,e_wallet,cash_on_delivery',
            'amount' => 'required|numeric|min:0',
            'card_details' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        // Process payment based on method
        $transactionId = 'TXN-' . strtoupper(Str::random(12));
        
        $payment = Payment::create([
            'user_id' => $request->user()->id,
            'order_id' => $request->order_id,
            'booking_id' => $request->booking_id,
            'payment_method' => $request->payment_method,
            'amount' => $request->amount,
            'total_amount' => $request->amount,
            'status' => $request->payment_method === 'cash_on_delivery' ? 'pending' : 'paid',
            'transaction_id' => $transactionId,
        ]);

        // If e_wallet, deduct from user wallet
        if ($request->payment_method === 'e_wallet') {
            $user = $request->user();
            if ($user->wallet_balance < $request->amount) {
                return response()->json([
                    'success' => false,
                    'message' => 'Insufficient wallet balance'
                ], 400);
            }
            $user->decrement('wallet_balance', $request->amount);
        }

        return response()->json([
            'success' => true,
            'message' => 'Payment processed successfully',
            'data' => $payment
        ], 201);
    }

    /**
     * Get payment methods
     */
    public function methods(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                [
                    'id' => 'credit_card',
                    'name' => 'Credit Card',
                    'icon' => 'credit-card',
                ],
                [
                    'id' => 'e_wallet',
                    'name' => 'E-Wallet',
                    'icon' => 'wallet',
                ],
                [
                    'id' => 'cash_on_delivery',
                    'name' => 'Cash on Delivery',
                    'icon' => 'cash',
                ],
            ]
        ]);
    }

    /**
     * Apply discount/coupon
     */
    public function applyDiscount(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'coupon_code' => 'required|string',
            'order_amount' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $coupon = Coupon::where('code', $request->coupon_code)
            ->where('is_active', true)
            ->where('valid_from', '<=', now())
            ->where('valid_until', '>=', now())
            ->first();

        if (!$coupon) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired coupon'
            ], 404);
        }

        if ($request->order_amount < $coupon->minimum_order_amount) {
            return response()->json([
                'success' => false,
                'message' => 'Minimum order amount not met'
            ], 400);
        }

        // Calculate discount
        $discount = 0;
        if ($coupon->discount_type === 'percentage') {
            $discount = ($request->order_amount * $coupon->discount_value) / 100;
            if ($coupon->max_discount_amount) {
                $discount = min($discount, $coupon->max_discount_amount);
            }
        } else {
            $discount = min($coupon->discount_value, $request->order_amount);
        }

        return response()->json([
            'success' => true,
            'message' => 'Discount applied successfully',
            'data' => [
                'coupon' => $coupon,
                'discount_amount' => $discount,
                'final_amount' => $request->order_amount - $discount,
            ]
        ]);
    }

    /**
     * Get payment history
     */
    public function history(Request $request): JsonResponse
    {
        $payments = $request->user()->payments()
            ->with(['order', 'booking'])
            ->latest()
            ->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $payments
        ]);
    }
}
