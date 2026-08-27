<?php

namespace App\Support;

use App\Models\Customer;
use App\Models\Vehicle;

class WhatsApp
{
    public const DEFAULT_TEMPLATE = 'Olá, {nome}! Sentimos sua falta. Podemos agendar uma revisão para o seu {veiculo}?';

    public static function render(string $template, Customer $customer, ?Vehicle $vehicle = null, string $service = ''): string
    {
        $firstName = explode(' ', trim($customer->name))[0] ?: $customer->name;
        $vehicleLabel = $vehicle ? trim("{$vehicle->brand} {$vehicle->model}") : 'veículo';

        return strtr($template, [
            '{nome}' => $firstName,
            '{veiculo}' => $vehicleLabel !== '' ? $vehicleLabel : 'veículo',
            '{placa}' => $vehicle?->plate ?? '',
            '{servico}' => $service,
        ]);
    }

    public static function digits(?string $phone): string
    {
        return preg_replace('/\D+/', '', (string) $phone) ?? '';
    }

    public static function link(?string $phone, string $message): string
    {
        $digits = self::digits($phone);

        return 'https://wa.me/'.$digits.'?text='.rawurlencode($message);
    }
}
