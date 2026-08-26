<?php

namespace App\Http\Resources;

use App\Support\Dates;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VehicleResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'customerId' => $this->customer_id,
            'plate' => $this->plate,
            'brand' => $this->brand,
            'model' => $this->model,
            'year' => $this->year,
            'mileage' => $this->mileage,
            'nextMaintenance' => $this->next_maintenance ? Dates::formatBr($this->next_maintenance) : '',
            'maintenanceStatus' => $this->maintenance_status,
        ];
    }
}
