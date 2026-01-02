<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create super admin user
        $superAdmin = User::create([
            'name' => 'Super Admin',
            'email' => 'admin@elkcms.local',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        $superAdmin->assignRole('super-admin');

        $this->command->info('Super admin created: admin@elkcms.local / password');

        // Create regular admin user
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'user@elkcms.local',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        $admin->assignRole('admin');

        $this->command->info('Admin user created: user@elkcms.local / password');
    }
}
