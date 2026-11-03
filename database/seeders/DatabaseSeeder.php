<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the database with Super Admin and registered Tour Managers and Tour Members.
     */
    public function run(): void
    {
        // 1. Super Admin Account (Logs into the Admin Panel)
        User::updateOrCreate(
            ['email' => 'admin@safarflow.test'],
            [
                'full_name' => 'Super Administrator',
                'phone' => '01700000000',
                'password' => Hash::make('password'),
                'role' => 'super_admin',
                'notifications_enabled' => true,
            ]
        );

        // 2. People registering as Tour Managers
        User::updateOrCreate(
            ['email' => 'manager@safarflow.test'],
            [
                'full_name' => 'Nusrat Jahan',
                'phone' => '01712000001',
                'password' => Hash::make('password'),
                'role' => 'manager',
            ]
        );

        User::updateOrCreate(
            ['email' => 'tariq@safarflow.test'],
            [
                'full_name' => 'Tariqul Islam',
                'phone' => '01712000002',
                'password' => Hash::make('password'),
                'role' => 'manager',
            ]
        );

        // 3. People registering as Tour Members
        User::updateOrCreate(
            ['email' => 'rahim@safarflow.test'],
            [
                'full_name' => 'Rahim Uddin',
                'phone' => '01712000003',
                'password' => Hash::make('password'),
                'role' => 'member',
            ]
        );

        User::updateOrCreate(
            ['email' => 'karim@safarflow.test'],
            [
                'full_name' => 'Karim Ahmed',
                'phone' => '01712000004',
                'password' => Hash::make('password'),
                'role' => 'member',
            ]
        );

        User::updateOrCreate(
            ['email' => 'hasan@safarflow.test'],
            [
                'full_name' => 'Hasan Mahmud',
                'phone' => '01712000005',
                'password' => Hash::make('password'),
                'role' => 'member',
            ]
        );

        User::updateOrCreate(
            ['email' => 'sakib@safarflow.test'],
            [
                'full_name' => 'Sakib Al Hasan',
                'phone' => '01712000006',
                'password' => Hash::make('password'),
                'role' => 'member',
            ]
        );
    }
}
