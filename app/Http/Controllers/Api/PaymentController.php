<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Coupon;
use App\Models\CouponUsage;
use App\Models\UserWalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
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
            'user_id'        => $request->user()->id,
            'order_id'       => $request->order_id,
            'booking_id'     => $request->booking_id,
            'payment_method' => $request->payment_method,
            'amount'         => $request->amount,
            'total_amount'   => $request->amount,
            'status'         => $request->payment_method === 'cash_on_delivery' ? 'pending' : 'paid',
            'transaction_id' => $transactionId,
        ]);

        // If e_wallet, deduct from user wallet inside a transaction with pessimistic lock
        if ($request->payment_method === 'e_wallet') {
            try {
                DB::transaction(function () use ($request, $payment) {
                    $user = \App\Models\User::lockForUpdate()->find($request->user()->id);
                    if ($user->wallet_balance < $request->amount) {
                        $payment->delete();
                        throw new \Exception('Insufficient wallet balance');
                    }
                    $balanceBefore = $user->wallet_balance;
                    $user->decrement('wallet_balance', $request->amount);
                    UserWalletTransaction::create([
                        'user_id'        => $user->id,
                        'type'           => 'debit',
                        'amount'         => $request->amount,
                        'balance_before' => $balanceBefore,
                        'balance_after'  => $user->fresh()->wallet_balance,
                        'description'    => 'Payment for order/booking',
                        'reference_type' => Payment::class,
                        'reference_id'   => $payment->id,
                    ]);
                });
            } catch (\Exception $e) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
            }
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

        try {
            $payload = DB::transaction(function () use ($request) {
                $coupon = Coupon::where('code', trim((string) $request->coupon_code))
                    ->lockForUpdate()
                    ->first();

                if (!$coupon || !$coupon->is_active) {
                    return ['error' => 404, 'body' => ['success' => false, 'message' => 'Invalid or expired coupon']];
                }

                $validFrom = $coupon->valid_from;
                $validUntil = $coupon->valid_until;
                if ($validFrom && now()->lt($validFrom)) {
                    return ['error' => 400, 'body' => ['success' => false, 'message' => 'Coupon is not valid yet']];
                }
                if ($validUntil && now()->gt($validUntil)) {
                    return ['error' => 400, 'body' => ['success' => false, 'message' => 'Coupon has expired']];
                }

                $minOrder = (float) ($coupon->minimum_order_amount ?? 0);
                if ((float) $request->order_amount < $minOrder) {
                    return ['error' => 400, 'body' => ['success' => false, 'message' => 'Minimum order amount not met']];
                }

                $userId = $request->user()->id;
                $perUserLimit = (int) ($coupon->usage_limit_per_user ?? 1);
                $userUsageCount = CouponUsage::where('coupon_id', $coupon->id)->where('user_id', $userId)->count();
                if ($userUsageCount >= $perUserLimit) {
                    return ['error' => 400, 'body' => ['success' => false, 'message' => 'Coupon usage limit reached']];
                }

                if ($coupon->usage_limit !== null) {
                    $totalUsages = CouponUsage::where('coupon_id', $coupon->id)->count();
                    if ($totalUsages >= (int) $coupon->usage_limit) {
                        return ['error' => 400, 'body' => ['success' => false, 'message' => 'Coupon is no longer available']];
                    }
                }

                $orderAmount = (float) $request->order_amount;
                $discount = 0.0;
                if ($coupon->discount_type === 'percentage') {
                    $discount = ($orderAmount * (float) $coupon->discount_value) / 100;
                    if ($coupon->max_discount_amount) {
                        $discount = min($discount, (float) $coupon->max_discount_amount);
                    }
                    $discount = min($discount, $orderAmount);
                } else {
                    $discount = min((float) $coupon->discount_value, $orderAmount);
                }

                return [
                    'ok' => true,
                    'coupon' => $coupon,
                    'discount_amount' => round($discount, 2),
                    'final_amount' => round($orderAmount - $discount, 2),
                ];
            });
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => 'Could not apply coupon'], 500);
        }

        if (isset($payload['error'])) {
            return response()->json($payload['body'], $payload['error']);
        }

        return response()->json([
            'success' => true,
            'message' => 'Discount applied successfully',
            'data' => [
                'coupon' => $payload['coupon'],
                'discount_amount' => $payload['discount_amount'],
                'final_amount' => $payload['final_amount'],
            ],
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
