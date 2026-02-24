<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Doctor extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'name',
        'name_ar',
        'name_en',
        'specialty',
        'specialty_ar',
        'specialty_en',
        'photo_url',
        'rating',
        'consultation_price',
        'discount_percentage',
        'available_days',
        'available_hours_start',
        'available_hours_end',
        'location',
        'location_ar',
        'location_en',
        'consultation_duration',
        'is_active',
        'status',
        'suspension_reason',
        'suspended_at',
    ];

    protected $casts = [
        'rating' => 'decimal:2',
        'consultation_price' => 'decimal:2',
        'discount_percentage' => 'decimal:2',
        'available_days' => 'array',
        'is_active' => 'boolean',
        'suspended_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function medicalCenters()
    {
        return $this->belongsToMany(MedicalCenter::class, 'doctor_medical_centers');
    }

    public function primaryMedicalCenter()
    {
        return $this->belongsTo(MedicalCenter::class, 'primary_medical_center_id');
    }

    public function ratings()
    {
        return $this->morphMany(Rating::class, 'rateable');
    }

    public function wallet()
    {
        return $this->hasOne(DoctorWallet::class);
    }

    public function selectedTreatments()
    {
        return $this->hasMany(DoctorSelectedTreatment::class);
    }
}
