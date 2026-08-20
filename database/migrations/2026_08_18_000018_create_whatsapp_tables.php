<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Drop old table if exists to recreate with clean production schema
        Schema::dropIfExists('whatsapp_messages');

        Schema::create('whatsapp_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->string('provider'); // meta, fonnte
            $table->string('direction')->default('OUTBOUND'); // OUTBOUND, INBOUND
            $table->string('message_type')->default('text'); // text, template, media, interactive
            $table->string('recipient')->index();
            $table->string('external_message_id')->nullable()->index();
            $table->string('template_name')->nullable();
            $table->text('body')->nullable();
            $table->json('payload')->nullable();
            $table->string('status')->default('QUEUED'); // QUEUED, SENT, DELIVERED, READ, FAILED
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();
        });

        Schema::create('whatsapp_webhook_events', function (Blueprint $table) {
            $table->id();
            $table->string('provider');
            $table->string('event_id')->index();
            $table->string('event_type');
            $table->json('payload')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('whatsapp_templates', function (Blueprint $table) {
            $table->id();
            $table->string('template_name')->unique();
            $table->string('language')->default('id');
            $table->text('body');
            $table->json('variables')->nullable(); // array of placeholder bindings
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('whatsapp_templates');
        Schema::dropIfExists('whatsapp_webhook_events');
        Schema::dropIfExists('whatsapp_messages');
    }
};
