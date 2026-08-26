<?php

namespace App\Http\Controllers;

use App\Http\Resources\CustomerResource;
use App\Models\Customer;
use App\Support\Dates;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CustomerController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return CustomerResource::collection(Customer::query()->orderBy('name')->get());
    }

    public function show(int $id): CustomerResource
    {
        $row = Customer::find($id);
        if (! $row) {
            throw new NotFoundHttpException('Cliente não encontrado.');
        }

        return new CustomerResource($row);
    }

    public function store(Request $request): CustomerResource
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'min:2'],
            'phone' => ['required', 'string'],
            'whatsapp' => ['nullable', 'string'],
            'email' => ['nullable', 'email'],
            'document' => ['required', 'string'],
            'lastVisit' => ['nullable', 'string'],
        ]);

        $row = Customer::create([
            'name' => trim($data['name']),
            'phone' => trim($data['phone']),
            'whatsapp' => trim($data['whatsapp'] ?? ''),
            'email' => trim($data['email'] ?? ''),
            'document' => trim($data['document']),
            'last_visit' => Dates::toSql($data['lastVisit'] ?? null),
        ]);

        return new CustomerResource($row);
    }

    public function update(Request $request, int $id): CustomerResource
    {
        $row = Customer::find($id);
        if (! $row) {
            throw new NotFoundHttpException('Cliente não encontrado.');
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'min:2'],
            'phone' => ['required', 'string'],
            'whatsapp' => ['nullable', 'string'],
            'email' => ['nullable', 'email'],
            'document' => ['required', 'string'],
            'lastVisit' => ['nullable', 'string'],
        ]);

        $row->fill([
            'name' => trim($data['name']),
            'phone' => trim($data['phone']),
            'whatsapp' => trim($data['whatsapp'] ?? ''),
            'email' => trim($data['email'] ?? ''),
            'document' => trim($data['document']),
            'last_visit' => Dates::toSql($data['lastVisit'] ?? null) ?? $row->last_visit,
        ])->save();

        return new CustomerResource($row);
    }
}
