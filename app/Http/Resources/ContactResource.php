<?php

namespace App\Http\Resources;

use App\Support\Dates;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ContactResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'customerId' => $this->customer_id,
            'date' => Dates::formatBr($this->date),
            'channel' => $this->channel,
            'message' => $this->message,
            'result' => $this->result,
        ];
    }
}
