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
        Schema::create('order_distributions', function (Blueprint $table) {
            $table->id();
            $table->string('symbol')->index();
            $table->json('order_book_data'); // グラフ用の生データをJSONで保持
            $table->decimal('long_ratio', 5, 2); // 買い注文の割合(%)
            $table->decimal('short_ratio', 5, 2); // 売り注文の割合(%)
            $table->timestamp('snapshot_at')->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_distributions');
    }
};
