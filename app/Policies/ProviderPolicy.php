<?php

namespace App\Policies;

use App\Models\Provider;
use App\Models\User;

class ProviderPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Provider $provider): bool
    {
        return $provider->organization_id === $user->organization_id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Provider $provider): bool
    {
        return $provider->organization_id === $user->organization_id;
    }

    public function delete(User $user, Provider $provider): bool
    {
        return $provider->organization_id === $user->organization_id;
    }

    public function restore(User $user, Provider $provider): bool
    {
        return $provider->organization_id === $user->organization_id;
    }

    public function forceDelete(User $user, Provider $provider): bool
    {
        return $provider->organization_id === $user->organization_id;
    }
}
