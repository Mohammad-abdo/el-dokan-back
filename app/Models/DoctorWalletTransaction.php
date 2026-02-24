<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DoctorWalletTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'doctor_id',
        'type',
        'amount',
        'description',
        'status',
        'booking_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function wallet()
    {
        return $this->belongsTo(DoctorWallet::class, 'doctor_id', 'doctor_id');
    }

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }
}
