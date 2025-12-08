<?php

use App\Enums\RoleEnum;
use App\Models\Organization;
use App\Models\User;
use Spatie\Permission\Models\Role;

test('guests cannot access billing page', function () {
    $this->get(route('billing.index'))
        ->assertRedirect(route('login'));
});

test('admin users can access billing page', function () {
    $organization = Organization::factory()->create();
    $adminRole = Role::firstOrCreate(['name' => RoleEnum::Admin->value]);
    $user = User::factory()->create(['organization_id' => $organization->id]);
    $user->assignRole($adminRole);

    $this->actingAs($user)
        ->get(route('billing.index'))
        ->assertOk()
        ->assertSee('Billing');
});

test('non-admin users cannot access billing page', function () {
    $organization = Organization::factory()->create();
    $userRole = Role::firstOrCreate(['name' => RoleEnum::User->value]);
    $user = User::factory()->create(['organization_id' => $organization->id]);
    $user->assignRole($userRole);

    $this->actingAs($user)
        ->get(route('billing.index'))
        ->assertForbidden();
});

test('viewer users cannot access billing page', function () {
    $organization = Organization::factory()->create();
    $viewerRole = Role::firstOrCreate(['name' => RoleEnum::Viewer->value]);
    $user = User::factory()->create(['organization_id' => $organization->id]);
    $user->assignRole($viewerRole);

    $this->actingAs($user)
        ->get(route('billing.index'))
        ->assertForbidden();
});

test('billing page shows current plan', function () {
    $organization = Organization::factory()->create();
    $adminRole = Role::firstOrCreate(['name' => RoleEnum::Admin->value]);
    $user = User::factory()->create(['organization_id' => $organization->id]);
    $user->assignRole($adminRole);

    $this->actingAs($user)
        ->get(route('billing.index'))
        ->assertOk()
        ->assertSee('Current Plan')
        ->assertSee('Free');
});

test('billing page shows usage statistics', function () {
    $organization = Organization::factory()->create();
    $adminRole = Role::firstOrCreate(['name' => RoleEnum::Admin->value]);
    $user = User::factory()->create(['organization_id' => $organization->id]);
    $user->assignRole($adminRole);

    $this->actingAs($user)
        ->get(route('billing.index'))
        ->assertOk()
        ->assertSee('Usage')
        ->assertSee('Tasks');
});
