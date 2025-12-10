<?php

use App\Livewire\Notifications\NotificationBell;
use App\Models\Organization;
use App\Models\Task;
use App\Models\User;
use App\Notifications\TaskAssigned;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;

test('task assignment creates database notification', function () {
    $organization = Organization::factory()->create();
    $assigner = User::factory()->create(['organization_id' => $organization->id, 'name' => 'Assigner User']);
    $assignee = User::factory()->create(['organization_id' => $organization->id, 'email' => 'assignee@example.com']);

    $this->actingAs($assigner)
        ->post(route('tasks.store'), [
            'name' => 'Notification Test Task',
            'description' => 'This is a test task description',
            'assigned_to_user_id' => $assignee->id,
        ])
        ->assertRedirect(route('tasks.index'));

    expect($assignee->unreadNotifications)->toHaveCount(1);

    $notification = $assignee->unreadNotifications->first();

    expect($notification->type)->toBe(TaskAssigned::class)
        ->and($notification->data['task_name'])->toBe('Notification Test Task')
        ->and($notification->data['task_description'])->toBe('This is a test task description')
        ->and($notification->data['assigner_name'])->toBe('Assigner User')
        ->and($notification->data['action_url'])->toContain('tasks')
        ->and($notification->read_at)->toBeNull();
});

test('notification bell shows correct unread count', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create();
    $assigner = User::factory()->create(['organization_id' => $organization->id]);
    $assignee = User::factory()->create(['organization_id' => $organization->id]);

    Task::factory()->count(3)->create([
        'organization_id' => $organization->id,
        'user_id' => $assigner->id,
        'assigned_to_user_id' => $assignee->id,
    ])->each(function ($task) use ($assignee) {
        $assignee->notify(new TaskAssigned($task));
    });

    $this->actingAs($assignee);

    Livewire::test(NotificationBell::class)
        ->assertSee('3');
});

test('notification bell displays unread notifications', function () {
    $organization = Organization::factory()->create();
    $assigner = User::factory()->create(['organization_id' => $organization->id, 'name' => 'John Doe']);
    $assignee = User::factory()->create(['organization_id' => $organization->id]);

    $task = Task::factory()->create([
        'name' => 'Test Notification Task',
        'description' => 'Task description for notification',
        'organization_id' => $organization->id,
        'user_id' => $assigner->id,
        'assigned_to_user_id' => $assignee->id,
    ]);

    $assignee->notify(new TaskAssigned($task));

    $this->actingAs($assignee);

    Livewire::test(NotificationBell::class)
        ->assertSee('Test Notification Task')
        ->assertSee('John Doe');
});

test('mark all as read works correctly', function () {
    $organization = Organization::factory()->create();
    $assigner = User::factory()->create(['organization_id' => $organization->id]);
    $assignee = User::factory()->create(['organization_id' => $organization->id]);

    Task::factory()->count(3)->create([
        'organization_id' => $organization->id,
        'user_id' => $assigner->id,
        'assigned_to_user_id' => $assignee->id,
    ])->each(function ($task) use ($assignee) {
        $assignee->notify(new TaskAssigned($task));
    });

    $this->actingAs($assignee);

    expect($assignee->unreadNotifications)->toHaveCount(3);

    Livewire::test(NotificationBell::class)
        ->call('markAllAsRead');

    $assignee->refresh();

    expect($assignee->unreadNotifications)->toHaveCount(0);
});

test('notifications link to correct task', function () {
    $organization = Organization::factory()->create();
    $assigner = User::factory()->create(['organization_id' => $organization->id]);
    $assignee = User::factory()->create(['organization_id' => $organization->id]);

    $task = Task::factory()->create([
        'organization_id' => $organization->id,
        'user_id' => $assigner->id,
        'assigned_to_user_id' => $assignee->id,
    ]);

    $assignee->notify(new TaskAssigned($task));

    $this->actingAs($assignee);

    $notification = $assignee->unreadNotifications->first();

    expect($notification->data['action_url'])->toBe(route('tasks.show', $task));
});

test('empty state displays when no notifications', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    Livewire::test(NotificationBell::class)
        ->assertSee('No new notifications');
});

test('only unread notifications appear in bell dropdown', function () {
    $organization = Organization::factory()->create();
    $assigner = User::factory()->create(['organization_id' => $organization->id]);
    $assignee = User::factory()->create(['organization_id' => $organization->id]);

    $task1 = Task::factory()->create([
        'name' => 'Unread Task',
        'organization_id' => $organization->id,
        'user_id' => $assigner->id,
        'assigned_to_user_id' => $assignee->id,
    ]);

    $task2 = Task::factory()->create([
        'name' => 'Read Task',
        'organization_id' => $organization->id,
        'user_id' => $assigner->id,
        'assigned_to_user_id' => $assignee->id,
    ]);

    $assignee->notify(new TaskAssigned($task1));
    $assignee->notify(new TaskAssigned($task2));

    $assignee->notifications()->where('data->task_name', 'Read Task')->first()->markAsRead();

    $this->actingAs($assignee);

    Livewire::test(NotificationBell::class)
        ->assertSee('Unread Task')
        ->assertDontSee('Read Task');
});

test('notification bell limits to 5 most recent notifications', function () {
    $organization = Organization::factory()->create();
    $assigner = User::factory()->create(['organization_id' => $organization->id]);
    $assignee = User::factory()->create(['organization_id' => $organization->id]);

    $tasks = Task::factory()->count(7)->create([
        'organization_id' => $organization->id,
        'user_id' => $assigner->id,
        'assigned_to_user_id' => $assignee->id,
    ]);

    foreach ($tasks as $task) {
        $assignee->notify(new TaskAssigned($task));
    }

    $this->actingAs($assignee);

    expect($assignee->unreadNotifications)->toHaveCount(7);

    $component = Livewire::test(NotificationBell::class);

    expect($component->unreadCount)->toBe(7);

    $notifications = $component->notifications;

    expect($notifications)->toHaveCount(5);
});

test('notification contains all required data fields', function () {
    $organization = Organization::factory()->create();
    $assigner = User::factory()->create(['organization_id' => $organization->id, 'name' => 'Jane Doe']);
    $assignee = User::factory()->create(['organization_id' => $organization->id]);

    $task = Task::factory()->create([
        'name' => 'Data Test Task',
        'description' => 'Task description',
        'organization_id' => $organization->id,
        'user_id' => $assigner->id,
        'assigned_to_user_id' => $assignee->id,
    ]);

    $assignee->notify(new TaskAssigned($task));

    $notification = $assignee->notifications->first();

    expect($notification->data)
        ->toHaveKey('task_id')
        ->toHaveKey('task_name')
        ->toHaveKey('task_description')
        ->toHaveKey('assigner_name')
        ->toHaveKey('action_url');

    expect($notification->data['task_id'])->toBe($task->id)
        ->and($notification->data['task_name'])->toBe('Data Test Task')
        ->and($notification->data['task_description'])->toBe('Task description')
        ->and($notification->data['assigner_name'])->toBe('Jane Doe');
});

test('notification badge shows 9+ for more than 9 unread', function () {
    $organization = Organization::factory()->create();
    $assigner = User::factory()->create(['organization_id' => $organization->id]);
    $assignee = User::factory()->create(['organization_id' => $organization->id]);

    Task::factory()->count(12)->create([
        'organization_id' => $organization->id,
        'user_id' => $assigner->id,
        'assigned_to_user_id' => $assignee->id,
    ])->each(function ($task) use ($assignee) {
        $assignee->notify(new TaskAssigned($task));
    });

    $this->actingAs($assignee);

    Livewire::test(NotificationBell::class)
        ->assertSee('9+');
});

test('email notification is sent when user has email notifications enabled', function () {
    Notification::fake();

    $organization = Organization::factory()->create();
    $assigner = User::factory()->create(['organization_id' => $organization->id]);
    $assignee = User::factory()->create([
        'organization_id' => $organization->id,
        'email' => 'assignee@example.com',
        'email_notifications' => true,
    ]);

    $task = Task::factory()->create([
        'organization_id' => $organization->id,
        'user_id' => $assigner->id,
        'assigned_to_user_id' => $assignee->id,
    ]);

    $assignee->notify(new TaskAssigned($task));

    Notification::assertSentTo($assignee, TaskAssigned::class, function ($notification, $channels) {
        return in_array('mail', $channels) && in_array('database', $channels);
    });
});

test('email notification is not sent when user has email notifications disabled', function () {
    Notification::fake();

    $organization = Organization::factory()->create();
    $assigner = User::factory()->create(['organization_id' => $organization->id]);
    $assignee = User::factory()->create([
        'organization_id' => $organization->id,
        'email' => 'assignee@example.com',
        'email_notifications' => false,
    ]);

    $task = Task::factory()->create([
        'organization_id' => $organization->id,
        'user_id' => $assigner->id,
        'assigned_to_user_id' => $assignee->id,
    ]);

    $assignee->notify(new TaskAssigned($task));

    Notification::assertSentTo($assignee, TaskAssigned::class, function ($notification, $channels) {
        return in_array('database', $channels) && ! in_array('mail', $channels);
    });
});

test('database notification is always sent regardless of email preference', function () {
    Notification::fake();

    $organization = Organization::factory()->create();
    $assigner = User::factory()->create(['organization_id' => $organization->id]);
    $assigneeWithEmail = User::factory()->create([
        'organization_id' => $organization->id,
        'email_notifications' => true,
    ]);
    $assigneeWithoutEmail = User::factory()->create([
        'organization_id' => $organization->id,
        'email_notifications' => false,
    ]);

    $task1 = Task::factory()->create([
        'organization_id' => $organization->id,
        'user_id' => $assigner->id,
        'assigned_to_user_id' => $assigneeWithEmail->id,
    ]);

    $task2 = Task::factory()->create([
        'organization_id' => $organization->id,
        'user_id' => $assigner->id,
        'assigned_to_user_id' => $assigneeWithoutEmail->id,
    ]);

    $assigneeWithEmail->notify(new TaskAssigned($task1));
    $assigneeWithoutEmail->notify(new TaskAssigned($task2));

    Notification::assertSentTo($assigneeWithEmail, TaskAssigned::class, function ($notification, $channels) {
        return in_array('database', $channels);
    });

    Notification::assertSentTo($assigneeWithoutEmail, TaskAssigned::class, function ($notification, $channels) {
        return in_array('database', $channels);
    });
});
