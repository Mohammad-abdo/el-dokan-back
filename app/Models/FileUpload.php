<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FileUpload extends Model
{
    use HasFactory;

    protected $fillable = [
        'uploadable_type',
        'uploadable_id',
        'file_type',
        'file_name',
        'file_path',
        'file_url',
        'file_size',
        'mime_type',
        'description',
    ];

    protected $casts = [
        'file_size' => 'integer',
    ];
}
