<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('bookings') && !Schema::hasColumn('bookings', 'referral_source')) {
            Schema::table('bookings', function (Blueprint $table) {
                $table->string('referral_source')->nullable()->after('source');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('bookings') && Schema::hasColumn('bookings', 'referral_source')) {
            Schema::table('bookings', function (Blueprint $table) {
                $table->dropColumn('referral_source');
            });
        }
    }
};

