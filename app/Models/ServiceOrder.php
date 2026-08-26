<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ServiceOrder extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'number',
        'customer_id',
        'vehicle_id',
        'date',
        'mileage',
        'status',
        'notes',
        'total',
    ];

    protected function casts(): array
    {
        return [
            'total' => 'float',
        ];
    }

    public function items(): HasMany
    {
        return $this->hasMany(ServiceOrderItem::class, 'order_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }
}
