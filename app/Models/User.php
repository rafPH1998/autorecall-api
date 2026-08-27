<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

#[Fillable(['name', 'email', 'password', 'role', 'active'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    use HasApiTokens;

    public $timestamps = false;

    public function isAdministrator(): bool
    {
        $role = mb_strtolower(trim((string) $this->role));

        return in_array($role, ['administrador', 'admin', 'owner'], true);
    }

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
        ];
    }
}
