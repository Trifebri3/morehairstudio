<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->string('event_type'); // registered, booking_created, booking_completed, transaction_created, loyalty_earned, review_submitted, etc.
            $table->timestamp('event_date')->useCurrent();
            $table->unsignedBigInteger('outlet_id')->nullable();
            $table->string('source')->default('system');
            $table->string('reference_type')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index('customer_id');
            $table->index('event_type');
            $table->index('event_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_activities');
    }
};
