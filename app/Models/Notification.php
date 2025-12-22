<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $fillable = [
        'scheduled_maintenance_id',
        'contact_id',
        'sent_at',
        'status',
        'message_content',
        'error_message',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
    ];

    public function scheduledMaintenance()
    {
        return $this->belongsTo(ScheduledMaintenance::class);
    }

    public function contact()
    {
        return $this->belongsTo(Contact::class);
    }
}
