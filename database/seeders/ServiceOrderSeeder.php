<?php

namespace Database\Seeders;

use App\Models\ServiceCatalog;
use App\Models\ServiceOrder;
use App\Models\Vehicle;
use Illuminate\Database\Seeder;

class ServiceOrderSeeder extends Seeder
{
    public function run(): void
    {
        $orders = [
            [
                'number' => 'OS-2026-0037',
                'plate' => 'GHI7J89',
                'date' => '2026-03-14',
                'mileage' => 65100,
                'status' => 'Finalizada',
                'notes' => '',
                'items' => [['service' => 'Troca da correia dentada', 'quantity' => 1]],
            ],
            [
                'number' => 'OS-2026-0042',
                'plate' => 'BRA2E19',
                'date' => '2026-08-18',
                'mileage' => 48500,
                'status' => 'Finalizada',
                'notes' => 'Cliente aguardou no local.',
                'items' => [['service' => 'Troca de óleo e filtro', 'quantity' => 1]],
            ],
            [
                'number' => 'OS-2026-0043',
                'plate' => 'DEF4G56',
                'date' => '2026-08-21',
                'mileage' => 31900,
                'status' => 'Em andamento',
                'notes' => 'Ruído ao frear.',
                'items' => [
                    ['service' => 'Revisão dos freios', 'quantity' => 1],
                    ['service' => 'Diagnóstico eletrônico', 'quantity' => 1],
                ],
            ],
            [
                'number' => 'OS-2026-0044',
                'plate' => 'OPQ3R45',
                'date' => '2026-08-24',
                'mileage' => 18400,
                'status' => 'Aberta',
                'notes' => 'Revisão preventiva.',
                'items' => [['service' => 'Alinhamento e balanceamento', 'quantity' => 1]],
            ],
        ];

        $services = ServiceCatalog::query()->get()->keyBy('name');

        foreach ($orders as $data) {
            $vehicle = Vehicle::query()->where('plate', $data['plate'])->firstOrFail();

            $items = collect($data['items'])->map(fn (array $item) => [
                'service_id' => $services[$item['service']]->id,
                'service_name' => $item['service'],
                'quantity' => $item['quantity'],
                'unit_price' => $services[$item['service']]->price,
            ]);

            $order = ServiceOrder::query()->updateOrCreate(['number' => $data['number']], [
                'customer_id' => $vehicle->customer_id,
                'vehicle_id' => $vehicle->id,
                'date' => $data['date'],
                'mileage' => $data['mileage'],
                'status' => $data['status'],
                'notes' => $data['notes'],
                'total' => $items->sum(fn (array $item) => $item['quantity'] * $item['unit_price']),
            ]);

            $order->items()->delete();
            $order->items()->createMany($items->all());
        }
    }
}
