<?php

namespace App\Http\Controllers;

use App\Http\Resources\SettingsResource;
use App\Services\SettingsService;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function __construct(private readonly SettingsService $settings) {}

    public function show(): SettingsResource
    {
        return $this->resource();
    }

    public function update(Request $request): SettingsResource
    {
        $data = $request->validate([
            'workshop' => ['required', 'array'],
            'workshop.name' => ['required', 'string'],
            'workshop.document' => ['required', 'string'],
            'workshop.phone' => ['required', 'string'],
            'workshop.whatsapp' => ['required', 'string'],
            'workshop.email' => ['required', 'email'],
            'workshop.address' => ['required', 'string'],
            'users' => ['required', 'array'],
            'users.*.id' => ['nullable', 'integer'],
            'users.*.name' => ['required', 'string'],
            'users.*.email' => ['required', 'email'],
            'users.*.role' => ['required', 'string'],
            'users.*.active' => ['required', 'boolean'],
            'users.*.password' => ['nullable', 'string'],
            'preferences' => ['required', 'array'],
            'preferences.maintenanceAlerts' => ['required', 'boolean'],
            'preferences.contactReminders' => ['required', 'boolean'],
            'preferences.weeklyReport' => ['required', 'boolean'],
            'preferences.defaultReminderDays' => ['required', 'integer'],
        ]);

        $this->settings->save($data, $request->user()->id);

        return $this->resource();
    }

    private function resource(): SettingsResource
    {
        return new SettingsResource(
            $this->settings->workshop(),
            $this->settings->users(),
        );
    }
}
