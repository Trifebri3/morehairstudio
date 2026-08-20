<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
      public function up(): void
      {
          Schema::table('outlets', function (Blueprint $table) {
              $table->integer('booking_lead_time_hours')->default(1);
          });
      }

      public function down(): void
      {
          Schema::table('outlets', function (Blueprint $table) {
              $table->dropColumn('booking_lead_time_hours');
          });
      }
};
