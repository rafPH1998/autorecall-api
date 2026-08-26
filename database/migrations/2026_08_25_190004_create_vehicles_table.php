<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->string('plate', 10)->unique();
            $table->string('brand', 80);
            $table->string('model', 80);
            $table->unsignedSmallInteger('year');
            $table->unsignedInteger('mileage')->default(0);
            $table->date('next_maintenance')->nullable();
            $table->string('maintenance_status', 20)->default('Próxima');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
