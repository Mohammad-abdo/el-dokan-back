<?php

namespace App\Services;

use App\Models\DoctorWallet;
use App\Models\DoctorWalletTransaction;
use App\Models\Doctor;
use Illuminate\Support\Facades\DB;

class DoctorWalletService
{
    /**
     * Get or create doctor wallet
     */
    public function getWallet(Doctor $doctor): DoctorWallet
    {
        return DoctorWallet::firstOrCreate(
            ['doctor_id' => $doctor->id],
            ['balance' => 0, 'commission_rate' => 0.15] // Default 15% commission
        );
    }
    
    /**
     * Add commission to doctor wallet
     */
    public function addCommission(Doctor $doctor, float $amount, string $description = ''): DoctorWalletTransaction
    {
        return DB::transaction(function () use ($doctor, $amount, $description) {
            $wallet = $this->getWallet($doctor);
            $wallet->increment('balance', $amount);
            
            return DoctorWalletTransaction::create([
                'doctor_id' => $doctor->id,
                'type' => 'commission',
                'amount' => $amount,
                'description' => $description ?: 'Commission from prescription',
                'status' => 'completed',
            ]);
        });
    }
    
    /**
     * Process withdrawal request
     */
    public function requestWithdrawal(Doctor $doctor, float $amount): DoctorWalletTransaction
    {
        $wallet = $this->getWallet($doctor);
        
        if ($wallet->balance < $amount) {
            throw new \Exception('Insufficient balance');
        }
        
        return DB::transaction(function () use ($wallet, $amount) {
            $wallet->decrement('balance', $amount);
            
            return DoctorWalletTransaction::create([
                'doctor_id' => $wallet->doctor_id,
                'type' => 'withdrawal',
                'amount' => $amount,
                'status' => 'pending',
            ]);
        });
    }
    
    /**
     * Get wallet transactions
     */
    public function getTransactions(Doctor $doctor, int $limit = 50): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $wallet = $this->getWallet($doctor);
        
        return DoctorWalletTransaction::where('doctor_id', $doctor->id)
            ->orderBy('created_at', 'desc')
            ->paginate($limit);
    }
}




