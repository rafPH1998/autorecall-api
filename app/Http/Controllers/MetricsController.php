<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Vehicle;
use App\Models\Workshop;
use App\Services\MetricsService;
use App\Support\Dates;
use App\Support\WhatsApp;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class MetricsController extends Controller
{
    public function __construct(private readonly MetricsService $metrics) {}

    public function dashboard(): array
    {
        return $this->metrics->dashboard();
    }

    public function reports(Request $request): array
    {
        return $this->metrics->reports(
            $request->query('from'),
            $request->query('to'),
        );
    }

    public function whatsappPreview(Request $request): array
    {
        $data = $request->validate([
            'customerId' => ['required', 'integer'],
            'message' => ['nullable', 'string'],
        ]);

        $customer = Customer::find($data['customerId']);
        if (! $customer) {
            throw new NotFoundHttpException('Cliente não encontrado.');
        }

        $vehicle = Vehicle::query()->where('customer_id', $customer->id)->orderBy('id')->first();
        $template = $data['message']
            ?? (Workshop::query()->value('whatsapp_template') ?: WhatsApp::DEFAULT_TEMPLATE);
        $rendered = WhatsApp::render($template, $customer, $vehicle);

        return [
            'message' => $rendered,
            'whatsappUrl' => WhatsApp::link($customer->whatsapp ?: $customer->phone, $rendered),
            'date' => Dates::formatBr(now()),
        ];
    }
}
