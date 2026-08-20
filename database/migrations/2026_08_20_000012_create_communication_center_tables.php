<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. WhatsApp Configuration Settings
        Schema::create('whatsapp_configurations', function (Blueprint $table) {
            $table->id();
            $table->string('provider')->unique(); // cloud_api, fonnte
            $table->text('config')->nullable(); // Encrypted JSON configuration
            $table->boolean('is_active')->default(false);
            $table->timestamps();
        });

        // 2. WhatsApp Automations
        Schema::create('whatsapp_automations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('event_type')->index(); // BOOKING_CREATED, BOOKING_CONFIRMED, BOOKING_COMPLETED, etc.
            $table->string('template_name')->index();
            $table->integer('delay_minutes')->default(0); // 0 for instant, or negative/positive offsets
            $table->boolean('is_active')->default(true);
            $table->foreignId('outlet_id')->nullable()->constrained()->nullOnDelete(); // scoped
            $table->timestamps();
        });

        // 3. WhatsApp Campaigns
        Schema::create('whatsapp_campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('template_name');
            $table->string('recipient_type'); // individual, segment, filtered, all
            $table->json('filters')->nullable(); // Target audience rules
            $table->timestamp('scheduled_at')->nullable();
            $table->string('status')->default('PENDING'); // PENDING, PROCESSING, COMPLETED, FAILED
            $table->timestamps();
        });

        // 4. WhatsApp Campaign Recipients Snapshot
        Schema::create('whatsapp_campaign_recipients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained('whatsapp_campaigns')->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->string('status')->default('PENDING'); // PENDING, SENT, DELIVERED, FAILED, SKIPPED
            $table->text('error_message')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
        });

        // 5. Email Configuration settings
        Schema::create('email_configurations', function (Blueprint $table) {
            $table->id();
            $table->string('host')->nullable();
            $table->integer('port')->nullable();
            $table->string('username')->nullable();
            $table->text('password')->nullable(); // Encrypted
            $table->string('encryption')->nullable(); // ssl, tls, null
            $table->string('from_address')->nullable();
            $table->string('from_name')->nullable();
            $table->boolean('is_active')->default(false);
            $table->timestamps();
        });

        // 6. Email Templates
        Schema::create('email_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('subject');
            $table->text('body');
            $table->json('variables')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 7. Email Logs
        Schema::create('email_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->string('recipient')->index();
            $table->string('subject');
            $table->string('status')->default('SENT'); // SENT, FAILED
            $table->text('error_message')->nullable();
            $table->timestamps();
        });

        // 8. Booking Tickets
        Schema::create('booking_tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->string('ticket_code')->unique();
            $table->string('passcode');
            $table->string('qr_code_path')->nullable();
            $table->string('pdf_path')->nullable();
            $table->timestamps();
        });

        // 9. Extend Customers for marketing opt-ins
        Schema::table('customers', function (Blueprint $table) {
            $table->boolean('whatsapp_marketing_opt_in')->default(true);
            $table->boolean('email_marketing_opt_in')->default(true);
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn(['whatsapp_marketing_opt_in', 'email_marketing_opt_in']);
        });

        Schema::dropIfExists('booking_tickets');
        Schema::dropIfExists('email_logs');
        Schema::dropIfExists('email_templates');
        Schema::dropIfExists('email_configurations');
        Schema::dropIfExists('whatsapp_campaign_recipients');
        Schema::dropIfExists('whatsapp_campaigns');
        Schema::dropIfExists('whatsapp_automations');
        Schema::dropIfExists('whatsapp_configurations');
    }
};
