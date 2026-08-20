<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('whatsapp_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->nullable()->constrained()->nullOnDelete();
            $table->string('message_id')->nullable()->index();
            $table->string('phone')->index();
            $table->string('type')->default('text'); // text or template
            $table->string('template_name')->nullable();
            $table->text('body');
            $table->string('status')->default('sent'); // pending, sent, delivered, read, failed
            $table->json('response_payload')->nullable();
            $table->json('raw_payload')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('whatsapp_messages');
    }
};
