<?php

use App\Models\Organization;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Spatie\Activitylog\Models\Activity;

test('task creation is logged', function () {
    $user = User::factory()->create();

    $task = Task::create([
        'name' => 'New Task',
        'description' => 'Task description',
        'user_id' => $user->id,
        'organization_id' => $user->organization_id,
    ]);

    expect(Activity::where('subject_type', Task::class)
        ->where('subject_id', $task->id)
        ->where('event', 'created')
        ->exists())->toBeTrue();
});

test('task update is logged', function () {
    $user = User::factory()->create();
    $task = Task::factory()->create([
        'user_id' => $user->id,
        'organization_id' => $user->organization_id,
    ]);

    $task->update(['name' => 'Updated Task Name']);

    expect(Activity::where('subject_type', Task::class)
        ->where('subject_id', $task->id)
        ->where('event', 'updated')
        ->exists())->toBeTrue();
});

test('project creation is logged', function () {
    $user = User::factory()->create();

    $project = Project::create([
        'name' => 'New Project',
        'description' => 'Project description',
        'user_id' => $user->id,
        'organization_id' => $user->organization_id,
    ]);

    expect(Activity::where('subject_type', Project::class)
        ->where('subject_id', $project->id)
        ->where('event', 'created')
        ->exists())->toBeTrue();
});

test('admin users can view activity log page', function () {
    $organization = Organization::factory()->create();
    $adminUser = User::factory()->create([
        'organization_id' => $organization->id,
    ]);
    $adminUser->assignRole('admin');

    $this->actingAs($adminUser)
        ->get(route('activity-log.index'))
        ->assertSuccessful()
        ->assertSee('Activity Log');
});

test('non-admin users cannot view activity log page', function () {
    $user = User::factory()->asViewer()->create();

    $this->actingAs($user)
        ->get(route('activity-log.index'))
        ->assertForbidden();
});

test('activity log displays task and project activities', function () {
    $organization = Organization::factory()->create();
    $adminUser = User::factory()->create([
        'organization_id' => $organization->id,
    ]);
    $adminUser->assignRole('admin');

    $task = Task::create([
        'name' => 'Test Task',
        'description' => 'Task description',
        'user_id' => $adminUser->id,
        'organization_id' => $organization->id,
    ]);

    $project = Project::create([
        'name' => 'Test Project',
        'description' => 'Project description',
        'user_id' => $adminUser->id,
        'organization_id' => $organization->id,
    ]);

    $this->actingAs($adminUser)
        ->get(route('activity-log.index'))
        ->assertSuccessful()
        ->assertSee('Task has been created')
        ->assertSee('Project has been created');
});

test('task show page displays recent activity', function () {
    $user = User::factory()->create();
    $task = Task::factory()->create([
        'user_id' => $user->id,
        'organization_id' => $user->organization_id,
    ]);

    $task->update(['name' => 'Updated Task']);

    $this->actingAs($user)
        ->get(route('tasks.show', $task))
        ->assertSuccessful()
        ->assertSee('Recent Activity')
        ->assertSee('Task has been updated');
});

test('project show page displays recent activity', function () {
    $organization = Organization::factory()->create();
    $organization->subscriptions()->create([
        'type' => 'default',
        'stripe_id' => 'sub_test',
        'stripe_status' => 'active',
        'stripe_price' => config('subscriptions.plans.pro.prices.monthly'),
        'quantity' => 1,
    ]);

    $user = User::factory()->create([
        'organization_id' => $organization->id,
    ]);

    $project = Project::factory()->create([
        'user_id' => $user->id,
        'organization_id' => $organization->id,
    ]);

    $project->update(['name' => 'Updated Project']);

    $this->actingAs($user)
        ->get(route('projects.show', $project))
        ->assertSuccessful()
        ->assertSee('Recent Activity')
        ->assertSee('Project has been updated');
});

test('task soft delete is logged', function () {
    $user = User::factory()->create();
    $task = Task::factory()->create([
        'user_id' => $user->id,
        'organization_id' => $user->organization_id,
    ]);

    $task->delete();

    expect(Activity::where('subject_type', Task::class)
        ->where('subject_id', $task->id)
        ->where('event', 'deleted')
        ->exists())->toBeTrue();

    expect($task->trashed())->toBeTrue();
});

test('project soft delete is logged', function () {
    $user = User::factory()->create();
    $project = Project::factory()->create([
        'user_id' => $user->id,
        'organization_id' => $user->organization_id,
    ]);

    $project->delete();

    expect(Activity::where('subject_type', Project::class)
        ->where('subject_id', $project->id)
        ->where('event', 'deleted')
        ->exists())->toBeTrue();

    expect($project->trashed())->toBeTrue();
});

test('soft deleted task activities are shown in activity log', function () {
    $organization = Organization::factory()->create();
    $adminUser = User::factory()->create([
        'organization_id' => $organization->id,
    ]);
    $adminUser->assignRole('admin');

    $task = Task::create([
        'name' => 'Task to Delete',
        'description' => 'Task description',
        'user_id' => $adminUser->id,
        'organization_id' => $organization->id,
    ]);

    $task->delete();

    $this->actingAs($adminUser)
        ->get(route('activity-log.index'))
        ->assertSuccessful()
        ->assertSee('Task has been deleted')
        ->assertSee('Deleted');
});

test('soft deleted project activities are shown in activity log', function () {
    $organization = Organization::factory()->create();
    $organization->subscriptions()->create([
        'type' => 'default',
        'stripe_id' => 'sub_test',
        'stripe_status' => 'active',
        'stripe_price' => config('subscriptions.plans.pro.prices.monthly'),
        'quantity' => 1,
    ]);

    $adminUser = User::factory()->create([
        'organization_id' => $organization->id,
    ]);
    $adminUser->assignRole('admin');

    $project = Project::create([
        'name' => 'Project to Delete',
        'description' => 'Project description',
        'user_id' => $adminUser->id,
        'organization_id' => $organization->id,
    ]);

    $project->delete();

    $this->actingAs($adminUser)
        ->get(route('activity-log.index'))
        ->assertSuccessful()
        ->assertSee('Project has been deleted')
        ->assertSee('Deleted');
});

test('tasks can be restored after soft delete', function () {
    $user = User::factory()->create();
    $task = Task::factory()->create([
        'user_id' => $user->id,
        'organization_id' => $user->organization_id,
    ]);

    $task->delete();
    expect($task->trashed())->toBeTrue();

    $task->restore();
    expect($task->trashed())->toBeFalse();
});

test('projects can be restored after soft delete', function () {
    $user = User::factory()->create();
    $project = Project::factory()->create([
        'user_id' => $user->id,
        'organization_id' => $user->organization_id,
    ]);

    $project->delete();
    expect($project->trashed())->toBeTrue();

    $project->restore();
    expect($project->trashed())->toBeFalse();
});
