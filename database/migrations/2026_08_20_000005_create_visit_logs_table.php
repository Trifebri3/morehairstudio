<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visit_logs', function (Blueprint $table) {
            $table->id();
            $table->string('ip_address');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('page_url');
            $table->string('search_query')->nullable();
            $table->string('location')->nullable();
            $table->string('device');
            $table->string('gender')->nullable();
            $table->integer('age')->nullable();
            $table->string('browser')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visit_logs');
    }
};
