<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('outlets', function (Blueprint $table) {
            $table->time('clock_out_start_time')->default('16:00:00');
            $table->time('clock_out_end_time')->default('18:00:00');
        });
    }

    public function down(): void
    {
        Schema::table('outlets', function (Blueprint $table) {
            $table->dropColumn(['clock_out_start_time', 'clock_out_end_time']);
        });
    }
};
