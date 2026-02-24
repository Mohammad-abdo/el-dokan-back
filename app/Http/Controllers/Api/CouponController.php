<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\CouponUsage;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class CouponController extends Controller
{
    /**
     * Validate coupon
     */
    public function validateCoupon(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|string',
            'order_amount' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $coupon = Coupon::where('code', $request->code)
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

        // Check minimum order amount
        if ($request->order_amount < $coupon->minimum_order_amount) {
            return response()->json([
                'success' => false,
                'message' => 'Minimum order amount not met'
            ], 400);
        }

        // Check usage limit
        $usageCount = CouponUsage::where('coupon_id', $coupon->id)
            ->where('user_id', $request->user()->id)
            ->count();

        if ($usageCount >= $coupon->usage_limit_per_user) {
            return response()->json([
                'success' => false,
                'message' => 'Coupon usage limit reached'
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
            'message' => 'Coupon is valid',
            'data' => [
                'coupon' => $coupon,
                'discount_amount' => $discount,
            ]
        ]);
    }
}
