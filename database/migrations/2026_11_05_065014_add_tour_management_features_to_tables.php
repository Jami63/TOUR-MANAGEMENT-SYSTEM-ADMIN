<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Tours table additions
        Schema::table('tours', function (Blueprint $table) {
            $table->string('finish_type', 30)->nullable()->after('status'); // 'normal', 'emergency'
            $table->timestamp('finished_at')->nullable()->after('finish_type');
            $table->text('emergency_reason')->nullable()->after('finished_at');
            $table->text('settlement_notes')->nullable()->after('emergency_reason');
            $table->decimal('total_collected', 12, 2)->default(0.00)->after('settlement_notes');
            $table->decimal('total_actual_expense', 12, 2)->default(0.00)->after('total_collected');
            $table->decimal('total_refund_amount', 12, 2)->default(0.00)->after('total_actual_expense');
        });

        // 2. Tour Members table - allow members without registered user accounts
        Schema::table('tour_members', function (Blueprint $table) {
            $table->string('name')->nullable()->after('user_id');
            $table->string('phone')->nullable()->after('name');
            $table->string('email')->nullable()->after('phone');
        });

        // Make user_id nullable & adjust column types across databases (MySQL & SQLite)
        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE tour_members MODIFY user_id BIGINT UNSIGNED NULL');
            DB::statement('ALTER TABLE payments MODIFY method VARCHAR(50) NOT NULL');
            DB::statement('ALTER TABLE refunds MODIFY method VARCHAR(50) NULL');
        } else {
            Schema::table('tour_members', function (Blueprint $table) {
                $table->unsignedBigInteger('user_id')->nullable()->change();
            });
            Schema::table('payments', function (Blueprint $table) {
                $table->string('method', 50)->change();
            });
            Schema::table('refunds', function (Blueprint $table) {
                $table->string('method', 50)->nullable()->change();
            });
        }

        // 3. Payments table - allow flexible methods and manual logging metadata
        Schema::table('payments', function (Blueprint $table) {
            $table->date('payment_date')->nullable()->after('amount');
            $table->text('notes')->nullable()->after('transaction_id');
            $table->foreignId('received_by')->nullable()->after('status')->constrained('users')->nullOnDelete();
        });

        // 4. Refunds table - add tour_id for direct tour queries and flexible method
        Schema::table('refunds', function (Blueprint $table) {
            $table->foreignId('tour_id')->nullable()->after('id')->constrained('tours')->cascadeOnDelete();
            $table->text('notes')->nullable()->after('refund_date');
            $table->foreignId('refunded_by')->nullable()->after('status')->constrained('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('refunds', function (Blueprint $table) {
            $table->dropForeign(['tour_id']);
            $table->dropForeign(['refunded_by']);
            $table->dropColumn(['tour_id', 'notes', 'refunded_by']);
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['received_by']);
            $table->dropColumn(['payment_date', 'notes', 'received_by']);
        });

        Schema::table('tour_members', function (Blueprint $table) {
            $table->dropColumn(['name', 'phone', 'email']);
        });

        Schema::table('tours', function (Blueprint $table) {
            $table->dropColumn([
                'finish_type',
                'finished_at',
                'emergency_reason',
                'settlement_notes',
                'total_collected',
                'total_actual_expense',
                'total_refund_amount',
            ]);
        });
    }
};
