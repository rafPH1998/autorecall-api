<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('service_orders')->cascadeOnDelete();
            $table->foreignId('service_id')->nullable()->constrained('services')->nullOnDelete();
            $table->string('service_name', 160);
            $table->unsignedInteger('quantity')->default(1);
            $table->decimal('unit_price', 10, 2);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_order_items');
    }
};
