<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use App\Models\DoctorWallet;
use App\Models\DoctorWalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class AdminDoctorWalletController extends Controller
{
    /**
     * Show doctor wallet with summary stats
     */
    public function show(Doctor $doctor): JsonResponse
    {
        $wallet = DoctorWallet::firstOrCreate(
            ['doctor_id' => $doctor->id],
            ['balance' => 0, 'commission_rate' => 0.15]
        );

        $wallet->load('doctor');

        $transactions = DoctorWalletTransaction::where('doctor_id', $doctor->id)->get();

        $total_from_bookings = $transactions->where('type', 'booking_payment')->where('status', 'completed')->sum(fn ($t) => (float) $t->amount);
        $total_commission = $transactions->where('type', 'commission')->where('status', 'completed')->sum(fn ($t) => (float) $t->amount);
        $total_transfer = $transactions->where('type', 'transfer')->where('status', 'completed')->sum(fn ($t) => (float) $t->amount);
        $total_withdrawn = $transactions->where('type', 'withdrawal')->where('status', 'completed')->sum(fn ($t) => (float) $t->amount);
        $total_refund = $transactions->where('type', 'refund')->sum(fn ($t) => (float) $t->amount);
        $pending_withdrawals = $transactions->where('type', 'withdrawal')->where('status', 'pending')->sum(fn ($t) => (float) $t->amount);

        $total_earned = $total_from_bookings + $total_commission + $total_transfer - $total_refund;

        $wallet->setAttribute('summary', [
            'total_from_bookings' => round($total_from_bookings, 2),
            'total_commission' => round($total_commission, 2),
            'total_transfer' => round($total_transfer, 2),
            'total_withdrawn' => round($total_withdrawn, 2),
            'total_refund' => round($total_refund, 2),
            'pending_withdrawals' => round($pending_withdrawals, 2),
            'total_earned' => round($total_earned, 2),
        ]);

        $wallet->load(['transactions' => function ($query) {
            $query->latest()->limit(100);
        }]);

        return response()->json([
            'success' => true,
            'data' => $wallet
        ]);
    }

    /**
     * Get wallet transactions
     */
    public function transactions(Doctor $doctor): JsonResponse
    {
        $wallet = DoctorWallet::where('doctor_id', $doctor->id)->first();
        
        if (!$wallet) {
            return response()->json([
                'success' => true,
                'data' => []
            ]);
        }

        $transactions = $wallet->transactions()
            ->latest()
            ->limit(100)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $transactions
        ]);
    }

    /**
     * Transfer to doctor wallet
     */
    public function transfer(Request $request, Doctor $doctor): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:0',
            'description' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::transaction(function () use ($doctor, $request) {
            $wallet = DoctorWallet::firstOrCreate(
                ['doctor_id' => $doctor->id],
                ['balance' => 0, 'commission_rate' => 0.15]
            );

            $wallet->increment('balance', $request->amount);

            DoctorWalletTransaction::create([
                'doctor_id' => $doctor->id,
                'type' => 'transfer',
                'amount' => $request->amount,
                'description' => $request->description ?? 'Admin transfer',
                'status' => 'completed',
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Transfer completed successfully'
        ]);
    }

    /**
     * Set commission rate
     */
    public function setCommission(Request $request, Doctor $doctor): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'commission_rate' => 'required|numeric|min:0|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $wallet = DoctorWallet::firstOrCreate(
            ['doctor_id' => $doctor->id],
            ['balance' => 0, 'commission_rate' => 0.15]
        );

        // Convert percentage to decimal (15 -> 0.15)
        $commissionRate = $request->commission_rate > 1 
            ? $request->commission_rate / 100 
            : $request->commission_rate;

        $wallet->update(['commission_rate' => $commissionRate]);

        return response()->json([
            'success' => true,
            'message' => 'Commission rate updated successfully',
            'data' => $wallet
        ]);
    }
}
