<?php

namespace App\Http\Controllers;

use App\Http\Resources\ServiceResource;
use App\Models\ServiceCatalog;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ServiceController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return ServiceResource::collection(ServiceCatalog::query()->orderBy('name')->get());
    }

    public function store(Request $request): ServiceResource
    {
        return new ServiceResource(ServiceCatalog::create($this->payload($request)));
    }

    public function update(Request $request, int $id): ServiceResource
    {
        $row = ServiceCatalog::find($id);
        if (! $row) {
            throw new NotFoundHttpException('Serviço não encontrado.');
        }
        $row->fill($this->payload($request))->save();

        return new ServiceResource($row);
    }

    private function payload(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string'],
            'description' => ['required', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'intervalMonths' => ['nullable', 'integer'],
            'intervalMileage' => ['nullable', 'integer'],
            'active' => ['nullable', 'boolean'],
        ]);

        return [
            'name' => trim($data['name']),
            'description' => trim($data['description']),
            'price' => $data['price'],
            'interval_months' => $data['intervalMonths'] ?? null,
            'interval_mileage' => $data['intervalMileage'] ?? null,
            'active' => $data['active'] ?? true,
        ];
    }
}
