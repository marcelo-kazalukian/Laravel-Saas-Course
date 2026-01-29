<?php

namespace App\Policies;

use App\Models\Calendar;
use App\Models\User;

class CalendarPolicy
{
    public function update(User $user, Calendar $calendar): bool
    {
        return $calendar->organization_id === $user->organization_id;
    }

    public function delete(User $user, Calendar $calendar): bool
    {
        return $calendar->organization_id === $user->organization_id;
    }
}
