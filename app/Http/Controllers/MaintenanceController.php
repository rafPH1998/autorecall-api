<?php

namespace App\Http\Controllers;

use App\Http\Resources\MaintenanceResource;
use App\Models\Maintenance;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class MaintenanceController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return MaintenanceResource::collection(
            Maintenance::query()->orderBy('due_date')->get(),
        );
    }
}
