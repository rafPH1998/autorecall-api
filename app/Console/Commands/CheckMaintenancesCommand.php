<?php

namespace App\Console\Commands;

use App\Services\MaintenanceCheckService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Command;

#[Description('Classifica manutenções próximas e atrasadas e cria notificações sem duplicar.')]
class CheckMaintenancesCommand extends Command
{
    protected $signature = 'maintenances:check';

    public function handle(MaintenanceCheckService $service): int
    {
        $result = $service->run();

        $this->info(sprintf(
            'Manutenções: %d verificadas, %d atualizadas, %d notificações novas.',
            $result['checked'],
            $result['updated'],
            $result['notified'],
        ));

        return self::SUCCESS;
    }
}
