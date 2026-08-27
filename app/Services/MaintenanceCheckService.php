<?php

namespace App\Services;

use App\Models\AppNotification;
use App\Models\Customer;
use App\Models\Maintenance;
use App\Models\Vehicle;
use App\Models\Workshop;
use Carbon\Carbon;
use Illuminate\Database\UniqueConstraintViolationException;

class MaintenanceCheckService
{
    /** @var list<int> */
    public const UPCOMING_WINDOWS = [7, 15, 30];

    /**
     * @return array{checked: int, updated: int, notified: int}
     */
    public function run(?Carbon $now = null): array
    {
        $today = ($now ?? Carbon::now('America/Sao_Paulo'))->timezone('America/Sao_Paulo')->toDateString();
        $workshop = Workshop::query()->first();
        $alertsEnabled = $workshop?->maintenance_alerts ?? true;
        $contactReminders = $workshop?->contact_reminders ?? true;

        $checked = 0;
        $updated = 0;
        $notified = 0;
        $vehicleIds = [];

        $rows = Maintenance::query()
            ->with(['vehicle.customer'])
            ->where('status', '!=', 'Concluída')
            ->orderBy('id')
            ->get();

        foreach ($rows as $maintenance) {
            $checked++;
            $vehicleIds[$maintenance->vehicle_id] = true;

            $status = $this->classify($maintenance, $today);
            if ($maintenance->status !== $status) {
                $maintenance->update(['status' => $status]);
                $updated++;
            }

            if ($alertsEnabled && $this->notifyMaintenance($maintenance, $status, $today)) {
                $notified++;
            }
        }

        foreach (array_keys($vehicleIds) as $vehicleId) {
            $this->syncVehicle((int) $vehicleId);
        }

        if ($contactReminders) {
            $notified += $this->notifyInactiveCustomers($today);
        }

        return compact('checked', 'updated', 'notified');
    }

    public function classify(Maintenance $maintenance, string $today): string
    {
        $due = $this->dueDate($maintenance);
        $mileage = (int) ($maintenance->vehicle?->mileage ?? 0);
        $overdueByDate = $due < $today;
        $overdueByMileage = $maintenance->due_mileage > 0 && $mileage >= $maintenance->due_mileage;

        return $overdueByDate || $overdueByMileage ? 'Atrasada' : 'Próxima';
    }

    public function upcomingWindow(string $due, string $today): ?int
    {
        if ($due < $today) {
            return null;
        }

        $daysLeft = (int) Carbon::createFromFormat('Y-m-d', $today, 'America/Sao_Paulo')
            ->diffInDays(Carbon::createFromFormat('Y-m-d', $due, 'America/Sao_Paulo'));

        foreach (self::UPCOMING_WINDOWS as $window) {
            if ($daysLeft <= $window) {
                return $window;
            }
        }

        return null;
    }

    public function sourceKey(Maintenance $maintenance, string $status, ?int $window = null): string
    {
        $base = sprintf('maintenance:%d:%s:%s', $maintenance->id, $status, $this->dueDate($maintenance));

        return $window ? "{$base}:D{$window}" : $base;
    }

    private function notifyMaintenance(Maintenance $maintenance, string $status, string $today): bool
    {
        if ($status === 'Atrasada') {
            return $this->notifyOnce(
                $this->sourceKey($maintenance, $status),
                'Manutenção atrasada',
                $this->description($maintenance, $status, $today),
                'maintenance',
            );
        }

        $window = $this->upcomingWindow($this->dueDate($maintenance), $today);
        if ($status !== 'Próxima' || ! $window) {
            return false;
        }

        return $this->notifyOnce(
            $this->sourceKey($maintenance, $status, $window),
            'Manutenção próxima',
            $this->description($maintenance, $status, $today, $window),
            'maintenance',
        );
    }

    private function notifyInactiveCustomers(string $today): int
    {
        $created = 0;
        $cutoff = Carbon::createFromFormat('Y-m-d', $today, 'America/Sao_Paulo')->subMonths(6)->toDateString();
        $monthKey = substr($today, 0, 7);

        $customers = Customer::query()
            ->where(function ($query) use ($cutoff) {
                $query->whereNull('last_visit')->orWhere('last_visit', '<', $cutoff);
            })
            ->orderBy('id')
            ->get();

        foreach ($customers as $customer) {
            $when = $customer->last_visit
                ? Carbon::parse($customer->last_visit)->format('d/m/Y')
                : 'sem visitas';
            if ($this->notifyOnce(
                "customer:{$customer->id}:inactive:{$monthKey}",
                'Cliente para contato',
                "{$customer->name} está sem retorno ({$when}).",
                'contact',
            )) {
                $created++;
            }
        }

        return $created;
    }

    private function notifyOnce(string $source, string $title, string $description, string $type): bool
    {
        $now = now();
        $payload = [
            'title' => $title,
            'description' => $description,
            'type' => $type,
            'channel' => 'in_app',
            'send_status' => 'sent',
            'scheduled_at' => $now,
            'sent_at' => $now,
            'read' => false,
        ];

        try {
            $notification = AppNotification::query()->firstOrCreate(
                ['source' => $source],
                $payload,
            );

            return $notification->wasRecentlyCreated;
        } catch (UniqueConstraintViolationException) {
            return false;
        }
    }

    private function description(Maintenance $maintenance, string $status, string $today, ?int $window = null): string
    {
        $vehicle = $maintenance->vehicle;
        $customer = $vehicle?->customer?->name ?? 'Cliente';
        $label = $vehicle
            ? trim("{$vehicle->brand} {$vehicle->model} ({$vehicle->plate})")
            : 'veículo';
        $service = $maintenance->service_name;

        if ($status === 'Atrasada') {
            $due = $this->dueDate($maintenance);
            if ($due < $today) {
                return "{$service} do {$label} de {$customer} está atrasada (previsão {$this->formatBr($due)}).";
            }

            return "{$service} do {$label} de {$customer} atingiu a quilometragem prevista.";
        }

        $days = (int) Carbon::createFromFormat('Y-m-d', $today, 'America/Sao_Paulo')
            ->diffInDays(Carbon::createFromFormat('Y-m-d', $this->dueDate($maintenance), 'America/Sao_Paulo'));

        if ($days === 0) {
            return "{$service} do {$label} de {$customer} vence hoje.";
        }

        $labelDays = $days === 1 ? '1 dia' : "{$days} dias";
        $windowNote = $window ? " (janela de {$window} dias)" : '';

        return "{$service} do {$label} de {$customer} vence em {$labelDays}.{$windowNote}";
    }

    private function syncVehicle(int $vehicleId): void
    {
        $vehicle = Vehicle::query()->find($vehicleId);
        if (! $vehicle) {
            return;
        }

        $open = Maintenance::query()
            ->where('vehicle_id', $vehicleId)
            ->where('status', '!=', 'Concluída')
            ->orderBy('due_date')
            ->get();

        if ($open->isEmpty()) {
            $vehicle->update(['maintenance_status' => 'Concluída']);

            return;
        }

        $vehicle->update([
            'next_maintenance' => $this->dueDate($open->first()),
            'maintenance_status' => $open->contains(fn (Maintenance $item) => $item->status === 'Atrasada')
                ? 'Atrasada'
                : 'Próxima',
        ]);
    }

    private function dueDate(Maintenance $maintenance): string
    {
        $value = $maintenance->due_date;

        if ($value instanceof Carbon) {
            return $value->format('Y-m-d');
        }

        return substr((string) $value, 0, 10);
    }

    private function formatBr(string $date): string
    {
        return Carbon::createFromFormat('Y-m-d', $date, 'America/Sao_Paulo')->format('d/m/Y');
    }
}
