<?php

namespace App\Http\Controllers;

use App\Http\Resources\ServiceOrderResource;
use App\Models\ServiceOrder;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class OrderController extends Controller
{
    public function __construct(private readonly OrderService $orders) {}

    public function index(): AnonymousResourceCollection
    {
        return ServiceOrderResource::collection(
            ServiceOrder::with('items')->orderByDesc('id')->get(),
        );
    }

    public function show(int $id): ServiceOrderResource
    {
        $row = ServiceOrder::with('items')->find($id);
        if (! $row) {
            throw new NotFoundHttpException('Ordem de serviço não encontrada.');
        }

        return new ServiceOrderResource($row);
    }

    public function store(Request $request): ServiceOrderResource
    {
        $data = $request->validate([
            'customerId' => ['required', 'integer'],
            'vehicleId' => ['required', 'integer'],
            'mileage' => ['required', 'integer'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.serviceId' => ['required', 'integer'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unitPrice' => ['required', 'numeric', 'min:0'],
        ]);

        return new ServiceOrderResource($this->orders->create($data));
    }

    public function finish(int $id): ServiceOrderResource
    {
        return new ServiceOrderResource($this->orders->finish($id));
    }
}
