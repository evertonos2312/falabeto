<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $email = env('ADMIN_EMAIL');
        $password = env('ADMIN_PASSWORD');

        if (! $email || ! $password) {
            return;
        }

        $admin = Admin::firstOrCreate(
            ['email' => $email],
            [
                'name' => 'Owner',
                'password' => Hash::make($password),
            ]
        );

        $roles = ['owner', 'admin', 'support'];
        foreach ($roles as $roleName) {
            Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'admin']);
        }

        $admin->assignRole('owner');
    }
}
