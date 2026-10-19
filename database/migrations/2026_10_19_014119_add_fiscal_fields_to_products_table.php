<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Os padrões são os do perfil "prepared" (config/fiscal.php), que cobre
     * quase todo o cardápio: um banco já existente recebe valores válidos
     * sem precisar rodar o seeder de novo.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('ncm', 8)->default('21069090')->after('description');
            $table->string('cfop', 4)->default('5101')->after('ncm');
            $table->string('csosn', 3)->default('102')->after('cfop');
            $table->string('cest', 7)->nullable()->after('csosn');
            $table->string('unit', 6)->default('UN')->after('cest');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['ncm', 'cfop', 'csosn', 'cest', 'unit']);
        });
    }
};
