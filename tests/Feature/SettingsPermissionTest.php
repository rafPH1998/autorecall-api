<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Workshop;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SettingsPermissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_attendant_cannot_update_settings(): void
    {
        $this->seedWorkshop();
        $user = $this->user('Atendente');
        Sanctum::actingAs($user);

        $this->putJson('/settings', $this->payload($user))
            ->assertForbidden()
            ->assertJson(['message' => 'Apenas o administrador da oficina pode alterar estas configurações.']);
    }

    public function test_administrator_can_update_settings(): void
    {
        $this->seedWorkshop();
        $user = $this->user('Administrador');
        Sanctum::actingAs($user);

        $this->putJson('/settings', $this->payload($user))
            ->assertOk()
            ->assertJsonPath('workshop.name', 'Oficina Teste');
    }

    private function seedWorkshop(): void
    {
        Workshop::query()->create([
            'name' => 'Oficina Auto Center',
            'document' => '12.345.678/0001-90',
            'phone' => '(11) 3333-1212',
            'whatsapp' => '(11) 99999-1212',
            'email' => 'contato@autocenter.com.br',
            'address' => 'Av. das Oficinas, 250',
            'maintenance_alerts' => true,
            'contact_reminders' => true,
            'weekly_report' => false,
            'default_reminder_days' => 15,
        ]);
    }

    private function user(string $role): User
    {
        return User::query()->create([
            'name' => $role,
            'email' => strtolower($role).'@oficina.test',
            'password' => password_hash('123456', PASSWORD_BCRYPT),
            'role' => $role,
            'active' => true,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(User $user): array
    {
        return [
            'workshop' => [
                'name' => 'Oficina Teste',
                'document' => '12.345.678/0001-90',
                'phone' => '(11) 3333-1212',
                'whatsapp' => '(11) 99999-1212',
                'email' => 'contato@oficina.test',
                'address' => 'Rua A, 1',
            ],
            'users' => [[
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'active' => true,
            ]],
            'preferences' => [
                'maintenanceAlerts' => true,
                'contactReminders' => true,
                'weeklyReport' => false,
                'defaultReminderDays' => 15,
                'whatsappTemplate' => 'Olá, {nome}',
            ],
        ];
    }
}
