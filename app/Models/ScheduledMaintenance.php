<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScheduledMaintenance extends Model
{
    protected $fillable = [
        'kingdom_hall_id',
        'maintenance_task_id',
        'scheduled_date',
        'completed_date',
        'status',
        'notes',
    ];

    protected $casts = [
        'scheduled_date' => 'date',
        'completed_date' => 'date',
    ];

    public function kingdomHall()
    {
        return $this->belongsTo(KingdomHall::class);
    }

    public function maintenanceTask()
    {
        return $this->belongsTo(MaintenanceTask::class);
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function maintenanceHistories()
    {
        return $this->hasMany(MaintenanceHistory::class);
    }
}
