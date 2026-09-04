<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('stylists', function (Blueprint $table) {
            $table->string('instagram')->nullable()->after('specialization');
            $table->string('tiktok')->nullable()->after('instagram');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stylists', function (Blueprint $table) {
            $table->dropColumn(['instagram', 'tiktok']);
        });
    }
};
