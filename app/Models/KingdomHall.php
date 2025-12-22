<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KingdomHall extends Model
{
    protected $fillable = [
        'name',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function contacts()
    {
        return $this->hasMany(Contact::class);
    }

    public function scheduledMaintenances()
    {
        return $this->hasMany(ScheduledMaintenance::class);
    }
}
