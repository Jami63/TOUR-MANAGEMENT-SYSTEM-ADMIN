<?php

use App\Models\User;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('admin:create {email} {password} {name=System Admin}', function (string $email, string $password, string $name) {
    $user = User::where('email', $email)->first();

    if ($user) {
        $user->update([
            'role' => 'super_admin',
            'email_verified_at' => now(),
        ]);
        $this->info("User [{$email}] was updated to Super Admin (email verified)!");
        return;
    }

    User::create([
        'full_name' => $name,
        'email' => $email,
        'password' => Hash::make($password),
        'role' => 'super_admin',
        'email_verified_at' => now(),
    ]);

    $this->info("Super Admin account successfully created for [{$email}]!");
})->purpose('Create or upgrade an account to verified Super Admin');

Artisan::command('admin:promote {email}', function (string $email) {
    $user = User::where('email', $email)->first();

    if (! $user) {
        $this->error("No user found with email [{$email}].");
        return;
    }

    $user->update([
        'role' => 'super_admin',
        'email_verified_at' => $user->email_verified_at ?? now(),
    ]);

    $this->info("User '{$user->full_name}' ({$email}) is now a Super Admin!");
})->purpose('Promote an existing user to Super Admin');

Artisan::command('admin:list', function () {
    $users = User::all(['id', 'full_name', 'email', 'role', 'email_verified_at']);
    $this->table(['ID', 'Name', 'Email', 'Role', 'Email Verified'], $users->toArray());
})->purpose('List all users and their assigned roles');
