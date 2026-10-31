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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('status', 20);
            $table->decimal('total', 10, 2);
            // Código tPag da NFC-e
            $table->string('payment_method', 2);
            $table->decimal('amount_received', 10, 2);
            $table->decimal('change', 10, 2)->default(0);
            $table->string('consumer_cpf', 11)->nullable();
            $table->timestamp('closed_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
