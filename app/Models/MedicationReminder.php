<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MedicationReminder extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'prescription_medication_id',
        'medication_name',
        'reminder_time',
        'time_period',
        'frequency',
        'specific_days',
        'duration',
        'duration_days',
        'is_active',
    ];

    protected $casts = [
        'specific_days' => 'array',
        'is_active' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function prescriptionMedication()
    {
        return $this->belongsTo(PrescriptionMedication::class);
    }
}
