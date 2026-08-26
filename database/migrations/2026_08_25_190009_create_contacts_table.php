<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->string('channel', 20);
            $table->text('message');
            $table->string('result', 160);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contacts');
    }
};
