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
        Schema::create('analysis_results', function (Blueprint $table) {
            $table->id();
            $table->string('symbol');
            $table->decimal('prediction_percentage', 5, 2); // 例: 70.00 (%)
            $table->enum('prediction_type', ['up', 'down']); // 上昇か下落か
            $table->json('basis_data'); // なぜその結果になったかの根拠（RSI値など）を記録
            $table->boolean('is_correct')->nullable(); // 後から答え合わせ（勝率計算用）
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('analysis_results');
    }
};
