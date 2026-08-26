<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Maintenance extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'customer_id',
        'vehicle_id',
        'service_name',
        'due_date',
        'due_mileage',
        'status',
    ];
}
