<?php

use App\Models\Organization;
use App\Models\User;

test('guests cannot access billing page', function () {
    $this->get(route('billing.index'))
        ->assertRedirect(route('login'));
});

test('admin users can access billing page', function () {
    $organization = Organization::factory()->create();
    $user = User::factory()->asAdmin()->create(['organization_id' => $organization->id]);

    $this->actingAs($user)
        ->get(route('billing.index'))
        ->assertOk()
        ->assertSee('Billing');
});

test('non-admin users cannot access billing page', function () {
    $organization = Organization::factory()->create();
    $user = User::factory()->asUser()->create(['organization_id' => $organization->id]);

    $this->actingAs($user)
        ->get(route('billing.index'))
        ->assertForbidden();
});

test('viewer users cannot access billing page', function () {
    $organization = Organization::factory()->create();
    $user = User::factory()->asViewer()->create(['organization_id' => $organization->id]);

    $this->actingAs($user)
        ->get(route('billing.index'))
        ->assertForbidden();
});

test('billing page shows current plan', function () {
    $organization = Organization::factory()->create();
    $user = User::factory()->asAdmin()->create(['organization_id' => $organization->id]);

    $this->actingAs($user)
        ->get(route('billing.index'))
        ->assertOk()
        ->assertSee('Current Plan')
        ->assertSee('Free');
});

test('billing page shows usage statistics', function () {
    $organization = Organization::factory()->create();
    $user = User::factory()->asAdmin()->create(['organization_id' => $organization->id]);

    $this->actingAs($user)
        ->get(route('billing.index'))
        ->assertOk()
        ->assertSee('Usage')
        ->assertSee('Tasks');
});
