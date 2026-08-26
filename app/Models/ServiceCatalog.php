<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceCatalog extends Model
{
    public $timestamps = false;

    protected $table = 'services';

    protected $fillable = [
        'name',
        'description',
        'price',
        'interval_months',
        'interval_mileage',
        'active',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'float',
            'active' => 'boolean',
        ];
    }
}
