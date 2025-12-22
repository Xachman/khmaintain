<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    protected $fillable = [
        'kingdom_hall_id',
        'name',
        'email',
        'phone',
        'role',
        'notify_email',
        'notify_sms',
        'active',
    ];

    protected $casts = [
        'notify_email' => 'boolean',
        'notify_sms' => 'boolean',
        'active' => 'boolean',
    ];

    public function kingdomHall()
    {
        return $this->belongsTo(KingdomHall::class);
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }
}
