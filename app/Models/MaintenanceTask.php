<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MaintenanceTask extends Model
{
    protected $fillable = [
        'name',
        'description',
        'frequency_type',
        'estimated_duration',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
        'estimated_duration' => 'integer',
    ];
}
