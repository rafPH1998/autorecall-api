<?php

namespace App\Services;

use App\Models\AppNotification;
use App\Models\Customer;
use App\Models\Maintenance;
use App\Models\ServiceCatalog;
use App\Models\ServiceOrder;
use App\Models\Vehicle;
use App\Support\Dates;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class OrderService
{
    public function create(array $dto): ServiceOrder
    {
        if (empty($dto['items'])) {
            throw ValidationException::withMessages(['items' => 'Adicione ao menos um serviço.']);
        }

        $customer = Customer::find($dto['customerId']);
        $vehicle = Vehicle::find($dto['vehicleId']);

        if (! $customer) {
            throw ValidationException::withMessages(['customerId' => 'Cliente inválido.']);
        }
        if (! $vehicle || $vehicle->customer_id !== $customer->id) {
            throw ValidationException::withMessages(['vehicleId' => 'Veículo inválido para este cliente.']);
        }

        $items = [];
        foreach ($dto['items'] as $item) {
            $service = ServiceCatalog::find($item['serviceId'] ?? null);
            if (! $service) {
                throw ValidationException::withMessages(['items' => 'Serviço inválido.']);
            }
            $items[] = [
                'service_id' => $service->id,
                'service_name' => $service->name,
                'quantity' => $item['quantity'],
                'unit_price' => $item['unitPrice'] ?? $service->price,
            ];
        }

        $total = collect($items)->sum(fn ($item) => $item['quantity'] * $item['unit_price']);

        $order = DB::transaction(function () use ($customer, $vehicle, $dto, $items, $total) {
            $order = ServiceOrder::create([
                'number' => $this->nextNumber(),
                'customer_id' => $customer->id,
                'vehicle_id' => $vehicle->id,
                'date' => Dates::toSql(now()),
                'mileage' => $dto['mileage'],
                'status' => 'Aberta',
                'notes' => $dto['notes'] ?? '',
                'total' => $total,
            ]);

            foreach ($items as $item) {
                $order->items()->create($item);
            }

            return $order;
        });

        return $order->fresh('items');
    }

    public function finish(int $id): ServiceOrder
    {
        $order = ServiceOrder::with('items')->find($id);
        if (! $order) {
            throw new NotFoundHttpException('Ordem de serviço não encontrada.');
        }
        if (in_array($order->status, ['Finalizada', 'Cancelada'], true)) {
            throw ValidationException::withMessages(['status' => 'Esta ordem não pode ser finalizada.']);
        }

        DB::transaction(function () use ($order) {
            $order->update(['status' => 'Finalizada']);

            $customer = Customer::find($order->customer_id);
            $vehicle = Vehicle::find($order->vehicle_id);

            if ($customer) {
                $customer->update(['last_visit' => Dates::toSql($order->date)]);
            }
            if ($vehicle && $order->mileage > $vehicle->mileage) {
                $vehicle->update(['mileage' => $order->mileage]);
                $vehicle->refresh();
            }

            foreach ($order->items as $item) {
                if (! $item->service_id || ! $vehicle) {
                    continue;
                }
                $service = ServiceCatalog::find($item->service_id);
                if (! $service || (! $service->interval_months && ! $service->interval_mileage)) {
                    continue;
                }
                $this->scheduleMaintenance($vehicle->fresh(), $service, $order);
            }

            AppNotification::create([
                'title' => 'OS finalizada',
                'description' => "{$order->number} foi concluída.",
                'type' => 'system',
                'read' => false,
            ]);
        });

        return $order->fresh('items');
    }

    private function nextNumber(): string
    {
        $year = now()->year;
        $last = ServiceOrder::where('number', 'like', "OS-{$year}-%")
            ->orderByDesc('id')
            ->first();
        $seq = $last ? ((int) last(explode('-', $last->number))) + 1 : 1;

        return sprintf('OS-%d-%04d', $year, $seq);
    }

    private function scheduleMaintenance(Vehicle $vehicle, ServiceCatalog $service, ServiceOrder $order): void
    {
        $due = $service->interval_months
            ? Carbon::today()->addMonths($service->interval_months)
            : Carbon::today();
        $dueMileage = $vehicle->mileage + ($service->interval_mileage ?? 0);
        $status = $due->lt(Carbon::today()) ? 'Atrasada' : 'Próxima';
        $payload = [
            'customer_id' => $vehicle->customer_id,
            'vehicle_id' => $vehicle->id,
            'service_name' => $service->name,
            'due_date' => $due->format('Y-m-d'),
            'due_mileage' => $dueMileage,
            'status' => $status,
        ];

        Maintenance::updateOrCreate(
            ['vehicle_id' => $vehicle->id, 'service_name' => $service->name],
            $payload,
        );

        $vehicle->update([
            'next_maintenance' => $due->format('Y-m-d'),
            'maintenance_status' => $status,
        ]);

        AppNotification::create([
            'title' => $status === 'Atrasada' ? 'Manutenção atrasada' : 'Manutenção agendada',
            'description' => "{$service->name} do {$vehicle->brand} {$vehicle->model} ({$vehicle->plate}) após a {$order->number}.",
            'type' => 'maintenance',
            'read' => false,
        ]);
    }
}
