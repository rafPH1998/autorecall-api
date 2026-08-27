<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Workshop extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'name',
        'document',
        'phone',
        'whatsapp',
        'email',
        'address',
        'maintenance_alerts',
        'contact_reminders',
        'weekly_report',
        'default_reminder_days',
        'whatsapp_template',
    ];

    protected function casts(): array
    {
        return [
            'maintenance_alerts' => 'boolean',
            'contact_reminders' => 'boolean',
            'weekly_report' => 'boolean',
        ];
    }
}
