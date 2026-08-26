<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ServiceOrderItemResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'serviceId' => $this->service_id ?? 0,
            'serviceName' => $this->service_name,
            'quantity' => $this->quantity,
            'unitPrice' => (float) $this->unit_price,
        ];
    }
}
