<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserWalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;

class AdminUserController extends Controller
{
    /**
     * Display a listing of users
     */
    public function index(Request $request): JsonResponse
    {
        $query = User::with('roles');

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Search
        if ($request->has('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('username', 'like', '%' . $request->search . '%')
                  ->orWhere('phone', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }

        $users = $query->latest()->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $users
        ]);
    }

    /**
     * Store a newly created user
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string|unique:users,username',
            'phone' => 'required|string|unique:users,phone',
            'email' => 'nullable|email|unique:users,email',
            'password' => 'required|string|min:6',
            'status' => 'sometimes|in:pending,active,suspended',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::create([
            'username' => $request->username,
            'phone' => $request->phone,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'status' => $request->status ?? 'active',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'User created successfully',
            'data' => $user
        ], 201);
    }

    /**
     * Display the specified user
     */
    public function show(User $user): JsonResponse
    {
        $user->load(['roles', 'addresses', 'orders', 'bookings']);

        return response()->json([
            'success' => true,
            'data' => $user
        ]);
    }

    /**
     * Update the specified user
     */
    public function update(Request $request, User $user): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'username' => 'sometimes|string|unique:users,username,' . $user->id,
            'phone' => 'sometimes|string|unique:users,phone,' . $user->id,
            'email' => 'nullable|email|unique:users,email,' . $user->id,
            'password' => 'sometimes|string|min:6',
            'status' => 'sometimes|in:pending,active,suspended',
            'wallet_balance' => 'sometimes|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $request->all();
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        $newBalance = null;
        if (array_key_exists('wallet_balance', $data)) {
            $newBalance = (float) $data['wallet_balance'];
            unset($data['wallet_balance']);
        }

        if ($newBalance !== null) {
            DB::transaction(function () use ($user, $newBalance) {
                $locked = User::lockForUpdate()->find($user->id);
                if (!$locked) {
                    return;
                }
                $before = (float) $locked->wallet_balance;
                if (abs($before - $newBalance) < 0.00001) {
                    return;
                }
                $diff = round($newBalance - $before, 2);
                $locked->update(['wallet_balance' => $newBalance]);
                UserWalletTransaction::create([
                    'user_id'        => $locked->id,
                    'type'           => $diff >= 0 ? 'credit' : 'debit',
                    'amount'         => abs($diff),
                    'balance_before' => $before,
                    'balance_after'  => $newBalance,
                    'description'    => 'Admin wallet adjustment',
                    'reference_type' => null,
                    'reference_id'   => null,
                ]);
            });
        }

        if (!empty($data)) {
            $user->refresh();
            $user->update($data);
        }

        return response()->json([
            'success' => true,
            'message' => 'User updated successfully',
            'data' => $user->fresh()
        ]);
    }

    /**
     * Remove the specified user
     */
    public function destroy(User $user): JsonResponse
    {
        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'User deleted successfully'
        ]);
    }

    /**
     * Suspend user
     */
    public function suspend(User $user): JsonResponse
    {
        $user->update(['status' => 'suspended']);

        return response()->json([
            'success' => true,
            'message' => 'User suspended successfully',
            'data' => $user
        ]);
    }

    /**
     * Activate user
     */
    public function activate(User $user): JsonResponse
    {
        $user->update(['status' => 'active']);

        return response()->json([
            'success' => true,
            'message' => 'User activated successfully',
            'data' => $user
        ]);
    }
}
