<?php

namespace App\Http\Resources;

use App\Support\Dates;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'date' => Dates::formatWhen($this->created_at),
            'type' => $this->type,
            'read' => (bool) $this->read,
        ];
    }
}
