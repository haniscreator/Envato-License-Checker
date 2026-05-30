<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class License extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_code',
        'domain',
        'buyer_username',
        'item_id',
        'item_name',
        'license_type',
        'purchase_date',
        'status',
        'last_checked_at',
    ];

    protected $casts = [
        'purchase_date' => 'datetime',
        'last_checked_at' => 'datetime',
    ];

    /**
     * Scope a query to only include active licenses.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
