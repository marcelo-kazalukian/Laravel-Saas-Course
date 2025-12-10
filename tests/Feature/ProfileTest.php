<?php

use App\Livewire\Settings\Profile;
use App\Models\User;
use Livewire\Livewire;

test('user can update email notification preference to disabled', function () {
    $user = User::factory()->create(['email_notifications' => true]);

    $this->actingAs($user);

    Livewire::test(Profile::class)
        ->set('email_notifications', false)
        ->call('updateProfileInformation');

    $user->refresh();

    expect($user->email_notifications)->toBeFalse();
});

test('user can update email notification preference to enabled', function () {
    $user = User::factory()->create(['email_notifications' => false]);

    $this->actingAs($user);

    Livewire::test(Profile::class)
        ->set('email_notifications', true)
        ->call('updateProfileInformation');

    $user->refresh();

    expect($user->email_notifications)->toBeTrue();
});

test('email notification preference is loaded correctly on mount', function () {
    $user = User::factory()->create(['email_notifications' => false]);

    $this->actingAs($user);

    Livewire::test(Profile::class)
        ->assertSet('email_notifications', false);

    $user2 = User::factory()->create(['email_notifications' => true]);

    $this->actingAs($user2);

    Livewire::test(Profile::class)
        ->assertSet('email_notifications', true);
});

test('user can update profile with email notifications unchanged', function () {
    $user = User::factory()->create([
        'name' => 'Original Name',
        'email' => 'original@example.com',
        'email_notifications' => true,
    ]);

    $this->actingAs($user);

    Livewire::test(Profile::class)
        ->set('name', 'Updated Name')
        ->set('email', 'updated@example.com')
        ->call('updateProfileInformation');

    $user->refresh();

    expect($user->name)->toBe('Updated Name')
        ->and($user->email)->toBe('updated@example.com')
        ->and($user->email_notifications)->toBeTrue();
});
