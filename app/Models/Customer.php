<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'name',
        'phone',
        'whatsapp',
        'email',
        'document',
        'last_visit',
    ];

    public function vehicles(): HasMany
    {
        return $this->hasMany(Vehicle::class);
    }
}
