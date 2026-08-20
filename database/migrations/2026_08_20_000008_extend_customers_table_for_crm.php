<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->text('address')->nullable();
            $table->string('status')->default('active'); // active, inactive, lost
            $table->json('tags')->nullable();
            $table->text('notes')->nullable();
            $table->integer('loyalty_points')->default(0);
            $table->string('first_acquisition_source')->default('Website');
            $table->string('latest_acquisition_source')->default('Website');
            $table->json('acquisition_metadata')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn([
                'address', 'status', 'tags', 'notes', 'loyalty_points',
                'first_acquisition_source', 'latest_acquisition_source', 'acquisition_metadata'
            ]);
        });
    }
};
