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
        Schema::create('refunds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tour_member_id')->unique()->constrained('tour_members')->cascadeOnDelete();
            $table->decimal('total_paid', 12, 2)->default(0.00);
            $table->decimal('final_share', 12, 2)->default(0.00);
            $table->decimal('adjustment_total', 12, 2)->default(0.00);
            $table->decimal('refund_amount', 12, 2)->default(0.00);
            $table->enum('method', ['bKash', 'Nagad', 'Cash'])->nullable();
            $table->date('refund_date')->nullable();
            $table->enum('status', ['pending', 'processing', 'refunded'])->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('refunds');
    }
};
