<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LicenseVerificationLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_code',
        'domain',
        'ip_address',
        'status',
        'message',
    ];
}
