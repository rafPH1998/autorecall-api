<?php

namespace App\Services;

use App\Models\Contact;
use App\Models\Customer;
use App\Models\Maintenance;
use App\Models\ServiceOrder;
use App\Models\Vehicle;
use App\Support\Dates;

class MetricsService
{
    /**
     * @return array<string, mixed>
     */
    public function dashboard(): array
    {
        $overdue = Maintenance::query()->where('status', 'Atrasada')->count();
        $upcoming = Maintenance::query()->where('status', 'Próxima')->count();
        $openOrders = ServiceOrder::query()
            ->whereNotIn('status', ['Finalizada', 'Cancelada'])
            ->count();

        return [
            'customers' => Customer::query()->count(),
            'vehicles' => Vehicle::query()->count(),
            'upcomingMaintenances' => $upcoming,
            'overdueMaintenances' => $overdue,
            'openOrders' => $openOrders,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function reports(?string $from, ?string $to): array
    {
        $start = Dates::toSql($from);
        $end = Dates::toSql($to);

        $contacts = Contact::query()
            ->when($start, fn ($q) => $q->whereDate('date', '>=', $start))
            ->when($end, fn ($q) => $q->whereDate('date', '<=', $end))
            ->get();

        $orders = ServiceOrder::query()
            ->when($start, fn ($q) => $q->whereDate('date', '>=', $start))
            ->when($end, fn ($q) => $q->whereDate('date', '<=', $end))
            ->get();

        $contacted = $contacts->pluck('customer_id')->unique()->count();
        $returns = $contacts->filter(function (Contact $item) {
            return ! in_array($item->result, ['Aguardando resposta', 'Mensagem visualizada'], true);
        })->count();
        $appointments = $contacts->filter(fn (Contact $item) => str_contains(mb_strtolower($item->result), 'agendado'))->count();
        $revenue = $orders->where('status', 'Finalizada')->sum('total');

        return [
            'from' => $start ? Dates::formatBr($start) : null,
            'to' => $end ? Dates::formatBr($end) : null,
            'contactedCustomers' => $contacted,
            'returns' => $returns,
            'appointments' => $appointments,
            'revenue' => (float) $revenue,
            'conversion' => $contacted ? (int) round(($returns / $contacted) * 100) : 0,
        ];
    }
}
