<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Doctor;
use App\Models\Shop;
use App\Models\CompanyPlan;
use App\Models\OtpVerification;
use App\Services\OtpService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Laravel\Sanctum\PersonalAccessToken;

class AuthController extends Controller
{
    protected OtpService $otpService;

    public function __construct(OtpService $otpService)
    {
        $this->otpService = $otpService;
    }

    /**
     * Format user data with roles
     */
    protected function formatUserData(User $user): array
    {
        // Load roles if not already loaded
        if (!$user->relationLoaded('roles')) {
            $user->load('roles');
        }

        // Sync role field with Spatie roles if role field is empty or different
        $roleNames = $user->roles->pluck('name')->toArray();
        if (empty($user->role) || !in_array($user->role, $roleNames)) {
            if (!empty($roleNames)) {
                $priority = ['admin', 'doctor', 'shop', 'driver', 'representative', 'user'];
                $primaryRole = collect($priority)->first(fn ($r) => in_array($r, $roleNames)) ?? $roleNames[0];
                $user->update(['role' => $primaryRole]);
                $user->refresh();
            }
        }

        $data = [
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
            'role_names' => $roleNames,
            'created_at' => $user->created_at,
            'updated_at' => $user->updated_at,
        ];

        if ($user->role === 'company') {
            $shop = Shop::where('user_id', $user->id)->first();
            $data['company_vendor_status'] = $shop ? ($shop->vendor_status ?? 'approved') : null;
            $data['company_id'] = $shop?->id;
        }

        return $data;
    }

    /**
     * Register a new user
     */
    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string|unique:users,username',
            'phone' => 'required|string|unique:users,phone',
            'email' => 'nullable|email|unique:users,email',
            'password' => 'nullable|string|min:6',
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
            'password' => $request->password ? Hash::make($request->password) : null,
            'status' => 'pending',
        ]);

        // Generate and send OTP
        $otp = $this->otpService->generateOtp($request->phone, 'verification');

        return response()->json([
            'success' => true,
            'message' => 'User registered successfully. Please verify OTP.',
            'data' => [
                'user_id' => $user->id,
                'phone' => $user->phone,
            ]
        ], 201);
    }

    /**
     * Register a new doctor (public). Account stays pending_review until admin activates.
     */
    public function registerDoctor(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'phone' => 'required|string|max:50',
            'specialty' => 'required|string|max:255',
            'available_hours_start' => 'required|string|max:10',
            'available_hours_end' => 'required|string|max:10',
            'location' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = User::create([
            'username' => $request->email,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'status' => 'active',
        ]);

        $user->assignRole('doctor');
        $user->update(['role' => 'doctor']);

        Doctor::create([
            'user_id' => $user->id,
            'name' => $request->name,
            'specialty' => $request->specialty,
            'available_hours_start' => $request->available_hours_start,
            'available_hours_end' => $request->available_hours_end,
            'location' => $request->location ?? '',
            'available_days' => $request->available_days ?? [],
            'status' => 'pending_review',
            'is_active' => false,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Thank you for registering. Your account is under review. We will notify you once it is activated.',
            'data' => [
                'user_id' => $user->id,
                'email' => $user->email,
            ],
        ], 201);
    }

    /**
     * Register a new company (pending admin approval).
     */
    public function registerCompany(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'phone' => 'required|string|max:50',
            'address' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = User::create([
            'username' => $request->email,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'status' => 'active',
        ]);

        $user->assignRole('company');
        $user->update(['role' => 'company']);

        $defaultPlan = CompanyPlan::where('slug', 'basic')->first();
        $shop = Shop::create([
            'user_id' => $user->id,
            'name' => $request->name,
            'category' => 'company',
            'address' => $request->address,
            'phone' => $request->phone,
            'is_active' => false,
            'vendor_status' => Shop::VENDOR_STATUS_PENDING,
            'company_plan_id' => $defaultPlan?->id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Company registration submitted. Your account is pending admin approval. You will be notified once activated.',
            'data' => [
                'user_id' => $user->id,
                'company_id' => $shop->id,
                'email' => $user->email,
                'vendor_status' => 'pending_approval',
            ],
        ], 201);
    }

    /**
     * Login user
     */
    public function login(Request $request): JsonResponse
    {
        // Validate email + password only
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'email' => $request->email,
                'password' => $request->password,
                'message' => 'Validation error ',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Find user by email
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found',
            ], 404);
        }

        // Check password
        if (!Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials',
            ], 401);
        }

        // Activate pending users
        if ($user->status === 'pending') {
            $user->update(['status' => 'active']);
        }

        // Create token
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'data' => [
                'user' => $this->formatUserData($user),
                'token' => $token,
            ]
        ]);
    }


    /**
     * Social login (Google, Apple)
     */
    public function socialLogin(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'provider' => 'required|in:google,apple',
            'provider_id' => 'required|string',
            'email' => 'nullable|email',
            'name' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::where('provider', $request->provider)
            ->where('provider_id', $request->provider_id)
            ->first();

        if (!$user) {
            // Create new user
            $user = User::create([
                'username' => $request->name ?? Str::random(8),
                'email' => $request->email,
                'provider' => $request->provider,
                'provider_id' => $request->provider_id,
                'status' => 'active',
            ]);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'data' => [
                'user' => $this->formatUserData($user),
                'token' => $token,
            ]
        ]);
    }

    /**
     * Verify OTP
     */
    public function verifyOtp(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string',
            'otp' => 'required|string|size:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $verified = $this->otpService->verifyOtp($request->phone, $request->otp);

        if (!$verified) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired OTP'
            ], 400);
        }

        $user = User::where('phone', $request->phone)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }

        // Activate user if pending
        if ($user->status === 'pending') {
            $user->update(['status' => 'active']);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'OTP verified successfully',
            'data' => [
                'user' => $this->formatUserData($user),
                'token' => $token,
            ]
        ]);
    }

    /**
     * Resend OTP
     */
    public function resendOtp(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $otp = $this->otpService->generateOtp($request->phone, 'verification');

        return response()->json([
            'success' => true,
            'message' => 'OTP resent successfully',
        ]);
    }

    /**
     * Guest login
     */
    public function guestLogin(): JsonResponse
    {
        $guestToken = Str::random(32);

        return response()->json([
            'success' => true,
            'message' => 'Guest session created',
            'data' => [
                'guest_token' => $guestToken,
            ]
        ]);
    }

    /**
     * Get current authenticated user
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user();
        
        return response()->json([
            'success' => true,
            'user' => $this->formatUserData($user),
        ]);
    }

    /**
     * Logout user
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully',
        ]);
    }

    /**
     * Change password
     */
    public function changePassword(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Current password is incorrect'
            ], 400);
        }

        $user->update([
            'password' => Hash::make($request->new_password)
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Password changed successfully',
        ]);
    }

    /**
     * Login with prescription link
     */
    public function loginWithLink(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'link' => 'required|string',
            'phone' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        // TODO: Verify prescription link and create session
        $user = User::where('phone', $request->phone)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }

        $token = $user->createToken('prescription_link_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'data' => [
                'user' => $this->formatUserData($user),
                'token' => $token,
            ]
        ]);
    }
}