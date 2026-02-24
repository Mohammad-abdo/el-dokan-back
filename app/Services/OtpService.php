<?php

namespace App\Services;

use App\Models\OtpVerification;
use Illuminate\Support\Facades\Log;

class OtpService
{
    /**
     * Generate and send OTP
     */
    public function generateOtp(string $phone, string $type = 'verification'): string
    {
        // Generate 6-digit OTP
        $otp = str_pad((string)rand(0, 999999), 6, '0', STR_PAD_LEFT);
        
        // Store OTP in database
        OtpVerification::updateOrCreate(
            ['phone' => $phone, 'type' => $type ?? 'verification'],
            [
                'otp' => $otp,
                'otp_code' => $otp,
                'expires_at' => now()->addMinutes(5),
                'is_verified' => false,
                'verified' => false,
            ]
        );
        
        // TODO: Send OTP via SMS service (Twilio, etc.)
        Log::info("OTP generated for {$phone}: {$otp}");
        
        return $otp;
    }
    
    /**
     * Verify OTP
     */
    public function verifyOtp(string $phone, string $otp, string $type = 'verification'): bool
    {
        $otpRecord = OtpVerification::where('phone', $phone)
            ->where('type', $type ?? 'verification')
            ->where(function($query) use ($otp) {
                $query->where('otp', $otp)
                      ->orWhere('otp_code', $otp);
            })
            ->where(function($query) {
                $query->where('is_verified', false)
                      ->orWhere('verified', false);
            })
            ->where('expires_at', '>', now())
            ->first();
            
        if ($otpRecord) {
            $otpRecord->update([
                'is_verified' => true,
                'verified' => true,
                'verified_at' => now()
            ]);
            return true;
        }
        
        return false;
    }
    
    /**
     * Check if OTP exists and is valid
     */
    public function hasValidOtp(string $phone, string $type = 'verification'): bool
    {
        return OtpVerification::where('phone', $phone)
            ->where('type', $type ?? 'verification')
            ->where(function($query) {
                $query->where('is_verified', false)
                      ->orWhere('verified', false);
            })
            ->where('expires_at', '>', now())
            ->exists();
    }
}

