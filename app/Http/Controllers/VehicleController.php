<?php

namespace App\Http\Controllers;

use App\Http\Resources\VehicleResource;
use App\Models\Customer;
use App\Models\Vehicle;
use App\Support\Dates;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class VehicleController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection|array
    {
        $query = Vehicle::query()->orderBy('plate');
        $q = trim((string) $request->query('q', ''));
        if ($q !== '') {
            $like = '%'.addcslashes($q, '%_\\').'%';
            $query->where(function ($inner) use ($like) {
                $inner->where('plate', 'like', $like)
                    ->orWhere('brand', 'like', $like)
                    ->orWhere('model', 'like', $like);
            });
        }

        if ($request->filled('perPage')) {
            $perPage = min(100, max(1, (int) $request->query('perPage', 15)));
            $page = max(1, (int) $request->query('page', 1));
            $paginator = $query->paginate($perPage, ['*'], 'page', $page);

            return [
                'items' => VehicleResource::collection($paginator->getCollection()),
                'total' => $paginator->total(),
                'page' => $paginator->currentPage(),
                'perPage' => $paginator->perPage(),
            ];
        }

        return VehicleResource::collection($query->get());
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
            'plate' => ['required', 'string', Rule::unique('vehicles', 'plate')->ignore($request->route('id'))],
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
