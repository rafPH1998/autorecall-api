<?php

namespace App\Http\Resources;

use App\Models\User;
use App\Models\Workshop;
use App\Support\WhatsApp;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;

class SettingsResource extends JsonResource
{
    /**
     * @param  Collection<int, User>  $users
     */
    public function __construct(Workshop $workshop, private readonly Collection $users)
    {
        parent::__construct($workshop);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'workshop' => new WorkshopResource($this->resource),
            'workshopUsers' => UserResource::collection($this->users),
            'preferences' => [
                'maintenanceAlerts' => (bool) $this->maintenance_alerts,
                'contactReminders' => (bool) $this->contact_reminders,
                'weeklyReport' => (bool) $this->weekly_report,
                'defaultReminderDays' => $this->default_reminder_days,
                'whatsappTemplate' => $this->whatsapp_template ?: WhatsApp::DEFAULT_TEMPLATE,
            ],
        ];
    }
}
