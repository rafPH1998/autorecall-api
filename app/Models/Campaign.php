<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Campaign extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'name',
        'months',
        'message',
    ];

    public function customers(): BelongsToMany
    {
        return $this->belongsToMany(Customer::class, 'campaign_contacts')
            ->withPivot('contact_id');
    }
}
