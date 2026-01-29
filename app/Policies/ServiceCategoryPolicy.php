<?php

namespace App\Policies;

use App\Models\ServiceCategory;
use App\Models\User;

class ServiceCategoryPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, ServiceCategory $serviceCategory): bool
    {
        return $serviceCategory->organization_id === $user->organization_id;
    }

    public function create(User $user): bool
    {
        // check profile organization association
        return true;
    }

    public function update(User $user, ServiceCategory $serviceCategory): bool
    {
        return $serviceCategory->organization_id === $user->organization_id;
    }

    public function delete(User $user, ServiceCategory $serviceCategory): bool
    {
        return $serviceCategory->organization_id === $user->organization_id;
    }

    public function restore(User $user, ServiceCategory $serviceCategory): bool
    {
        return $serviceCategory->organization_id === $user->organization_id;
    }

    public function forceDelete(User $user, ServiceCategory $serviceCategory): bool
    {
        return $serviceCategory->organization_id === $user->organization_id;
    }
}
