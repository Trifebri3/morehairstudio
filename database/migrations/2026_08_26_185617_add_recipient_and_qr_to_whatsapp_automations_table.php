<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('whatsapp_automations', function (Blueprint $table) {
            $table->string('recipient')->default('customer')->after('template_name'); // 'customer' or 'stylist'
            $table->boolean('include_qr')->default(false)->after('recipient');
        });
    }

    public function down(): void
    {
        Schema::table('whatsapp_automations', function (Blueprint $table) {
            $table->dropColumn(['recipient', 'include_qr']);
        });
    }
};
