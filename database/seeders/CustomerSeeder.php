<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        $customers = [
            ['name' => 'Ana Souza', 'phone' => '(11) 98888-1010', 'whatsapp' => '5511988881010', 'email' => 'ana@email.com', 'document' => '123.456.789-00', 'last_visit' => '2026-08-18'],
            ['name' => 'Bruno Lima', 'phone' => '(11) 97777-2020', 'whatsapp' => '5511977772020', 'email' => 'bruno@email.com', 'document' => '234.567.890-11', 'last_visit' => '2026-07-02'],
            ['name' => 'Carla Mendes', 'phone' => '(11) 96666-3030', 'whatsapp' => '5511966663030', 'email' => 'carla@email.com', 'document' => '345.678.901-22', 'last_visit' => '2026-03-14'],
            ['name' => 'Diego Alves', 'phone' => '(11) 95555-4040', 'whatsapp' => '5511955554040', 'email' => 'diego@email.com', 'document' => '456.789.012-33', 'last_visit' => '2026-01-28'],
            ['name' => 'Elisa Ramos', 'phone' => '(11) 94444-5050', 'whatsapp' => '5511944445050', 'email' => 'elisa@email.com', 'document' => '567.890.123-44', 'last_visit' => '2026-08-09'],
            ['name' => 'Fábio Costa', 'phone' => '(11) 93333-6060', 'whatsapp' => '5511933336060', 'email' => 'fabio@email.com', 'document' => '678.901.234-55', 'last_visit' => '2026-05-21'],
        ];

        foreach ($customers as $customer) {
            Customer::query()->updateOrCreate(['document' => $customer['document']], $customer);
        }
    }
}
