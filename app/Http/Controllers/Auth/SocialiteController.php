<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;

class SocialiteController extends Controller
{
    public function redirect(string $provider): RedirectResponse
    {
        $this->validateProvider($provider);

        return Socialite::driver($provider)->redirect();
    }

    public function callback(string $provider): RedirectResponse
    {
        $this->validateProvider($provider);

        $socialiteUser = Socialite::driver($provider)->user();

        $user = User::firstOrCreate(
            [
                'oauth_provider' => $provider,
                'oauth_id' => $socialiteUser->getId(),
            ],
            [
                'name' => $socialiteUser->getName() ?? $socialiteUser->getNickname(),
                'email' => $socialiteUser->getEmail(),
                'password' => Hash::make(str()->random(24)),
                'email_verified_at' => now(),
            ]
        );

        auth()->login($user, remember: true);

        return redirect()->intended(route('dashboard', absolute: false));
    }

    protected function validateProvider(string $provider): void
    {
        if (! in_array($provider, ['google', 'github'])) {
            abort(404);
        }
    }
}
