<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Booking extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'booking_number',
        'user_id',
        'doctor_id',
        'booking_type',
        'appointment_date',
        'appointment_time',
        'patient_name',
        'status',
        'total_amount',
        'payment_method',
        'payment_status',
        'rating',
        'complaint',
    ];

    protected $casts = [
        'appointment_date' => 'date',
        'total_amount' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }
}
