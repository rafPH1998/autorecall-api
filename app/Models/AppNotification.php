<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppNotification extends Model
{
    public const UPDATED_AT = null;

    protected $table = 'notifications';

    protected $fillable = [
        'title',
        'description',
        'type',
        'read',
    ];

    protected function casts(): array
    {
        return [
            'read' => 'boolean',
            'created_at' => 'datetime',
        ];
    }
}
