<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('name', 160);
            $table->string('phone', 30);
            $table->string('whatsapp', 30)->default('');
            $table->string('email', 160)->default('');
            $table->string('document', 30);
            $table->date('last_visit')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
