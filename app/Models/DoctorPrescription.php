<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DoctorPrescription extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'prescription_number',
        'doctor_id',
        'patient_id',
        'prescription_name',
        'patient_name',
        'patient_phone',
        'notes',
        'share_link',
        'is_shared',
        'is_template',
    ];

    protected $casts = [
        'is_shared' => 'boolean',
        'is_template' => 'boolean',
    ];

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    public function patient()
    {
        return $this->belongsTo(User::class, 'patient_id');
    }

    public function items()
    {
        return $this->hasMany(DoctorPrescriptionItem::class);
    }
}
