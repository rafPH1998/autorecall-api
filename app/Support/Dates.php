<?php

namespace App\Support;

use Carbon\Carbon;

class Dates
{
    public static function formatBr(mixed $value): string
    {
        if (! $value) {
            return '';
        }

        $raw = $value instanceof Carbon
            ? $value->format('Y-m-d')
            : substr((string) $value, 0, 10);

        if (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $raw, $match)) {
            return "{$match[3]}/{$match[2]}/{$match[1]}";
        }

        try {
            return Carbon::parse($value)->format('d/m/Y');
        } catch (\Throwable) {
            return '';
        }
    }

    public static function toSql(mixed $value): ?string
    {
        if (! $value || $value === 'Sem visitas') {
            return null;
        }

        if ($value instanceof Carbon) {
            return $value->format('Y-m-d');
        }

        $value = trim((string) $value);

        if (preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', $value, $match)) {
            return "{$match[3]}-{$match[2]}-{$match[1]}";
        }

        if (preg_match('/^(\d{4}-\d{2}-\d{2})/', $value, $match)) {
            return $match[1];
        }

        return null;
    }

    public static function formatWhen(mixed $value): string
    {
        $date = $value instanceof Carbon ? $value : Carbon::parse($value);
        $time = $date->format('H:i');

        if ($date->isToday()) {
            return "Hoje, {$time}";
        }

        if ($date->isYesterday()) {
            return "Ontem, {$time}";
        }

        return $date->format('d/m/Y').', '.$time;
    }
}
