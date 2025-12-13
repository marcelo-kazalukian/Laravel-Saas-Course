<?php

use App\Models\Organization;
use App\Models\Task;
use App\Models\User;
use App\Notifications\TaskAssigned;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;

test('guests cannot access tasks index', function () {
    $this->get(route('tasks.index'))->assertRedirect(route('login'));
});

test('authenticated users can view tasks index', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('tasks.index'))
        ->assertSuccessful()
        ->assertSee('Tasks');
});

test('users can only see tasks from their organization', function () {
    $organization1 = Organization::factory()->create(['name' => 'Org 1']);
    $organization2 = Organization::factory()->create(['name' => 'Org 2']);

    $user1 = User::factory()->create(['organization_id' => $organization1->id]);
    $user2 = User::factory()->create(['organization_id' => $organization2->id]);

    $task1 = Task::factory()->create([
        'name' => 'Org 1 Task',
        'organization_id' => $organization1->id,
        'user_id' => $user1->id,
    ]);

    $task2 = Task::factory()->create([
        'name' => 'Org 2 Task',
        'organization_id' => $organization2->id,
        'user_id' => $user2->id,
    ]);

    $this->actingAs($user1)
        ->get(route('tasks.index'))
        ->assertSuccessful()
        ->assertSee('Org 1 Task')
        ->assertDontSee('Org 2 Task');
});

test('authenticated users can view create task page', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('tasks.create'))
        ->assertSuccessful()
        ->assertSee('Create New Task');
});

test('users can create a task with name only', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('tasks.store'), [
            'name' => 'New Task',
        ])
        ->assertRedirect(route('tasks.index'))
        ->assertSessionHas('success', 'Task created successfully.');

    $this->assertDatabaseHas('tasks', [
        'name' => 'New Task',
        'description' => null,
        'user_id' => $user->id,
        'organization_id' => $user->organization_id,
    ]);
});

test('users can create a task with name and description', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('tasks.store'), [
            'name' => 'Complete Task',
            'description' => 'This is a detailed description',
        ])
        ->assertRedirect(route('tasks.index'))
        ->assertSessionHas('success', 'Task created successfully.');

    $this->assertDatabaseHas('tasks', [
        'name' => 'Complete Task',
        'description' => 'This is a detailed description',
        'user_id' => $user->id,
        'organization_id' => $user->organization_id,
    ]);
});

test('task name is required when creating', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('tasks.store'), [
            'name' => '',
            'description' => 'Description without name',
        ])
        ->assertSessionHasErrors(['name']);

    $this->assertDatabaseMissing('tasks', [
        'description' => 'Description without name',
    ]);
});

test('task name cannot exceed 255 characters', function () {
    $user = User::factory()->create();
    $longName = str_repeat('a', 256);

    $this->actingAs($user)
        ->post(route('tasks.store'), [
            'name' => $longName,
        ])
        ->assertSessionHasErrors(['name']);
});

test('users can view edit task page for their organization tasks', function () {
    $user = User::factory()->create();
    $task = Task::factory()->create([
        'organization_id' => $user->organization_id,
        'user_id' => $user->id,
    ]);

    $this->actingAs($user)
        ->get(route('tasks.edit', $task))
        ->assertSuccessful()
        ->assertSee('Edit Task')
        ->assertSee($task->name);
});

test('users cannot view edit page for tasks from other organizations', function () {
    $organization1 = Organization::factory()->create();
    $organization2 = Organization::factory()->create();

    $user1 = User::factory()->create(['organization_id' => $organization1->id]);
    $user2 = User::factory()->create(['organization_id' => $organization2->id]);

    $task = Task::factory()->create([
        'organization_id' => $organization2->id,
        'user_id' => $user2->id,
    ]);

    $this->actingAs($user1)
        ->get(route('tasks.edit', $task))
        ->assertForbidden();
});

test('users can update their organization tasks', function () {
    $user = User::factory()->create();
    $task = Task::factory()->create([
        'name' => 'Original Name',
        'description' => 'Original Description',
        'organization_id' => $user->organization_id,
        'user_id' => $user->id,
    ]);

    $this->actingAs($user)
        ->put(route('tasks.update', $task), [
            'name' => 'Updated Name',
            'description' => 'Updated Description',
        ])
        ->assertRedirect(route('tasks.index'))
        ->assertSessionHas('success', 'Task updated successfully.');

    $this->assertDatabaseHas('tasks', [
        'id' => $task->id,
        'name' => 'Updated Name',
        'description' => 'Updated Description',
    ]);
});

test('users cannot update tasks from other organizations', function () {
    $organization1 = Organization::factory()->create();
    $organization2 = Organization::factory()->create();

    $user1 = User::factory()->create(['organization_id' => $organization1->id]);
    $user2 = User::factory()->create(['organization_id' => $organization2->id]);

    $task = Task::factory()->create([
        'name' => 'Original Name',
        'organization_id' => $organization2->id,
        'user_id' => $user2->id,
    ]);

    $this->actingAs($user1)
        ->put(route('tasks.update', $task), [
            'name' => 'Hacked Name',
        ])
        ->assertForbidden();

    $this->assertDatabaseHas('tasks', [
        'id' => $task->id,
        'name' => 'Original Name',
    ]);
});

test('task name is required when updating', function () {
    $user = User::factory()->create();
    $task = Task::factory()->create([
        'organization_id' => $user->organization_id,
        'user_id' => $user->id,
    ]);

    $this->actingAs($user)
        ->put(route('tasks.update', $task), [
            'name' => '',
        ])
        ->assertSessionHasErrors(['name']);
});

test('users can delete their organization tasks', function () {
    $user = User::factory()->create();
    $task = Task::factory()->create([
        'organization_id' => $user->organization_id,
        'user_id' => $user->id,
    ]);

    $this->actingAs($user)
        ->delete(route('tasks.destroy', $task))
        ->assertRedirect(route('tasks.index'))
        ->assertSessionHas('success', 'Task deleted successfully.');

    $this->assertSoftDeleted('tasks', [
        'id' => $task->id,
    ]);
});

test('users cannot delete tasks from other organizations', function () {
    $organization1 = Organization::factory()->create();
    $organization2 = Organization::factory()->create();

    $user1 = User::factory()->create(['organization_id' => $organization1->id]);
    $user2 = User::factory()->create(['organization_id' => $organization2->id]);

    $task = Task::factory()->create([
        'organization_id' => $organization2->id,
        'user_id' => $user2->id,
    ]);

    $this->actingAs($user1)
        ->delete(route('tasks.destroy', $task))
        ->assertForbidden();

    $this->assertDatabaseHas('tasks', [
        'id' => $task->id,
    ]);
});

test('users can create a task without assigning it', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('tasks.store'), [
            'name' => 'Unassigned Task',
        ])
        ->assertRedirect(route('tasks.index'))
        ->assertSessionHas('success', 'Task created successfully.');

    $this->assertDatabaseHas('tasks', [
        'name' => 'Unassigned Task',
        'assigned_to_user_id' => null,
    ]);
});

test('users can create a task and assign it to a user in their organization', function () {
    $organization = Organization::factory()->create();
    $user = User::factory()->create(['organization_id' => $organization->id]);
    $assignee = User::factory()->create(['organization_id' => $organization->id, 'name' => 'John Doe']);

    $this->actingAs($user)
        ->post(route('tasks.store'), [
            'name' => 'Assigned Task',
            'description' => 'Task description',
            'assigned_to_user_id' => $assignee->id,
        ])
        ->assertRedirect(route('tasks.index'))
        ->assertSessionHas('success', 'Task created successfully.');

    $this->assertDatabaseHas('tasks', [
        'name' => 'Assigned Task',
        'assigned_to_user_id' => $assignee->id,
        'user_id' => $user->id,
        'organization_id' => $organization->id,
    ]);
});

test('users can update task assignment', function () {
    $organization = Organization::factory()->create();
    $user = User::factory()->create(['organization_id' => $organization->id]);
    $assignee1 = User::factory()->create(['organization_id' => $organization->id, 'name' => 'Jane Doe']);
    $assignee2 = User::factory()->create(['organization_id' => $organization->id, 'name' => 'John Smith']);

    $task = Task::factory()->create([
        'organization_id' => $organization->id,
        'user_id' => $user->id,
        'assigned_to_user_id' => $assignee1->id,
    ]);

    $this->actingAs($user)
        ->put(route('tasks.update', $task), [
            'name' => $task->name,
            'assigned_to_user_id' => $assignee2->id,
        ])
        ->assertRedirect(route('tasks.index'))
        ->assertSessionHas('success', 'Task updated successfully.');

    $this->assertDatabaseHas('tasks', [
        'id' => $task->id,
        'assigned_to_user_id' => $assignee2->id,
    ]);
});

test('users can unassign a task', function () {
    $organization = Organization::factory()->create();
    $user = User::factory()->create(['organization_id' => $organization->id]);
    $assignee = User::factory()->create(['organization_id' => $organization->id]);

    $task = Task::factory()->create([
        'organization_id' => $organization->id,
        'user_id' => $user->id,
        'assigned_to_user_id' => $assignee->id,
    ]);

    $this->actingAs($user)
        ->put(route('tasks.update', $task), [
            'name' => $task->name,
            'assigned_to_user_id' => null,
        ])
        ->assertRedirect(route('tasks.index'))
        ->assertSessionHas('success', 'Task updated successfully.');

    $this->assertDatabaseHas('tasks', [
        'id' => $task->id,
        'assigned_to_user_id' => null,
    ]);
});

test('assigned user validation fails for non-existent user', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('tasks.store'), [
            'name' => 'Task with invalid assignee',
            'assigned_to_user_id' => 99999,
        ])
        ->assertSessionHasErrors(['assigned_to_user_id']);
});

test('tasks index displays assigned user name', function () {
    $organization = Organization::factory()->create();
    $user = User::factory()->create(['organization_id' => $organization->id]);
    $assignee = User::factory()->create(['organization_id' => $organization->id, 'name' => 'Alice Johnson']);

    $assignedTask = Task::factory()->create([
        'name' => 'Assigned Task',
        'organization_id' => $organization->id,
        'user_id' => $user->id,
        'assigned_to_user_id' => $assignee->id,
    ]);

    $unassignedTask = Task::factory()->create([
        'name' => 'Unassigned Task',
        'organization_id' => $organization->id,
        'user_id' => $user->id,
        'assigned_to_user_id' => null,
    ]);

    $this->actingAs($user)
        ->get(route('tasks.index'))
        ->assertSuccessful()
        ->assertSee('Alice Johnson')
        ->assertSee('Unassigned');
});

test('create task page shows users from the same organization', function () {
    $organization = Organization::factory()->create();
    $user = User::factory()->create(['organization_id' => $organization->id]);
    $teammate = User::factory()->create(['organization_id' => $organization->id, 'name' => 'Teammate']);
    $outsider = User::factory()->create(['name' => 'Outsider']);

    $this->actingAs($user)
        ->get(route('tasks.create'))
        ->assertSuccessful()
        ->assertSee('Teammate')
        ->assertDontSee('Outsider');
});

test('edit task page shows users from the same organization', function () {
    $organization = Organization::factory()->create();
    $user = User::factory()->create(['organization_id' => $organization->id]);
    $teammate = User::factory()->create(['organization_id' => $organization->id, 'name' => 'Teammate']);
    $outsider = User::factory()->create(['name' => 'Outsider']);

    $task = Task::factory()->create([
        'organization_id' => $organization->id,
        'user_id' => $user->id,
    ]);

    $this->actingAs($user)
        ->get(route('tasks.edit', $task))
        ->assertSuccessful()
        ->assertSee('Teammate')
        ->assertDontSee('Outsider');
});

test('notification is sent when task is assigned to user on creation', function () {
    Notification::fake();

    $organization = Organization::factory()->create();
    $user = User::factory()->create(['organization_id' => $organization->id]);
    $assignee = User::factory()->create(['organization_id' => $organization->id, 'email' => 'assignee@example.com']);

    $this->actingAs($user)
        ->post(route('tasks.store'), [
            'name' => 'Assigned Task',
            'description' => 'Task description',
            'assigned_to_user_id' => $assignee->id,
        ])
        ->assertRedirect(route('tasks.index'));

    Notification::assertSentTo($assignee, TaskAssigned::class, function ($notification) {
        return $notification->task->name === 'Assigned Task';
    });
});

test('notification is not sent when task is created without assignment', function () {
    Notification::fake();

    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('tasks.store'), [
            'name' => 'Unassigned Task',
        ])
        ->assertRedirect(route('tasks.index'));

    Notification::assertNothingSent();
});

test('notification is sent when task is reassigned to different user', function () {
    Notification::fake();

    $organization = Organization::factory()->create();
    $user = User::factory()->create(['organization_id' => $organization->id]);
    $assignee1 = User::factory()->create(['organization_id' => $organization->id, 'email' => 'assignee1@example.com']);
    $assignee2 = User::factory()->create(['organization_id' => $organization->id, 'email' => 'assignee2@example.com']);

    $task = Task::factory()->create([
        'organization_id' => $organization->id,
        'user_id' => $user->id,
        'assigned_to_user_id' => $assignee1->id,
    ]);

    $this->actingAs($user)
        ->put(route('tasks.update', $task), [
            'name' => $task->name,
            'assigned_to_user_id' => $assignee2->id,
        ])
        ->assertRedirect(route('tasks.index'));

    Notification::assertSentTo($assignee2, TaskAssigned::class, function ($notification) use ($task) {
        return $notification->task->id === $task->id;
    });

    Notification::assertNotSentTo($assignee1, TaskAssigned::class);
});

test('notification is not sent when task assignment remains unchanged', function () {
    Notification::fake();

    $organization = Organization::factory()->create();
    $user = User::factory()->create(['organization_id' => $organization->id]);
    $assignee = User::factory()->create(['organization_id' => $organization->id]);

    $task = Task::factory()->create([
        'organization_id' => $organization->id,
        'user_id' => $user->id,
        'assigned_to_user_id' => $assignee->id,
    ]);

    $this->actingAs($user)
        ->put(route('tasks.update', $task), [
            'name' => 'Updated Task Name',
            'assigned_to_user_id' => $assignee->id,
        ])
        ->assertRedirect(route('tasks.index'));

    Notification::assertNothingSent();
});

test('notification is not sent when task is unassigned', function () {
    Notification::fake();

    $organization = Organization::factory()->create();
    $user = User::factory()->create(['organization_id' => $organization->id]);
    $assignee = User::factory()->create(['organization_id' => $organization->id]);

    $task = Task::factory()->create([
        'organization_id' => $organization->id,
        'user_id' => $user->id,
        'assigned_to_user_id' => $assignee->id,
    ]);

    $this->actingAs($user)
        ->put(route('tasks.update', $task), [
            'name' => $task->name,
            'assigned_to_user_id' => null,
        ])
        ->assertRedirect(route('tasks.index'));

    Notification::assertNothingSent();
});

test('notification is sent when unassigned task gets assigned to a user', function () {
    Notification::fake();

    $organization = Organization::factory()->create();
    $user = User::factory()->create(['organization_id' => $organization->id]);
    $assignee = User::factory()->create(['organization_id' => $organization->id, 'email' => 'assignee@example.com']);

    $task = Task::factory()->create([
        'organization_id' => $organization->id,
        'user_id' => $user->id,
        'assigned_to_user_id' => null,
    ]);

    $this->actingAs($user)
        ->put(route('tasks.update', $task), [
            'name' => $task->name,
            'assigned_to_user_id' => $assignee->id,
        ])
        ->assertRedirect(route('tasks.index'));

    Notification::assertSentTo($assignee, TaskAssigned::class, function ($notification) use ($task) {
        return $notification->task->id === $task->id;
    });
});

test('users can create a task with images', function () {
    Storage::fake('public');

    $user = User::factory()->create();
    $image1 = UploadedFile::fake()->image('task-image-1.jpg', 800, 600);
    $image2 = UploadedFile::fake()->image('task-image-2.png', 1024, 768);

    $this->actingAs($user)
        ->post(route('tasks.store'), [
            'name' => 'Task with Images',
            'description' => 'A task that has uploaded images',
            'images' => [$image1, $image2],
        ])
        ->assertRedirect(route('tasks.index'))
        ->assertSessionHas('success', 'Task created successfully.');

    $task = Task::where('name', 'Task with Images')->first();

    expect($task)->not->toBeNull();
    expect($task->getMedia('images'))->toHaveCount(2);
});

test('users can add images when updating a task', function () {
    Storage::fake('public');

    $user = User::factory()->create();
    $task = Task::factory()->create([
        'organization_id' => $user->organization_id,
        'user_id' => $user->id,
    ]);

    $image = UploadedFile::fake()->image('new-task-image.jpg', 800, 600);

    $this->actingAs($user)
        ->put(route('tasks.update', $task), [
            'name' => $task->name,
            'images' => [$image],
        ])
        ->assertRedirect(route('tasks.index'))
        ->assertSessionHas('success', 'Task updated successfully.');

    $task->refresh();

    expect($task->getMedia('images'))->toHaveCount(1);
});

test('users can delete images when updating a task', function () {
    Storage::fake('public');

    $user = User::factory()->create();
    $task = Task::factory()->create([
        'organization_id' => $user->organization_id,
        'user_id' => $user->id,
    ]);

    $task->addMedia(UploadedFile::fake()->image('existing-image.jpg', 800, 600))
        ->toMediaCollection('images');

    $mediaId = $task->getFirstMedia('images')->id;

    $this->actingAs($user)
        ->put(route('tasks.update', $task), [
            'name' => $task->name,
            'delete_images' => [$mediaId],
        ])
        ->assertRedirect(route('tasks.index'))
        ->assertSessionHas('success', 'Task updated successfully.');

    $task->refresh();

    expect($task->getMedia('images'))->toHaveCount(0);
});

test('image upload validation rejects non-image files', function () {
    $user = User::factory()->create();
    $pdfFile = UploadedFile::fake()->create('document.pdf', 1024, 'application/pdf');

    $this->actingAs($user)
        ->post(route('tasks.store'), [
            'name' => 'Task with invalid file',
            'images' => [$pdfFile],
        ])
        ->assertSessionHasErrors(['images.0']);
});

test('image upload validation rejects files exceeding size limit', function () {
    $user = User::factory()->create();
    $largeImage = UploadedFile::fake()->image('large-image.jpg')->size(6000);

    $this->actingAs($user)
        ->post(route('tasks.store'), [
            'name' => 'Task with large image',
            'images' => [$largeImage],
        ])
        ->assertSessionHasErrors(['images.0']);
});

test('task show page displays images', function () {
    Storage::fake('public');

    $user = User::factory()->create();
    $task = Task::factory()->create([
        'organization_id' => $user->organization_id,
        'user_id' => $user->id,
    ]);

    $task->addMedia(UploadedFile::fake()->image('show-image.jpg', 800, 600))
        ->toMediaCollection('images');

    $this->actingAs($user)
        ->get(route('tasks.show', $task))
        ->assertSuccessful()
        ->assertSee('Images');
});

test('task index page displays thumbnail', function () {
    Storage::fake('public');

    $user = User::factory()->create();
    $task = Task::factory()->create([
        'name' => 'Task with Thumbnail',
        'organization_id' => $user->organization_id,
        'user_id' => $user->id,
    ]);

    $task->addMedia(UploadedFile::fake()->image('thumbnail-test.jpg', 800, 600))
        ->toMediaCollection('images');

    $this->actingAs($user)
        ->get(route('tasks.index'))
        ->assertSuccessful()
        ->assertSee('Task with Thumbnail');
});
