<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Auth\RegisterRequest;
use App\Http\Requests\Api\Auth\LoginRequest;
use App\Http\Requests\Api\Auth\OtpVerifyRequest;
use App\Http\Requests\Api\Auth\ChangePasswordRequest;
use App\Http\Requests\Api\Auth\SocialLoginRequest;
use App\Models\FailedLoginAttempt;
use App\Models\OtpVerification;
use App\Models\PasswordResetToken;
use App\Models\RefreshToken;
use App\Models\User;
use App\Models\Doctor;
use App\Models\Shop;
use App\Models\CompanyPlan;
use App\Services\OtpService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Laravel\Sanctum\PersonalAccessToken;

class AuthController extends Controller
{
    const MAX_LOGIN_ATTEMPTS = 5;
    const LOCKOUT_MINUTES = 15;
    const OTP_MAX_ATTEMPTS = 5;
    const TOKEN_EXPIRY_HOURS = 24;
    const REFRESH_TOKEN_DAYS = 30;

    protected OtpService $otpService;

    public function __construct(OtpService $otpService)
    {
        $this->otpService = $otpService;
    }

    protected function formatUserData(User $user): array
    {
        if (!$user->relationLoaded('roles')) {
            $user->load('roles');
        }

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

    protected function createAuthToken(User $user): array
    {
        $accessToken = $user->createToken('auth_token', ['*'], new \DateTimeImmutable('+' . self::TOKEN_EXPIRY_HOURS . ' hours'))->plainTextToken;

        $refreshToken = RefreshToken::create([
            'user_id'    => $user->id,
            'token'      => hash('sha256', Str::random(64)),
            'expires_at' => new \DateTimeImmutable('+' . self::REFRESH_TOKEN_DAYS . ' days'),
        ]);

        return [
            'access_token'  => $accessToken,
            'refresh_token' => $refreshToken->token,
            'expires_in'    => self::TOKEN_EXPIRY_HOURS * 3600,
        ];
    }

    // ─── Login attempt tracking helpers ───────────────────────────────────────

    protected function recordFailedLogin(string $identifier, string $ip): void
    {
        $record = FailedLoginAttempt::where('identifier', $identifier)
            ->where('ip_address', $ip)
            ->first();

        if ($record) {
            $record->attempts++;
            $record->last_attempt_at = now();
            if ($record->attempts >= self::MAX_LOGIN_ATTEMPTS) {
                $record->locked_until = new \DateTimeImmutable('+' . self::LOCKOUT_MINUTES . ' minutes');
            }
            $record->save();
        } else {
            FailedLoginAttempt::create([
                'identifier'      => $identifier,
                'ip_address'      => $ip,
                'attempts'        => 1,
                'last_attempt_at' => now(),
            ]);
        }
    }

    protected function isLockedOut(string $identifier, string $ip): bool
    {
        $record = FailedLoginAttempt::where('identifier', $identifier)
            ->where('ip_address', $ip)
            ->first();

        if (!$record || !$record->locked_until) {
            return false;
        }

        if ($record->locked_until->isPast()) {
            $record->delete();
            return false;
        }

        return true;
    }

    protected function clearLoginAttempts(string $identifier, string $ip): void
    {
        FailedLoginAttempt::where('identifier', $identifier)
            ->where('ip_address', $ip)
            ->delete();
    }

    // ─── Public endpoints ──────────────────────────────────────────────────────

    public function register(RegisterRequest $request): JsonResponse
    {
        $user = User::create([
            'username' => $request->username,
            'phone'    => $request->phone,
            'email'    => $request->email,
            'password' => $request->password ? Hash::make($request->password) : null,
            'status'   => 'pending',
        ]);

        $this->otpService->generateOtp($request->phone, 'verification');

        return response()->json([
            'success' => true,
            'message' => 'User registered successfully. Please verify OTP.',
            'data' => [
                'user_id' => $user->id,
                'phone'   => $user->phone,
            ]
        ], 201);
    }

    public function registerDoctor(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name'                  => 'required|string|max:255',
            'email'                 => 'required|email|unique:users,email',
            'password'              => 'required|string|min:6|confirmed',
            'phone'                 => 'required|string|max:50',
            'specialty'             => 'required|string|max:255',
            'available_hours_start' => 'required|string|max:10',
            'available_hours_end'   => 'required|string|max:10',
            'location'              => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Validation error', 'errors' => $validator->errors()], 422);
        }

        $user = User::create([
            'username' => $request->email,
            'email'    => $request->email,
            'phone'    => $request->phone,
            'password' => Hash::make($request->password),
            'status'   => 'active',
        ]);

        $user->assignRole('doctor');
        $user->update(['role' => 'doctor']);

        Doctor::create([
            'user_id'               => $user->id,
            'name'                  => $request->name,
            'specialty'             => $request->specialty,
            'available_hours_start' => $request->available_hours_start,
            'available_hours_end'   => $request->available_hours_end,
            'location'              => $request->location ?? '',
            'available_days'        => $request->available_days ?? [],
            'status'                => 'pending_review',
            'is_active'             => false,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Thank you for registering. Your account is under review.',
            'data' => ['user_id' => $user->id, 'email' => $user->email],
        ], 201);
    }

    public function registerCompany(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'phone'    => 'required|string|max:50',
            'address'  => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Validation error', 'errors' => $validator->errors()], 422);
        }

        $user = User::create([
            'username' => $request->email,
            'email'    => $request->email,
            'phone'    => $request->phone,
            'password' => Hash::make($request->password),
            'status'   => 'active',
        ]);

        $user->assignRole('company');
        $user->update(['role' => 'company']);

        $defaultPlan = CompanyPlan::where('slug', 'basic')->first();
        $shop = Shop::create([
            'user_id'         => $user->id,
            'name'            => $request->name,
            'category'        => 'company',
            'address'         => $request->address,
            'phone'           => $request->phone,
            'is_active'       => false,
            'vendor_status'   => Shop::VENDOR_STATUS_PENDING,
            'company_plan_id' => $defaultPlan?->id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Company registration submitted. Pending admin approval.',
            'data' => [
                'user_id'       => $user->id,
                'company_id'    => $shop->id,
                'email'         => $user->email,
                'vendor_status' => 'pending_approval',
            ],
        ], 201);
    }

    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Validation error', 'errors' => $validator->errors()], 422);
        }

        $identifier = $request->email;
        $ip = $request->ip();

        if ($this->isLockedOut($identifier, $ip)) {
            return response()->json([
                'success' => false,
                'message' => 'Too many failed attempts. Account temporarily locked. Please try again later.',
            ], 429);
        }

        $user = User::where('email', $identifier)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            $this->recordFailedLogin($identifier, $ip);
            return response()->json(['success' => false, 'message' => 'Invalid credentials'], 401);
        }

        $this->clearLoginAttempts($identifier, $ip);

        if ($user->status === 'pending') {
            $user->update(['status' => 'active']);
        }

        $tokens = $this->createAuthToken($user);

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'data' => array_merge(['user' => $this->formatUserData($user)], $tokens),
        ]);
    }

    public function socialLogin(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'provider'    => 'required|in:google,apple',
            'provider_id' => 'required|string',
            'email'       => 'nullable|email',
            'name'        => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Validation error', 'errors' => $validator->errors()], 422);
        }

        $user = User::where('provider', $request->provider)
            ->where('provider_id', $request->provider_id)
            ->first();

        if (!$user) {
            $user = User::create([
                'username'    => $request->name ?? Str::random(8),
                'email'       => $request->email,
                'provider'    => $request->provider,
                'provider_id' => $request->provider_id,
                'status'      => 'active',
            ]);
        }

        $tokens = $this->createAuthToken($user);

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'data' => array_merge(['user' => $this->formatUserData($user)], $tokens),
        ]);
    }

    public function verifyOtp(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string',
            'otp'   => 'required|string|size:6',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Validation error', 'errors' => $validator->errors()], 422);
        }

        $otpRecord = OtpVerification::where('phone', $request->phone)
            ->where('is_verified', false)
            ->latest()
            ->first();

        if ($otpRecord) {
            if ($otpRecord->locked_until && !$otpRecord->locked_until->isPast()) {
                return response()->json(['success' => false, 'message' => 'OTP locked. Please request a new one.'], 429);
            }

            if ($otpRecord->attempts >= self::OTP_MAX_ATTEMPTS) {
                $otpRecord->update(['locked_until' => new \DateTimeImmutable('+10 minutes')]);
                return response()->json(['success' => false, 'message' => 'Too many attempts. OTP locked.'], 429);
            }

            $otpRecord->increment('attempts');
        }

        $verified = $this->otpService->verifyOtp($request->phone, $request->otp);

        if (!$verified) {
            return response()->json(['success' => false, 'message' => 'Invalid or expired OTP'], 400);
        }

        $user = User::where('phone', $request->phone)->first();

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User not found'], 404);
        }

        if ($user->status === 'pending') {
            $user->update(['status' => 'active']);
        }

        $tokens = $this->createAuthToken($user);

        return response()->json([
            'success' => true,
            'message' => 'OTP verified successfully',
            'data' => array_merge(['user' => $this->formatUserData($user)], $tokens),
        ]);
    }

    public function resendOtp(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Validation error', 'errors' => $validator->errors()], 422);
        }

        $this->otpService->generateOtp($request->phone, 'verification');

        return response()->json(['success' => true, 'message' => 'OTP resent successfully']);
    }

    public function guestLogin(Request $request): JsonResponse
    {
        $fingerprint = hash('sha256', $request->ip() . '|' . $request->userAgent());
        $guestToken = hash('sha256', $fingerprint . '|' . time());

        return response()->json([
            'success' => true,
            'message' => 'Guest session created',
            'data' => ['guest_token' => $guestToken],
        ]);
    }

    public function forgotPassword(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string|exists:users,phone',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Validation error', 'errors' => $validator->errors()], 422);
        }

        $this->otpService->generateOtp($request->phone, 'password_reset');

        DB::table('password_reset_tokens')->upsert(
            ['phone' => $request->phone, 'token' => Str::random(64), 'created_at' => now()],
            ['phone'],
            ['token', 'created_at']
        );

        return response()->json(['success' => true, 'message' => 'OTP sent to your phone number.']);
    }

    public function resetPassword(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'phone'        => 'required|string|exists:users,phone',
            'otp'          => 'required|string|size:6',
            'new_password' => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Validation error', 'errors' => $validator->errors()], 422);
        }

        $verified = $this->otpService->verifyOtp($request->phone, $request->otp);

        if (!$verified) {
            return response()->json(['success' => false, 'message' => 'Invalid or expired OTP'], 400);
        }

        $user = User::where('phone', $request->phone)->firstOrFail();
        $user->update(['password' => Hash::make($request->new_password)]);

        DB::table('password_reset_tokens')->where('phone', $request->phone)->delete();

        return response()->json(['success' => true, 'message' => 'Password reset successfully.']);
    }

    public function refreshToken(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'refresh_token' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Validation error', 'errors' => $validator->errors()], 422);
        }

        $refreshRecord = RefreshToken::where('token', $request->refresh_token)->first();

        if (!$refreshRecord || $refreshRecord->isExpired()) {
            return response()->json(['success' => false, 'message' => 'Invalid or expired refresh token'], 401);
        }

        $user = $refreshRecord->user;
        $refreshRecord->delete();

        $tokens = $this->createAuthToken($user);

        return response()->json([
            'success' => true,
            'message' => 'Token refreshed successfully',
            'data' => array_merge(['user' => $this->formatUserData($user)], $tokens),
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'user' => $this->formatUserData($request->user()),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['success' => true, 'message' => 'Logged out successfully']);
    }

    public function changePassword(ChangePasswordRequest $request): JsonResponse
    {
        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json(['success' => false, 'message' => 'Current password is incorrect'], 400);
        }

        $user->update(['password' => Hash::make($request->new_password)]);

        return response()->json(['success' => true, 'message' => 'Password changed successfully']);
    }

    public function loginWithLink(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'link'  => 'required|string',
            'phone' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Validation error', 'errors' => $validator->errors()], 422);
        }

        $user = User::where('phone', $request->phone)->first();

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User not found'], 404);
        }

        $tokens = $this->createAuthToken($user);

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'data' => array_merge(['user' => $this->formatUserData($user)], $tokens),
        ]);
    }
}
