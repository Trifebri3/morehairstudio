<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('booking_code')->unique();
            $table->string('booking_token')->unique();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('outlet_id')->constrained()->cascadeOnDelete();
            $table->foreignId('stylist_id')->nullable()->constrained()->nullOnDelete();
            $table->date('booking_date');
            $table->string('status')->default('pending');
            $table->string('source')->default('website');
            $table->decimal('total_amount', 10, 2);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('net_amount', 10, 2);
            $table->string('promo_code')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('booking_code');
            $table->index('booking_token');
            $table->index('customer_id');
            $table->index('outlet_id');
            $table->index('stylist_id');
            $table->index('booking_date');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
