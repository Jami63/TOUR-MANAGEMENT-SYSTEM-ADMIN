<?php

namespace Database\Seeders;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\ExpenseShare;
use App\Models\Payment;
use App\Models\Tour;
use App\Models\TourDestination;
use App\Models\TourMember;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the database with Super Admin, Tour Managers, and sample tour data.
     * Only 2 user roles exist: super_admin and manager. Tour members do NOT have user accounts.
     */
    public function run(): void
    {
        // 1. Super Admin Account (Strictly Read-Only Monitor)
        $superAdmin = User::updateOrCreate(
            ['email' => 'admin@safarflow.test'],
            [
                'full_name' => 'Super Administrator',
                'phone' => '01700000000',
                'password' => Hash::make('password'),
                'role' => 'super_admin',
                'notifications_enabled' => true,
            ]
        );

        // 2. Tour Managers (Manage tours, members, expenses, payments, refunds)
        $managerNusrat = User::updateOrCreate(
            ['email' => 'manager@safarflow.test'],
            [
                'full_name' => 'Nusrat Jahan',
                'phone' => '01712000001',
                'password' => Hash::make('password'),
                'role' => 'manager',
                'notifications_enabled' => true,
            ]
        );

        $managerTariq = User::updateOrCreate(
            ['email' => 'tariq@safarflow.test'],
            [
                'full_name' => 'Tariqul Islam',
                'phone' => '01712000002',
                'password' => Hash::make('password'),
                'role' => 'manager',
                'notifications_enabled' => true,
            ]
        );

        // 3. Default Expense Categories
        $categories = [
            'Transport',
            'Accommodation',
            'Food & Dining',
            'Sightseeing & Entry',
            'Activities & Recreation',
            'Miscellaneous',
        ];

        $catModels = [];
        foreach ($categories as $catName) {
            $catModels[$catName] = ExpenseCategory::firstOrCreate(
                ['category_name' => $catName],
                ['created_by' => $managerNusrat->id]
            );
        }

        // 4. Sample Live Tour created by Tour Manager Nusrat Jahan (Requirement 2 & 3)
        // Tour: Cox's Bazar, 5 Members, ৳200,000 Budget
        $tour = Tour::create([
            'created_by' => $managerNusrat->id,
            'tour_name' => "Cox's Bazar Beach Expedition",
            'start_location' => 'Dhaka',
            'start_date' => now()->subDays(2)->toDateString(),
            'end_date' => now()->addDays(3)->toDateString(),
            'estimated_budget' => 200000.00,
            'description' => '5-day leisure and adventure tour to Cox\'s Bazar, Inani, and Marine Drive.',
            'status' => 'ongoing',
        ]);

        // Waypoint Destinations
        TourDestination::create(['tour_id' => $tour->id, 'destination_name' => "Cox's Bazar", 'visit_order' => 1]);
        TourDestination::create(['tour_id' => $tour->id, 'destination_name' => 'Inani Beach', 'visit_order' => 2]);
        TourDestination::create(['tour_id' => $tour->id, 'destination_name' => 'Himchari', 'visit_order' => 3]);

        // Tour Members:
        // Member #1 is the Tour Manager himself (has user account)
        $m1 = TourMember::create([
            'tour_id' => $tour->id,
            'user_id' => $managerNusrat->id,
            'name' => $managerNusrat->full_name,
            'phone' => $managerNusrat->phone,
            'email' => $managerNusrat->email,
            'tour_role' => 'manager',
            'status' => 'joined',
            'joined_at' => now()->subDays(2),
        ]);

        // Members 2 to 5 are friends without application accounts!
        $m2 = TourMember::create([
            'tour_id' => $tour->id,
            'name' => 'Tanvir Ahmed',
            'phone' => '01811223344',
            'email' => 'tanvir@gmail.com',
            'tour_role' => 'member',
            'status' => 'joined',
            'joined_at' => now()->subDays(2),
        ]);

        $m3 = TourMember::create([
            'tour_id' => $tour->id,
            'name' => 'Rafi Hossain',
            'phone' => '01811556677',
            'email' => 'rafi@gmail.com',
            'tour_role' => 'member',
            'status' => 'joined',
            'joined_at' => now()->subDays(2),
        ]);

        $m4 = TourMember::create([
            'tour_id' => $tour->id,
            'name' => 'Mehedi Hasan',
            'phone' => '01811889900',
            'email' => 'mehedi@gmail.com',
            'tour_role' => 'member',
            'status' => 'joined',
            'joined_at' => now()->subDays(2),
        ]);

        $m5 = TourMember::create([
            'tour_id' => $tour->id,
            'name' => 'Shamim Reza',
            'phone' => '01811334455',
            'email' => 'shamim@gmail.com',
            'tour_role' => 'member',
            'status' => 'joined',
            'joined_at' => now()->subDays(2),
        ]);

        $members = [$m1, $m2, $m3, $m4, $m5];

        // Expense 1: Bus Fare = ৳40,000 (divided equally: ৳8,000 to each of the 5 members)
        $exp1 = Expense::create([
            'tour_id' => $tour->id,
            'category_id' => $catModels['Transport']->id,
            'added_by' => $managerNusrat->id,
            'expense_name' => 'AC Scania Bus Round Trip',
            'total_amount' => 40000.00,
            'expense_date' => now()->subDays(2)->toDateString(),
            'description' => 'Dhaka - Cox\'s Bazar - Dhaka AC bus tickets for all 5 members.',
        ]);

        $share1_1 = ExpenseShare::create(['expense_id' => $exp1->id, 'tour_member_id' => $m1->id, 'share_amount' => 8000.00]);
        $share1_2 = ExpenseShare::create(['expense_id' => $exp1->id, 'tour_member_id' => $m2->id, 'share_amount' => 8000.00]);
        $share1_3 = ExpenseShare::create(['expense_id' => $exp1->id, 'tour_member_id' => $m3->id, 'share_amount' => 8000.00]);
        $share1_4 = ExpenseShare::create(['expense_id' => $exp1->id, 'tour_member_id' => $m4->id, 'share_amount' => 8000.00]);
        $share1_5 = ExpenseShare::create(['expense_id' => $exp1->id, 'tour_member_id' => $m5->id, 'share_amount' => 8000.00]);

        // Manual Payments for Expense 1:
        // Member 1 paid full ৳8,000 via bKash -> Status: Paid
        Payment::create([
            'share_id' => $share1_1->id,
            'amount' => 8000.00,
            'method' => 'bkash',
            'payment_date' => now()->subDays(2)->toDateString(),
            'transaction_id' => 'BK78291048',
            'notes' => 'Full bus fare paid via bKash',
            'status' => 'verified',
            'received_by' => $managerNusrat->id,
            'submitted_at' => now()->subDays(2),
        ]);

        // Member 2 paid partial ৳4,000 via Cash -> Status: Partial
        Payment::create([
            'share_id' => $share1_2->id,
            'amount' => 4000.00,
            'method' => 'cash',
            'payment_date' => now()->subDays(2)->toDateString(),
            'notes' => 'Advance cash payment handed at bus counter',
            'status' => 'verified',
            'received_by' => $managerNusrat->id,
            'submitted_at' => now()->subDays(2),
        ]);

        // Member 3 paid full ৳8,000 via Nagad -> Status: Paid
        Payment::create([
            'share_id' => $share1_3->id,
            'amount' => 8000.00,
            'method' => 'nagad',
            'payment_date' => now()->subDays(1)->toDateString(),
            'transaction_id' => 'NG55192847',
            'notes' => 'Received via Nagad',
            'status' => 'verified',
            'received_by' => $managerNusrat->id,
            'submitted_at' => now()->subDays(1),
        ]);

        // Members 4 & 5 paid 0 -> Status: Pending

        // Expense 2: Hotel Sea Crown = ৳50,000 (divided equally: ৳10,000 to each of the 5 members)
        $exp2 = Expense::create([
            'tour_id' => $tour->id,
            'category_id' => $catModels['Accommodation']->id,
            'added_by' => $managerNusrat->id,
            'expense_name' => 'Hotel Sea Crown 2-Night Stay',
            'total_amount' => 50000.00,
            'expense_date' => now()->subDays(1)->toDateString(),
            'description' => '2 Deluxe rooms with sea view for 5 members.',
        ]);

        foreach ($members as $m) {
            ExpenseShare::create([
                'expense_id' => $exp2->id,
                'tour_member_id' => $m->id,
                'share_amount' => 10000.00,
            ]);
        }

        // Update tour aggregate fields
        $totalExpenses = 90000.00;
        $totalCollected = 20000.00;
        $tour->update([
            'total_actual_expense' => $totalExpenses,
            'total_collected' => $totalCollected,
        ]);
    }
}
