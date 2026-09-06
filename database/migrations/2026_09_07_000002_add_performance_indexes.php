<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Indexes for bookings table
        Schema::table('bookings', function (Blueprint $table) {
            $table->index(['outlet_id', 'status'], 'idx_bookings_outlet_status');
            $table->index(['stylist_id', 'booking_date', 'status'], 'idx_bookings_stylist_date_status');
        });

        // 2. Indexes for booking_items table
        Schema::table('booking_items', function (Blueprint $table) {
            $table->index(['start_time', 'end_time'], 'idx_booking_items_times');
            $table->index('service_id', 'idx_booking_items_service_id');
        });

        // 3. Indexes for stylist_schedules table
        if (Schema::hasTable('stylist_schedules')) {
            Schema::table('stylist_schedules', function (Blueprint $table) {
                $table->index(['stylist_id', 'day_of_week', 'is_working'], 'idx_schedules_stylist_day_working');
            });
        }

        // 4. Indexes for outlet_services table
        if (Schema::hasTable('outlet_services')) {
            Schema::table('outlet_services', function (Blueprint $table) {
                $table->index(['outlet_id', 'is_active'], 'idx_outlet_services_active');
            });
        }

        // 5. Indexes for visit_logs table
        if (Schema::hasTable('visit_logs')) {
            Schema::table('visit_logs', function (Blueprint $table) {
                $table->index('page_url', 'idx_visit_logs_page_url');
                $table->index('created_at', 'idx_visit_logs_created_at');
            });
        }
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropIndex('idx_bookings_outlet_status');
            $table->dropIndex('idx_bookings_stylist_date_status');
        });

        Schema::table('booking_items', function (Blueprint $table) {
            $table->dropIndex('idx_booking_items_times');
            $table->dropIndex('idx_booking_items_service_id');
        });

        if (Schema::hasTable('stylist_schedules')) {
            Schema::table('stylist_schedules', function (Blueprint $table) {
                $table->dropIndex('idx_schedules_stylist_day_working');
            });
        }

        if (Schema::hasTable('outlet_services')) {
            Schema::table('outlet_services', function (Blueprint $table) {
                $table->dropIndex('idx_outlet_services_active');
            });
        }

        if (Schema::hasTable('visit_logs')) {
            Schema::table('visit_logs', function (Blueprint $table) {
                $table->dropIndex('idx_visit_logs_page_url');
                $table->dropIndex('idx_visit_logs_created_at');
            });
        }
    }
};
