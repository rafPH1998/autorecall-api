<?php

namespace Database\Seeders;

use App\Models\AppNotification;
use Illuminate\Database\Seeder;

class NotificationSeeder extends Seeder
{
    public function run(): void
    {
        if (AppNotification::query()->exists()) {
            return;
        }

        $notifications = [
            ['title' => 'Manutenção atrasada', 'description' => 'T-Cross de Bruno Lima precisa de revisão dos freios.', 'type' => 'maintenance', 'read' => false],
            ['title' => 'Novo cliente para contato', 'description' => 'Diego Alves não retorna há mais de 6 meses.', 'type' => 'contact', 'read' => false],
            ['title' => 'Manutenção próxima', 'description' => 'Honda Civic de Ana Souza vence em 11 dias.', 'type' => 'maintenance', 'read' => true],
            ['title' => 'Backup concluído', 'description' => 'Os dados da oficina foram sincronizados.', 'type' => 'system', 'read' => true],
        ];

        foreach ($notifications as $notification) {
            AppNotification::query()->create($notification);
        }
    }
}
