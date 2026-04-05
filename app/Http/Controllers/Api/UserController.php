<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserWalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    /**
     * Get user profile
     */
    public function profile(Request $request): JsonResponse
    {
        $user = $request->user()->load(['addresses', 'roles']);
        
        return response()->json([
            'success' => true,
            'data' => [
                'id' => $user->id,
                'username' => $user->username,
                'email' => $user->email,
                'phone' => $user->phone,
                'avatar_url' => $user->avatar_url,
                'wallet_balance' => $user->wallet_balance,
                'language_preference' => $user->language_preference,
                'status' => $user->status,
                'role' => $user->role,
                'user_type' => $user->user_type,
                'service_provider_type' => $user->service_provider_type,
                'roles' => $user->roles->map(function ($role) {
                    return [
                        'id' => $role->id,
                        'name' => $role->name,
                        'display_name' => $role->display_name ?? $role->name,
                    ];
                }),
                'role_names' => $user->roles->pluck('name')->toArray(),
                'addresses' => $user->addresses,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
            ]
        ]);
    }

    /**
     * Update user profile
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'username' => 'sometimes|string|unique:users,username,' . $request->user()->id,
            'email' => 'sometimes|email|unique:users,email,' . $request->user()->id,
            'avatar_url' => 'sometimes|string|url',
            'language_preference' => 'sometimes|string|in:ar,en',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();
        $user->update($request->only(['username', 'email', 'avatar_url', 'language_preference']));
        $user->load('roles');

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully',
            'data' => [
                'id' => $user->id,
                'username' => $user->username,
                'email' => $user->email,
                'phone' => $user->phone,
                'avatar_url' => $user->avatar_url,
                'wallet_balance' => $user->wallet_balance,
                'language_preference' => $user->language_preference,
                'status' => $user->status,
                'role' => $user->role,
                'user_type' => $user->user_type,
                'service_provider_type' => $user->service_provider_type,
                'roles' => $user->roles->map(function ($role) {
                    return [
                        'id' => $role->id,
                        'name' => $role->name,
                        'display_name' => $role->display_name ?? $role->name,
                    ];
                }),
                'role_names' => $user->roles->pluck('name')->toArray(),
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
            ]
        ]);
    }

    /**
     * Get user wallet
     */
    public function wallet(Request $request): JsonResponse
    {
        $user = $request->user();
        
        return response()->json([
            'success' => true,
            'data' => [
                'balance' => $user->wallet_balance,
                'currency' => 'EGP'
            ]
        ]);
    }

    /**
     * Top up wallet
     */
    public function topUpWallet(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();

        DB::transaction(function () use ($user, $request) {
            $lockedUser = User::lockForUpdate()->find($user->id);
            $balanceBefore = $lockedUser->wallet_balance;
            $lockedUser->increment('wallet_balance', $request->amount);
            UserWalletTransaction::create([
                'user_id'        => $lockedUser->id,
                'type'           => 'credit',
                'amount'         => $request->amount,
                'balance_before' => $balanceBefore,
                'balance_after'  => $lockedUser->fresh()->wallet_balance,
                'description'    => 'Wallet top-up',
                'reference_type' => null,
                'reference_id'   => null,
            ]);
        });

        $user->refresh();

        return response()->json([
            'success' => true,
            'message' => 'Wallet topped up successfully',
            'data' => ['balance' => $user->wallet_balance]
        ]);
    }
}
