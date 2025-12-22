<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MaintenanceHistory extends Model
{
    protected $fillable = [
        'scheduled_maintenance_id',
        'action',
        'performed_by',
        'notes',
    ];

    public function scheduledMaintenance()
    {
        return $this->belongsTo(ScheduledMaintenance::class);
    }
}
