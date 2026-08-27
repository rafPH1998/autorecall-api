<?php

namespace App\Http\Resources;

use App\Support\Dates;
use App\Support\WhatsApp;
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
            'whatsappUrl' => $this->customer
                ? WhatsApp::link($this->customer->whatsapp ?: $this->customer->phone, $this->message)
                : null,
        ];
    }
}
