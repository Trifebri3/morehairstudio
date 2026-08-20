<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stylist_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stylist_id')->constrained()->cascadeOnDelete();
            $table->integer('day_of_week'); // 0 = Sunday, 1 = Monday, ..., 6 = Saturday
            $table->time('start_time');
            $table->time('end_time');
            $table->time('break_start')->nullable();
            $table->time('break_end')->nullable();
            $table->boolean('is_working')->default(true);
            $table->timestamps();

            $table->unique(['stylist_id', 'day_of_week']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stylist_schedules');
    }
};
