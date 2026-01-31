<?php

use App\Models\Admin;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

it('admin can login and access admin panel', function () {
    $admin = Admin::create([
        'name' => 'Owner',
        'email' => 'admin@example.com',
        'password' => Hash::make('secret123'),
    ]);

    $role = Role::firstOrCreate(['name' => 'owner', 'guard_name' => 'admin']);
    $admin->assignRole($role);

    $this->actingAs($admin, 'admin');
    $this->get('/admin')->assertOk();
});

it('admin can access plans resource', function () {
    $admin = Admin::create([
        'name' => 'Owner',
        'email' => 'admin2@example.com',
        'password' => Hash::make('secret123'),
    ]);

    $role = Role::firstOrCreate(['name' => 'owner', 'guard_name' => 'admin']);
    $admin->assignRole($role);

    $this->actingAs($admin, 'admin');
    $this->get('/admin/plans')->assertOk();
});
