<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stylists', function (Blueprint $table) {
            $table->string('phone')->nullable();
        });

        Schema::table('whatsapp_templates', function (Blueprint $table) {
            $table->string('file_path')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('whatsapp_templates', function (Blueprint $table) {
            $table->dropColumn('file_path');
        });

        Schema::table('stylists', function (Blueprint $table) {
            $table->dropColumn('phone');
        });
    }
};
