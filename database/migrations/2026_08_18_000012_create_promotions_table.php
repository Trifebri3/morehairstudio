<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promotions', function (Blueprint $table) {
            $table->id();
            $table->string('promo_code')->unique();
            $table->string('discount_type'); // 'percentage' or 'fixed'
            $table->decimal('discount_value', 10, 2);
            $table->decimal('minimum_transaction', 10, 2)->default(0);
            $table->decimal('maximum_discount', 10, 2)->nullable();
            $table->timestamp('start_at')->nullable();
            $table->timestamp('end_at')->nullable();
            $table->integer('usage_limit')->nullable();
            $table->integer('usage_count')->default(0);
            $table->integer('customer_limit')->nullable();
            $table->json('outlet_scope')->nullable(); // scoped to outlet IDs
            $table->json('service_scope')->nullable(); // scoped to service IDs
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promotions');
    }
};
