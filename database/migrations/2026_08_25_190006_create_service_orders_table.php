<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_orders', function (Blueprint $table) {
            $table->id();
            $table->string('number', 30)->unique();
            $table->foreignId('customer_id')->constrained()->restrictOnDelete();
            $table->foreignId('vehicle_id')->constrained()->restrictOnDelete();
            $table->date('date');
            $table->unsignedInteger('mileage')->default(0);
            $table->string('status', 20)->default('Aberta');
            $table->text('notes')->nullable();
            $table->decimal('total', 10, 2)->default(0);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_orders');
    }
};
