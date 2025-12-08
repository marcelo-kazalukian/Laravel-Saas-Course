<?php

use App\Enums\RoleEnum;
use App\Models\Organization;
use App\Models\Task;
use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

test('free plan organization has 10 task limit', function () {
    $organization = Organization::factory()->create();
    $user = User::factory()->create(['organization_id' => $organization->id]);

    expect($organization->getTaskLimit())->toBe(10);
    expect($organization->canCreateTask())->toBeTrue();
});

test('free plan cannot access projects', function () {
    $organization = Organization::factory()->create();

    expect($organization->canAccessProjects())->toBeFalse();
});

test('task creation is blocked when free plan reaches limit', function () {
    $organization = Organization::factory()->create();
    $userRole = Role::firstOrCreate(['name' => RoleEnum::User->value]);
    Permission::firstOrCreate(['name' => 'tasks.create']);
    $userRole->givePermissionTo('tasks.create');

    $user = User::factory()->create(['organization_id' => $organization->id]);
    $user->assignRole($userRole);

    Task::factory()->count(10)->create(['organization_id' => $organization->id]);

    $response = $this->actingAs($user)->post(route('tasks.store'), [
        'name' => 'Test Task',
        'description' => 'Test Description',
    ]);

    $response->assertRedirect(route('billing.index'));
    $response->assertSessionHas('error');

    expect(Task::where('organization_id', $organization->id)->count())->toBe(10);
});

test('free plan users can view existing tasks', function () {
    $organization = Organization::factory()->create();
    $userRole = Role::firstOrCreate(['name' => RoleEnum::User->value]);
    Permission::firstOrCreate(['name' => 'tasks.viewAny']);
    $userRole->givePermissionTo('tasks.viewAny');

    $user = User::factory()->create(['organization_id' => $organization->id]);
    $user->assignRole($userRole);

    Task::factory()->count(5)->create(['organization_id' => $organization->id, 'user_id' => $user->id]);

    $this->actingAs($user)
        ->get(route('tasks.index'))
        ->assertOk();
});
