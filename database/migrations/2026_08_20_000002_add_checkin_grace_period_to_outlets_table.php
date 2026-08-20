<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('outlets', function (Blueprint $table) {
            $table->boolean('checkin_grace_period_active')->default(true);
            $table->integer('checkin_grace_period_minutes')->default(15);
        });
    }

    public function down(): void
    {
        Schema::table('outlets', function (Blueprint $table) {
            $table->dropColumn(['checkin_grace_period_active', 'checkin_grace_period_minutes']);
        });
    }
};
