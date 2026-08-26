<?php

namespace App\Http\Resources;

use App\Support\Dates;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MaintenanceResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'customerId' => $this->customer_id,
            'vehicleId' => $this->vehicle_id,
            'serviceName' => $this->service_name,
            'dueDate' => Dates::formatBr($this->due_date),
            'dueMileage' => $this->due_mileage,
            'status' => $this->currentStatus(),
        ];
    }

    /**
     * O status persistido pode ficar defasado, então uma manutenção em aberto
     * com vencimento no passado é reportada como atrasada.
     */
    private function currentStatus(): string
    {
        if ($this->status === 'Concluída') {
            return $this->status;
        }

        return Carbon::parse($this->due_date)->startOfDay()->lt(Carbon::today())
            ? 'Atrasada'
            : 'Próxima';
    }
}
