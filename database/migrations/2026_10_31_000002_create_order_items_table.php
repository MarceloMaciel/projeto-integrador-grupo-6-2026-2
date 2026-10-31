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
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            // O que já foi vendido não pode ser apagado do cardápio, só desativado.
            $table->foreignId('product_variation_id')->constrained('product_variations')->restrictOnDelete();
            // Cópia dos dados no momento da venda: mudar o cardápio não altera a nota.
            $table->string('code', 60);
            $table->string('description', 120);
            $table->string('ncm', 8);
            $table->string('cfop', 4);
            $table->string('csosn', 3);
            $table->string('cest', 7)->nullable();
            $table->string('unit', 6);
            $table->unsignedInteger('quantity');
            $table->decimal('unit_price', 10, 2);
            $table->decimal('total', 10, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
