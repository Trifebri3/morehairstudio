<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('outlets', function (Blueprint $table) {
            $table->time('attendance_start_time')->nullable()->default('07:00:00');
            $table->time('attendance_end_time')->nullable()->default('09:00:00');
        });
    }

    public function down(): void
    {
        Schema::table('outlets', function (Blueprint $table) {
            $table->dropColumn(['attendance_start_time', 'attendance_end_time']);
        });
    }
};
