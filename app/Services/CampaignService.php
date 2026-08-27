<?php

namespace App\Services;

use App\Models\AppNotification;
use App\Models\Campaign;
use App\Models\Contact;
use App\Models\Customer;
use App\Models\Vehicle;
use App\Models\Workshop;
use App\Support\Dates;
use App\Support\WhatsApp;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CampaignService
{
    /**
     * @return list<array<string, mixed>>
     */
    public function candidates(int $months = 6, ?string $q = null): array
    {
        $cutoff = Carbon::now('America/Sao_Paulo')->subMonths(max(1, $months))->toDateString();

        $customers = Customer::query()
            ->when($q, function ($query, $term) {
                $like = '%'.addcslashes($term, '%_\\').'%';
                $query->where(function ($inner) use ($like) {
                    $inner->where('name', 'like', $like)
                        ->orWhere('phone', 'like', $like)
                        ->orWhere('document', 'like', $like);
                });
            })
            ->where(function ($query) use ($cutoff) {
                $query->whereNull('last_visit')->orWhere('last_visit', '<', $cutoff);
            })
            ->orderBy('name')
            ->get();

        $template = Workshop::query()->value('whatsapp_template') ?: WhatsApp::DEFAULT_TEMPLATE;

        return $customers->map(function (Customer $customer) use ($template, $cutoff) {
            $vehicle = Vehicle::query()->where('customer_id', $customer->id)->orderBy('id')->first();
            $message = WhatsApp::render($template, $customer, $vehicle);

            return [
                'id' => $customer->id,
                'name' => $customer->name,
                'phone' => $customer->phone,
                'whatsapp' => $customer->whatsapp ?: $customer->phone,
                'lastVisit' => $customer->last_visit ? Dates::formatBr($customer->last_visit) : 'Sem visitas',
                'reason' => 'Sem retorno há mais de '.$months.' meses',
                'vehicle' => $vehicle ? trim("{$vehicle->brand} {$vehicle->model} ({$vehicle->plate})") : null,
                'message' => $message,
                'whatsappUrl' => WhatsApp::link($customer->whatsapp ?: $customer->phone, $message),
            ];
        })->all();
    }

    /**
     * @param  list<int>  $customerIds
     */
    public function create(string $name, int $months, array $customerIds, ?string $template = null): Campaign
    {
        $customerIds = array_values(array_unique(array_map('intval', $customerIds)));
        if ($customerIds === []) {
            throw ValidationException::withMessages(['customerIds' => 'Selecione ao menos um cliente.']);
        }

        $customers = Customer::query()->whereIn('id', $customerIds)->get();
        if ($customers->count() !== count($customerIds)) {
            throw ValidationException::withMessages(['customerIds' => 'Cliente inválido.']);
        }

        $template = $template ?: (Workshop::query()->value('whatsapp_template') ?: WhatsApp::DEFAULT_TEMPLATE);

        return DB::transaction(function () use ($name, $months, $customers, $template) {
            $campaign = Campaign::query()->create([
                'name' => $name !== '' ? $name : 'Campanha '.now()->format('d/m/Y H:i'),
                'months' => $months,
                'message' => $template,
            ]);

            foreach ($customers as $customer) {
                $vehicle = Vehicle::query()->where('customer_id', $customer->id)->orderBy('id')->first();
                $rendered = WhatsApp::render($template, $customer, $vehicle);
                $contact = Contact::query()->create([
                    'customer_id' => $customer->id,
                    'date' => Dates::toSql(now()),
                    'channel' => 'WhatsApp',
                    'message' => $rendered,
                    'result' => 'Aguardando resposta',
                ]);
                $campaign->customers()->attach($customer->id, ['contact_id' => $contact->id]);
            }

            AppNotification::query()->create([
                'title' => 'Campanha criada',
                'description' => "{$campaign->name}: {$customers->count()} cliente(s) para contato.",
                'type' => 'contact',
                'channel' => 'in_app',
                'send_status' => 'sent',
                'scheduled_at' => now(),
                'sent_at' => now(),
                'read' => false,
                'source' => 'campaign:'.$campaign->id,
            ]);

            return $campaign->load('customers');
        });
    }

    /**
     * @return Collection<int, Campaign>
     */
    public function list(): Collection
    {
        return Campaign::query()->withCount('customers')->orderByDesc('id')->get();
    }
}
