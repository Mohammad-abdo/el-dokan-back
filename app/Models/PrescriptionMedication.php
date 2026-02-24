<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PrescriptionMedication extends Model
{
    use HasFactory;

    protected $fillable = [
        'prescription_id',
        'medication_name',
        'form',
        'quantity',
        'duration_days',
        'dosage_instructions',
        'price',
    ];

    protected $casts = [
        'price' => 'decimal:2',
    ];

    public function prescription()
    {
        return $this->belongsTo(Prescription::class);
    }
}
