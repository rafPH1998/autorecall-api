<?php

namespace App\Http\Controllers;

use App\Http\Resources\ContactResource;
use App\Models\AppNotification;
use App\Models\Contact;
use App\Models\Customer;
use App\Support\Dates;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ContactController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return ContactResource::collection(Contact::query()->with('customer')->orderByDesc('id')->get());
    }

    public function store(Request $request): ContactResource
    {
        $data = $request->validate([
            'customerId' => ['required', 'integer'],
            'date' => ['nullable', 'string'],
            'channel' => ['nullable', 'string'],
            'message' => ['required', 'string'],
            'result' => ['nullable', 'string'],
        ]);

        $customer = Customer::find($data['customerId']);
        if (! $customer) {
            throw new NotFoundHttpException('Cliente não encontrado.');
        }

        $contact = Contact::create([
            'customer_id' => $customer->id,
            'date' => Dates::toSql($data['date'] ?? null) ?? Dates::toSql(now()),
            'channel' => $data['channel'] ?? 'WhatsApp',
            'message' => $data['message'],
            'result' => $data['result'] ?? 'Aguardando resposta',
        ]);

        AppNotification::create([
            'title' => 'Novo contato registrado',
            'description' => "Contato com {$customer->name} via {$contact->channel}.",
            'type' => 'contact',
            'read' => false,
        ]);

        return new ContactResource($contact->load('customer'));
    }
}
