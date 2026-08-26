<?php

namespace App\Http\Controllers;

use App\Http\Resources\VehicleResource;
use App\Models\Customer;
use App\Models\Vehicle;
use App\Support\Dates;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class VehicleController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return VehicleResource::collection(Vehicle::query()->orderBy('plate')->get());
    }

    public function show(int $id): VehicleResource
    {
        $row = Vehicle::find($id);
        if (! $row) {
            throw new NotFoundHttpException('Veículo não encontrado.');
        }

        return new VehicleResource($row);
    }

    public function store(Request $request): VehicleResource
    {
        return new VehicleResource(Vehicle::create($this->payload($request)));
    }

    public function update(Request $request, int $id): VehicleResource
    {
        $row = Vehicle::find($id);
        if (! $row) {
            throw new NotFoundHttpException('Veículo não encontrado.');
        }
        $row->fill($this->payload($request))->save();

        return new VehicleResource($row);
    }

    private function payload(Request $request): array
    {
        $data = $request->validate([
            'customerId' => ['required', 'integer'],
            'plate' => ['required', 'string'],
            'brand' => ['required', 'string'],
            'model' => ['required', 'string'],
            'year' => ['required', 'integer'],
            'mileage' => ['required', 'integer'],
            'nextMaintenance' => ['nullable', 'string'],
            'maintenanceStatus' => ['nullable', 'string'],
        ]);

        if (! Customer::where('id', $data['customerId'])->exists()) {
            throw ValidationException::withMessages(['customerId' => 'Cliente inválido.']);
        }

        return [
            'customer_id' => $data['customerId'],
            'plate' => strtoupper(trim($data['plate'])),
            'brand' => trim($data['brand']),
            'model' => trim($data['model']),
            'year' => $data['year'],
            'mileage' => $data['mileage'],
            'next_maintenance' => Dates::toSql($data['nextMaintenance'] ?? null),
            'maintenance_status' => $data['maintenanceStatus'] ?? 'Próxima',
        ];
    }
}
