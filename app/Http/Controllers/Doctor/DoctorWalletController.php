<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Services\DoctorWalletService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class DoctorWalletController extends Controller
{
    protected DoctorWalletService $walletService;

    public function __construct(DoctorWalletService $walletService)
    {
        $this->walletService = $walletService;
    }

    /**
     * Get doctor wallet
     */
    public function index(Request $request): JsonResponse
    {
        $doctor = $request->user()->doctor;
        
        if (!$doctor) {
            return response()->json([
                'success' => false,
                'message' => 'Doctor profile not found'
            ], 404);
        }
        
        $wallet = $this->walletService->getWallet($doctor);

        return response()->json([
            'success' => true,
            'data' => $wallet
        ]);
    }

    /**
     * Get wallet transactions
     */
    public function transactions(Request $request): JsonResponse
    {
        $doctor = $request->user()->doctor;
        
        if (!$doctor) {
            return response()->json([
                'success' => false,
                'message' => 'Doctor profile not found'
            ], 404);
        }
        
        $transactions = $this->walletService->getTransactions($doctor, 50);

        return response()->json([
            'success' => true,
            'data' => $transactions
        ]);
    }
}
