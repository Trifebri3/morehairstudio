<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->timestamp('checked_in_at')->nullable()->after('booking_date');
            $table->timestamp('service_start_at')->nullable()->after('checked_in_at');
            $table->timestamp('service_end_at')->nullable()->after('service_start_at');
            $table->unsignedSmallInteger('service_duration_minutes')->nullable()->after('service_end_at');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn([
                'checked_in_at',
                'service_start_at',
                'service_end_at',
                'service_duration_minutes',
            ]);
        });
    }
};
