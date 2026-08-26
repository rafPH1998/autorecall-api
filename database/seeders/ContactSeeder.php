<?php

namespace Database\Seeders;

use App\Models\Contact;
use App\Models\Customer;
use Illuminate\Database\Seeder;

class ContactSeeder extends Seeder
{
    public function run(): void
    {
        if (Contact::query()->exists()) {
            return;
        }

        $contacts = [
            ['document' => '234.567.890-11', 'date' => '2026-08-22', 'channel' => 'WhatsApp', 'message' => 'Olá Bruno, sua revisão está atrasada.', 'result' => 'Aguardando resposta'],
            ['document' => '345.678.901-22', 'date' => '2026-08-18', 'channel' => 'Telefone', 'message' => 'Contato sobre troca de óleo.', 'result' => 'Agendado para 28/08'],
            ['document' => '456.789.012-33', 'date' => '2026-08-10', 'channel' => 'WhatsApp', 'message' => 'Sentimos sua falta na oficina.', 'result' => 'Mensagem visualizada'],
        ];

        foreach ($contacts as $data) {
            $customerId = Customer::query()->where('document', $data['document'])->value('id');
            unset($data['document']);

            Contact::query()->create($data + ['customer_id' => $customerId]);
        }
    }
}
