<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DoctorMedicalCenter extends Model
{
    use HasFactory;

    protected $fillable = [
        'doctor_id',
        'medical_center_id',
    ];

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    public function medicalCenter()
    {
        return $this->belongsTo(MedicalCenter::class);
    }
}
