<?php

namespace App\Services;

use App\Models\User;
use App\Models\Workshop;
use Illuminate\Database\Eloquent\Collection;

class SettingsService
{
    /**
     * @return Collection<int, User>
     */
    public function users(): Collection
    {
        return User::query()->orderBy('id')->get();
    }

    public function save(array $dto, int $currentUserId): void
    {
        $workshop = $this->workshop();
        $workshop->fill([
            'name' => $dto['workshop']['name'],
            'document' => $dto['workshop']['document'],
            'phone' => $dto['workshop']['phone'],
            'whatsapp' => $dto['workshop']['whatsapp'],
            'email' => $dto['workshop']['email'],
            'address' => $dto['workshop']['address'],
            'maintenance_alerts' => $dto['preferences']['maintenanceAlerts'],
            'contact_reminders' => $dto['preferences']['contactReminders'],
            'weekly_report' => $dto['preferences']['weeklyReport'],
            'default_reminder_days' => $dto['preferences']['defaultReminderDays'],
        ])->save();

        $keepIds = [$currentUserId];
        foreach ($dto['users'] as $item) {
            if (! trim($item['email'] ?? '')) {
                continue;
            }
            $existing = ! empty($item['id']) ? User::find($item['id']) : null;
            $payload = [
                'name' => $item['name'],
                'email' => strtolower(trim($item['email'])),
                'role' => $item['role'],
                'active' => $item['active'],
            ];
            if (! $existing || ! empty($item['password'])) {
                $payload['password'] = bcrypt($item['password'] ?? '123456');
            }
            $user = $existing ?: new User;
            $user->fill($payload)->save();
            $keepIds[] = $user->id;
        }

        User::query()->whereNotIn('id', $keepIds)->delete();
    }

    public function workshop(): Workshop
    {
        $existing = Workshop::query()->first();
        if ($existing) {
            return $existing;
        }

        return Workshop::create([
            'name' => 'Oficina Auto Center',
            'document' => '',
            'phone' => '',
            'whatsapp' => '',
            'email' => 'contato@oficina.com',
            'address' => '',
        ]);
    }
}
