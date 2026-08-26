<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->string('name', 160);
            $table->text('description');
            $table->decimal('price', 10, 2);
            $table->unsignedInteger('interval_months')->nullable();
            $table->unsignedInteger('interval_mileage')->nullable();
            $table->boolean('active')->default(true);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
