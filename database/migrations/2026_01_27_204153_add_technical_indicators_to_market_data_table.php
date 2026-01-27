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
        Schema::table('market_data', function (Blueprint $table) {
            $table->decimal('ma25', 10, 4)->nullable()->after('close');
            $table->decimal('ma75', 10, 4)->nullable()->after('ma25');
            $table->decimal('ma200', 10, 4)->nullable()->after('ma75');
            $table->decimal('rsi', 10, 4)->nullable()->after('ma200');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('market_data', function (Blueprint $table) {
            //
        });
    }
};
