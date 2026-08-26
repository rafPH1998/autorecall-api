<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workshops', function (Blueprint $table) {
            $table->id();
            $table->string('name', 160);
            $table->string('document', 30);
            $table->string('phone', 30);
            $table->string('whatsapp', 30);
            $table->string('email', 160);
            $table->string('address');
            $table->boolean('maintenance_alerts')->default(true);
            $table->boolean('contact_reminders')->default(true);
            $table->boolean('weekly_report')->default(false);
            $table->unsignedInteger('default_reminder_days')->default(15);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workshops');
    }
};
