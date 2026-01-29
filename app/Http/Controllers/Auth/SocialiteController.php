<?php

namespace App\Http\Controllers\Auth;

use App\Enums\RoleEnum;
use App\Http\Controllers\Controller;
use App\Models\Organization;
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

        $user = User::where('oauth_provider', $provider)
            ->where('oauth_id', $socialiteUser->getId())
            ->first();

        if (! $user) {
            // if you change this logic, make sure to update the app\Actions\Fortify\CreateNewUser action as well
            $organization = Organization::create([
                'name' => ($socialiteUser->getName() ?? $socialiteUser->getNickname())."'s Organization",
            ]);

            $user = User::create([
                'oauth_provider' => $provider,
                'oauth_id' => $socialiteUser->getId(),
                'name' => $socialiteUser->getName() ?? $socialiteUser->getNickname(),
                'email' => $socialiteUser->getEmail(),
                'password' => Hash::make(str()->random(24)),
                'email_verified_at' => now(),
                'organization_id' => $organization->id,
            ]);

            $user->assignRole(RoleEnum::Admin);
        }

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
