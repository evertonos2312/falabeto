<?php

use App\Models\Admin;
use App\Support\Settings;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

it('allows owner to access product settings page', function () {
    $role = Role::query()->firstOrCreate([
        'name' => 'owner',
        'guard_name' => 'admin',
    ]);

    $admin = Admin::query()->create([
        'name' => 'Owner',
        'email' => 'owner@example.com',
        'password' => bcrypt('secret'),
    ]);

    $admin->assignRole($role);

    actingAs($admin, 'admin');

    get('/admin/settings')->assertOk();
});

it('updates a setting value', function () {
    $admin = Admin::query()->create([
        'name' => 'Owner',
        'email' => 'owner2@example.com',
        'password' => bcrypt('secret'),
    ]);

    Settings::set('commercial.trial_days_default', 21, 'int', 'commercial', $admin);

    expect(Settings::get('commercial.trial_days_default'))->toBe(21);
});
