<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Vehicle;
use Illuminate\Database\Seeder;

class VehicleSeeder extends Seeder
{
    public function run(): void
    {
        $vehicles = [
            ['document' => '123.456.789-00', 'plate' => 'BRA2E19', 'brand' => 'Honda', 'model' => 'Civic', 'year' => 2020, 'mileage' => 48500, 'next_maintenance' => '2026-09-05', 'maintenance_status' => 'Próxima'],
            ['document' => '123.456.789-00', 'plate' => 'ABC1D23', 'brand' => 'Toyota', 'model' => 'Corolla', 'year' => 2018, 'mileage' => 82100, 'next_maintenance' => '2026-11-10', 'maintenance_status' => 'Próxima'],
            ['document' => '234.567.890-11', 'plate' => 'DEF4G56', 'brand' => 'Volkswagen', 'model' => 'T-Cross', 'year' => 2022, 'mileage' => 31900, 'next_maintenance' => '2026-08-20', 'maintenance_status' => 'Atrasada'],
            ['document' => '345.678.901-22', 'plate' => 'GHI7J89', 'brand' => 'Chevrolet', 'model' => 'Onix', 'year' => 2019, 'mileage' => 69700, 'next_maintenance' => '2026-08-12', 'maintenance_status' => 'Atrasada'],
            ['document' => '456.789.012-33', 'plate' => 'KLM0N12', 'brand' => 'Hyundai', 'model' => 'HB20', 'year' => 2021, 'mileage' => 42600, 'next_maintenance' => '2026-10-18', 'maintenance_status' => 'Próxima'],
            ['document' => '567.890.123-44', 'plate' => 'OPQ3R45', 'brand' => 'Jeep', 'model' => 'Renegade', 'year' => 2023, 'mileage' => 18400, 'next_maintenance' => '2026-12-01', 'maintenance_status' => 'Próxima'],
        ];

        foreach ($vehicles as $vehicle) {
            $customerId = Customer::query()->where('document', $vehicle['document'])->value('id');
            unset($vehicle['document']);

            Vehicle::query()->updateOrCreate(
                ['plate' => $vehicle['plate']],
                $vehicle + ['customer_id' => $customerId],
            );
        }
    }
}
