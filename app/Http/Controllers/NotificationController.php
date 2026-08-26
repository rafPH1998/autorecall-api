<?php

namespace App\Http\Controllers;

use App\Http\Resources\NotificationResource;
use App\Models\AppNotification;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class NotificationController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return $this->list();
    }

    public function markRead(int $id): AnonymousResourceCollection
    {
        $row = AppNotification::find($id);
        if (! $row) {
            throw new NotFoundHttpException('Notificação não encontrada.');
        }
        $row->update(['read' => true]);

        return $this->list();
    }

    public function markAllRead(): AnonymousResourceCollection
    {
        AppNotification::query()->where('read', false)->update(['read' => true]);

        return $this->list();
    }

    private function list(): AnonymousResourceCollection
    {
        return NotificationResource::collection(
            AppNotification::query()->orderByDesc('id')->get(),
        );
    }
}
