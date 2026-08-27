<?php

namespace App\Http\Resources;

use App\Models\Contact;
use App\Support\WhatsApp;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CampaignResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $payload = [
            'id' => $this->id,
            'name' => $this->name,
            'months' => $this->months,
            'message' => $this->message,
            'customersCount' => $this->customers_count ?? ($this->relationLoaded('customers') ? $this->customers->count() : 0),
            'createdAt' => optional($this->created_at)?->toIso8601String(),
        ];

        if ($this->relationLoaded('customers')) {
            $contacts = Contact::query()
                ->whereIn('id', $this->customers->pluck('pivot.contact_id')->filter())
                ->get()
                ->keyBy('id');

            $payload['customers'] = $this->customers->map(function ($customer) use ($contacts) {
                $contact = $contacts->get($customer->pivot->contact_id);
                $message = $contact?->message ?? $this->message;

                return [
                    'id' => $customer->id,
                    'name' => $customer->name,
                    'phone' => $customer->phone,
                    'whatsappUrl' => WhatsApp::link($customer->whatsapp ?: $customer->phone, $message),
                    'message' => $message,
                    'contactId' => $customer->pivot->contact_id,
                ];
            })->values();
        }

        return $payload;
    }
}
