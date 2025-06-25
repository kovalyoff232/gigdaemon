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
        Schema::table('time_entries', function (Blueprint $table) {
            // Добавляем столбец client_id ПОСЛЕ project_id
            $table->foreignId('client_id')->after('project_id')->constrained()->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('time_entries', function (Blueprint $table) {
            // Важно правильно удалить столбец и внешний ключ
            $table->dropForeign(['client_id']);
            $table->dropColumn('client_id');
        });
    }
};