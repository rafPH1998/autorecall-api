<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $staff = [
            ['name' => 'Rafael Pereira', 'email' => 'rafael@autocenter.com.br', 'role' => 'Administrador'],
            ['name' => 'Marina Lopes', 'email' => 'marina@autocenter.com.br', 'role' => 'Atendente'],
            ['name' => 'Carlos Silva', 'email' => 'carlos@autocenter.com.br', 'role' => 'Mecânico'],
        ];

        foreach ($staff as $item) {
            $user = User::query()->firstOrNew(['email' => $item['email']]);
            $user->fill($item + ['active' => true]);

            // Senha só é definida na criação, para não sobrescrever a que o
            // usuário já trocou pelo painel.
            if (! $user->exists) {
                $user->password = Hash::make('123456');
            }

            $user->save();
        }
    }
}
