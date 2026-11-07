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
        Schema::table('payments', function (Blueprint $table) {
            $table->foreignId('tour_id')->nullable()->after('id')->constrained('tours')->cascadeOnDelete();
            $table->foreignId('tour_member_id')->nullable()->after('tour_id')->constrained('tour_members')->cascadeOnDelete();
        });

        // Make share_id nullable so advance payments don't require an existing expense share
        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE payments DROP FOREIGN KEY payments_share_id_foreign');
            DB::statement('ALTER TABLE payments MODIFY share_id BIGINT UNSIGNED NULL');
            DB::statement('ALTER TABLE payments ADD CONSTRAINT payments_share_id_foreign FOREIGN KEY (share_id) REFERENCES expense_shares(id) ON DELETE SET NULL');

            // Backfill existing payments with tour_id and tour_member_id
            DB::statement('
                UPDATE payments p
                JOIN expense_shares s ON p.share_id = s.id
                JOIN expenses e ON s.expense_id = e.id
                SET p.tour_id = e.tour_id, p.tour_member_id = s.tour_member_id
                WHERE p.tour_id IS NULL
            ');
        } else {
            Schema::table('payments', function (Blueprint $table) {
                $table->unsignedBigInteger('share_id')->nullable()->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['tour_id']);
            $table->dropForeign(['tour_member_id']);
            $table->dropColumn(['tour_id', 'tour_member_id']);
        });
    }
};
