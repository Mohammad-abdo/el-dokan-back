<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DoctorPrescriptionItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'doctor_prescription_id',
        'medication_name',
        'dosage',
        'quantity',
        'price',
        'status',
        'duration_days',
        'instructions',
    ];

    protected $casts = [
        'price' => 'decimal:2',
    ];

    public function prescription()
    {
        return $this->belongsTo(DoctorPrescription::class, 'doctor_prescription_id');
    }
}
