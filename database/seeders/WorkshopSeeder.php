<?php

namespace Database\Seeders;

use App\Models\Workshop;
use Illuminate\Database\Seeder;

class WorkshopSeeder extends Seeder
{
    public function run(): void
    {
        Workshop::query()->firstOrCreate([], [
            'name' => 'Oficina Auto Center',
            'document' => '12.345.678/0001-90',
            'phone' => '(11) 3333-1212',
            'whatsapp' => '(11) 99999-1212',
            'email' => 'contato@autocenter.com.br',
            'address' => 'Av. das Oficinas, 250 - São Paulo/SP',
            'maintenance_alerts' => true,
            'contact_reminders' => true,
            'weekly_report' => false,
            'default_reminder_days' => 15,
            'whatsapp_template' => \App\Support\WhatsApp::DEFAULT_TEMPLATE,
        ]);
    }
}
