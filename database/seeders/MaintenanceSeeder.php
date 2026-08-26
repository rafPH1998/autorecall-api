<?php

namespace Database\Seeders;

use App\Models\Maintenance;
use App\Models\Vehicle;
use Illuminate\Database\Seeder;

class MaintenanceSeeder extends Seeder
{
    public function run(): void
    {
        $maintenances = [
            ['plate' => 'BRA2E19', 'service_name' => 'Troca de óleo e filtro', 'due_date' => '2026-09-05', 'due_mileage' => 58500, 'status' => 'Próxima'],
            ['plate' => 'DEF4G56', 'service_name' => 'Revisão dos freios', 'due_date' => '2026-08-20', 'due_mileage' => 30000, 'status' => 'Atrasada'],
            ['plate' => 'GHI7J89', 'service_name' => 'Troca de óleo e filtro', 'due_date' => '2026-08-12', 'due_mileage' => 69000, 'status' => 'Atrasada'],
            ['plate' => 'KLM0N12', 'service_name' => 'Alinhamento e balanceamento', 'due_date' => '2026-10-18', 'due_mileage' => 50000, 'status' => 'Próxima'],
            ['plate' => 'OPQ3R45', 'service_name' => 'Primeira revisão', 'due_date' => '2026-12-01', 'due_mileage' => 20000, 'status' => 'Próxima'],
        ];

        foreach ($maintenances as $data) {
            $vehicle = Vehicle::query()->where('plate', $data['plate'])->firstOrFail();
            unset($data['plate']);

            Maintenance::query()->updateOrCreate(
                ['vehicle_id' => $vehicle->id, 'service_name' => $data['service_name']],
                $data + ['customer_id' => $vehicle->customer_id],
            );
        }
    }
}
