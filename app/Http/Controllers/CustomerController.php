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
    public function index(Request $request): AnonymousResourceCollection|array
    {
        $query = Customer::query()->orderBy('name');
        $q = trim((string) $request->query('q', ''));
        if ($q !== '') {
            $like = '%'.addcslashes($q, '%_\\').'%';
            $query->where(function ($inner) use ($like) {
                $inner->where('name', 'like', $like)
                    ->orWhere('phone', 'like', $like)
                    ->orWhere('whatsapp', 'like', $like)
                    ->orWhere('email', 'like', $like)
                    ->orWhere('document', 'like', $like);
            });
        }

        if ($request->filled('perPage')) {
            $perPage = min(100, max(1, (int) $request->query('perPage', 15)));
            $page = max(1, (int) $request->query('page', 1));
            $paginator = $query->paginate($perPage, ['*'], 'page', $page);

            return [
                'items' => CustomerResource::collection($paginator->getCollection()),
                'total' => $paginator->total(),
                'page' => $paginator->currentPage(),
                'perPage' => $paginator->perPage(),
            ];
        }

        return CustomerResource::collection($query->get());
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
