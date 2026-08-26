<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            WorkshopSeeder::class,
            UserSeeder::class,
            ServiceSeeder::class,
            CustomerSeeder::class,
            VehicleSeeder::class,
            ServiceOrderSeeder::class,
            MaintenanceSeeder::class,
            ContactSeeder::class,
            NotificationSeeder::class,
        ]);
    }
}
