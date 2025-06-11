<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EnrollUser extends Model
{
    use HasFactory;

    protected $table = 'enrollusers';

    protected $fillable = [
        'full_name',
        'email',
        'biometric_method',
        'face_descriptor',
        'face_image',
        'fingerprint_template',
        'fingerprint_credential',
    ];

    protected $casts = [
        'face_descriptor' => 'array',
    ];
}
