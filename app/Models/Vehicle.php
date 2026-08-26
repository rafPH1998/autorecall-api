<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Vehicle extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'customer_id',
        'plate',
        'brand',
        'model',
        'year',
        'mileage',
        'next_maintenance',
        'maintenance_status',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
