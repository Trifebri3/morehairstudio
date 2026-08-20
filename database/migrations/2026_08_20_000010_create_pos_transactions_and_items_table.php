<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pos_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_number')->unique();
            $table->foreignId('outlet_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('booking_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedBigInteger('staff_id')->nullable(); // stylist id
            $table->decimal('subtotal', 10, 2);
            $table->decimal('discount', 10, 2)->default(0);
            $table->decimal('tax', 10, 2)->default(0);
            $table->decimal('grand_total', 10, 2);
            $table->string('payment_status')->default('unpaid'); // unpaid, paid, refunded
            $table->string('status')->default('pending'); // pending, completed, refunded, cancelled
            $table->string('payment_method')->nullable(); // cash, qris, transfer, ewallet
            $table->string('transaction_reference')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index('transaction_number');
            $table->index('outlet_id');
            $table->index('customer_id');
            $table->index('booking_id');
            $table->index('status');
        });

        Schema::create('pos_transaction_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaction_id')->constrained('pos_transactions')->cascadeOnDelete();
            $table->string('item_type'); // service, product
            $table->unsignedBigInteger('item_id'); // services.id or products.id
            $table->integer('quantity')->default(1);
            $table->decimal('unit_price', 10, 2);
            $table->decimal('discount', 10, 2)->default(0);
            $table->decimal('subtotal', 10, 2);
            $table->timestamps();
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->unsignedBigInteger('booking_id')->nullable()->change();
            $table->foreignId('transaction_id')->nullable()->constrained('pos_transactions')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['transaction_id']);
            $table->dropColumn('transaction_id');
        });

        Schema::dropIfExists('pos_transaction_items');
        Schema::dropIfExists('pos_transactions');
    }
};
