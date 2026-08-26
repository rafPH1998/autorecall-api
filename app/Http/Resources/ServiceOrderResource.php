<?php

namespace App\Http\Resources;

use App\Support\Dates;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ServiceOrderResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'number' => $this->number,
            'customerId' => $this->customer_id,
            'vehicleId' => $this->vehicle_id,
            'date' => Dates::formatBr($this->date),
            'mileage' => $this->mileage,
            'status' => $this->status,
            'notes' => $this->notes ?? '',
            'total' => (float) $this->total,
            'items' => ServiceOrderItemResource::collection($this->whenLoaded('items')),
        ];
    }
}
