<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->string('channel', 20)->default('in_app')->after('type');
            $table->string('send_status', 20)->default('sent')->after('channel');
            $table->timestamp('scheduled_at')->nullable()->after('send_status');
            $table->timestamp('sent_at')->nullable()->after('scheduled_at');
        });

        Schema::table('workshops', function (Blueprint $table) {
            $table->text('whatsapp_template')->nullable()->after('default_reminder_days');
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->index('phone');
            $table->index('last_visit');
        });

        Schema::table('maintenances', function (Blueprint $table) {
            $table->index('due_date');
            $table->index('status');
        });

        Schema::create('campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('name', 160);
            $table->unsignedInteger('months')->default(6);
            $table->text('message');
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('campaign_contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained('campaigns')->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('contact_id')->nullable()->constrained('contacts')->nullOnDelete();
            $table->unique(['campaign_id', 'customer_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campaign_contacts');
        Schema::dropIfExists('campaigns');

        Schema::table('maintenances', function (Blueprint $table) {
            $table->dropIndex(['due_date']);
            $table->dropIndex(['status']);
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->dropIndex(['phone']);
            $table->dropIndex(['last_visit']);
        });

        Schema::table('workshops', function (Blueprint $table) {
            $table->dropColumn('whatsapp_template');
        });

        Schema::table('notifications', function (Blueprint $table) {
            $table->dropColumn(['channel', 'send_status', 'scheduled_at', 'sent_at']);
        });
    }
};
