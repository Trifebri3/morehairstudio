<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('outlet_id')->constrained()->cascadeOnDelete();
            $table->foreignId('stylist_id')->nullable()->constrained()->nullOnDelete();
            $table->integer('rating');
            $table->text('review')->nullable();
            $table->string('status')->default('approved');
            $table->timestamps();

            $table->index('outlet_id');
            $table->index('stylist_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
