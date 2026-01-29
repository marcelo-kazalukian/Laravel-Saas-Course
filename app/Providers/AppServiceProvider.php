<?php

namespace App\Providers;

use App\Models\Location;
use App\Models\User;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::define('manage-billing', fn (User $user) => $user->isAdmin());

        Event::listen(Login::class, function (Login $event) {
            session(['current_location_id' => $event->user->default_location_id]);

            $location = Location::find($event->user->default_location_id);

            session(['current_location_timezone' => $location?->timezone ?? null]);
        });
    }
}
