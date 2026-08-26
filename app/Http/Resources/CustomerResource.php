<?php

namespace App\Http\Resources;

use App\Support\Dates;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'phone' => $this->phone,
            'whatsapp' => $this->whatsapp,
            'email' => $this->email,
            'document' => $this->document,
            'lastVisit' => $this->last_visit ? Dates::formatBr($this->last_visit) : 'Sem visitas',
        ];
    }
}
