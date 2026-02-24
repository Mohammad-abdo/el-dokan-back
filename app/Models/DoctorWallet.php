<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DoctorWallet extends Model
{
    use HasFactory;

    protected $fillable = [
        'doctor_id',
        'balance',
        'commission_rate',
    ];

    protected $casts = [
        'balance' => 'decimal:2',
        'commission_rate' => 'decimal:2',
    ];

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    public function transactions()
    {
        return $this->hasMany(DoctorWalletTransaction::class, 'doctor_id', 'doctor_id');
    }
}
