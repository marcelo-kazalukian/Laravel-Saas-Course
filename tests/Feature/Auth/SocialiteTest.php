<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;

use function Pest\Laravel\assertAuthenticated;
use function Pest\Laravel\assertDatabaseHas;

test('redirects to google oauth provider', function () {
    $response = $this->get(route('socialite.redirect', 'google'));

    $response->assertRedirect();
});

test('redirects to github oauth provider', function () {
    $response = $this->get(route('socialite.redirect', 'github'));

    $response->assertRedirect();
});

test('creates new user on google oauth callback', function () {
    $socialiteUser = \Mockery::mock(SocialiteUser::class);
    $socialiteUser->shouldReceive('getId')->andReturn('google-123');
    $socialiteUser->shouldReceive('getName')->andReturn('John Doe');
    $socialiteUser->shouldReceive('getEmail')->andReturn('john@example.com');

    Socialite::shouldReceive('driver->user')->andReturn($socialiteUser);

    $response = $this->get(route('socialite.callback', 'google'));

    $response->assertRedirect(route('dashboard'));
    assertAuthenticated();
    assertDatabaseHas('users', [
        'email' => 'john@example.com',
        'name' => 'John Doe',
        'oauth_provider' => 'google',
        'oauth_id' => 'google-123',
    ]);

    $user = User::where('email', 'john@example.com')->first();
    expect($user->email_verified_at)->not->toBeNull();
});

test('creates new user on github oauth callback', function () {
    $socialiteUser = \Mockery::mock(SocialiteUser::class);
    $socialiteUser->shouldReceive('getId')->andReturn('github-456');
    $socialiteUser->shouldReceive('getName')->andReturn('Jane Smith');
    $socialiteUser->shouldReceive('getEmail')->andReturn('jane@example.com');

    Socialite::shouldReceive('driver->user')->andReturn($socialiteUser);

    $response = $this->get(route('socialite.callback', 'github'));

    $response->assertRedirect(route('dashboard'));
    assertAuthenticated();
    assertDatabaseHas('users', [
        'email' => 'jane@example.com',
        'name' => 'Jane Smith',
        'oauth_provider' => 'github',
        'oauth_id' => 'github-456',
    ]);
});

test('logs in existing oauth user', function () {
    $user = User::factory()->create([
        'email' => 'existing@example.com',
        'name' => 'Existing User',
        'oauth_provider' => 'google',
        'oauth_id' => 'google-789',
        'password' => Hash::make('random-password'),
    ]);

    $socialiteUser = \Mockery::mock(SocialiteUser::class);
    $socialiteUser->shouldReceive('getId')->andReturn('google-789');
    $socialiteUser->shouldReceive('getName')->andReturn('Existing User');
    $socialiteUser->shouldReceive('getEmail')->andReturn('existing@example.com');

    Socialite::shouldReceive('driver->user')->andReturn($socialiteUser);

    $response = $this->get(route('socialite.callback', 'google'));

    $response->assertRedirect(route('dashboard'));
    assertAuthenticated();
    expect(auth()->id())->toBe($user->id);
});

test('rejects invalid oauth provider', function () {
    $response = $this->get(route('socialite.redirect', 'invalid-provider'));

    $response->assertNotFound();
});

test('oauth callback rejects invalid provider', function () {
    $response = $this->get(route('socialite.callback', 'invalid-provider'));

    $response->assertNotFound();
});

test('uses nickname when name is not available', function () {
    $socialiteUser = \Mockery::mock(SocialiteUser::class);
    $socialiteUser->shouldReceive('getId')->andReturn('github-999');
    $socialiteUser->shouldReceive('getName')->andReturn(null);
    $socialiteUser->shouldReceive('getNickname')->andReturn('coolguy123');
    $socialiteUser->shouldReceive('getEmail')->andReturn('coolguy@example.com');

    Socialite::shouldReceive('driver->user')->andReturn($socialiteUser);

    $response = $this->get(route('socialite.callback', 'github'));

    $response->assertRedirect(route('dashboard'));
    assertDatabaseHas('users', [
        'email' => 'coolguy@example.com',
        'name' => 'coolguy123',
    ]);
});
