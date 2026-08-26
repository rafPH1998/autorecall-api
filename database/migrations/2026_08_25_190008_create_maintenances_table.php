<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('maintenances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('vehicle_id')->constrained()->cascadeOnDelete();
            $table->string('service_name', 160);
            $table->date('due_date');
            $table->unsignedInteger('due_mileage')->default(0);
            $table->string('status', 20)->default('Próxima');

            // O agendamento é reaproveitado a cada OS finalizada do mesmo serviço.
            $table->unique(['vehicle_id', 'service_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('maintenances');
    }
};
