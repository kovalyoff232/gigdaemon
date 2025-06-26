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
        Schema::table('clients', function (Blueprint $table) {
            $table->decimal('default_rate', 10, 2)->nullable()->after('phone');
            // ВОТ ОНО. ВАЛЮТА КЛИЕНТА.
            $table->string('default_currency', 3)->default('RUB')->after('default_rate');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn('default_rate');
            $table->dropColumn('default_currency');
        });
    }
};