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
            $table->string('customer_code')->unique();
            $table->string('phone')->unique();
            $table->string('whatsapp_phone')->nullable();
            $table->string('name');
            $table->string('email')->nullable();
            $table->date('birth_date')->nullable();
            $table->string('gender')->nullable();
            $table->timestamps();

            $table->index('phone');
            $table->index('customer_code');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
