<?php

namespace Database\Seeders;

use App\Models\ServiceCatalog;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    public function run(): void
    {
        $services = [
            ['name' => 'Troca de óleo e filtro', 'description' => 'Óleo do motor e filtro de óleo', 'price' => 280, 'interval_months' => 6, 'interval_mileage' => 10000, 'active' => true],
            ['name' => 'Alinhamento e balanceamento', 'description' => 'Alinhamento completo e balanceamento das rodas', 'price' => 180, 'interval_months' => 12, 'interval_mileage' => 15000, 'active' => true],
            ['name' => 'Revisão dos freios', 'description' => 'Inspeção de pastilhas, discos e fluido', 'price' => 350, 'interval_months' => 12, 'interval_mileage' => 20000, 'active' => true],
            ['name' => 'Higienização do ar-condicionado', 'description' => 'Limpeza do sistema e troca do filtro', 'price' => 160, 'interval_months' => 12, 'interval_mileage' => null, 'active' => true],
            ['name' => 'Diagnóstico eletrônico', 'description' => 'Leitura de módulos e falhas', 'price' => 120, 'interval_months' => null, 'interval_mileage' => null, 'active' => true],
            ['name' => 'Troca da correia dentada', 'description' => 'Kit de correia e tensionador', 'price' => 980, 'interval_months' => 48, 'interval_mileage' => 60000, 'active' => false],
        ];

        foreach ($services as $service) {
            ServiceCatalog::query()->updateOrCreate(['name' => $service['name']], $service);
        }
    }
}
