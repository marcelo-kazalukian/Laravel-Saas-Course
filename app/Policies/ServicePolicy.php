<?php

namespace App\Policies;

use App\Models\Service;
use App\Models\User;

class ServicePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Service $service): bool
    {
        return $service->organization_id === $user->organization_id;
    }

    public function create(User $user): bool
    {
        // check profile organization association
        return true;
    }

    public function update(User $user, Service $service): bool
    {
        return $service->organization_id === $user->organization_id;
    }

    public function delete(User $user, Service $service): bool
    {
        return $service->organization_id === $user->organization_id;
    }

    public function restore(User $user, Service $service): bool
    {
        return $service->organization_id === $user->organization_id;
    }

    public function forceDelete(User $user, Service $service): bool
    {
        return $service->organization_id === $user->organization_id;
    }
}
