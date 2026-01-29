<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property-read string $id
 * @property-read string $organization_id
 * @property-read string $slug
 * @property-read string $name
 * @property-read string $timezone
 * @property-read string|null $address
 * @property-read string|null $city
 * @property-read string|null $state
 * @property-read string|null $postal_code
 * @property-read string|null $country
 * @property-read string|null $phone
 * @property-read string|null $email
 * @property-read Calendar $calendar
 * @property-read Collection<int, LocationHour> $hours
 */
class Location extends Model
{
    protected $fillable = [
        'organization_id',
        'slug',
        'name',
        'timezone',
        'address',
        'city',
        'state',
        'postal_code',
        'country',
        'phone',
        'email',
    ];

    public function calendar(): HasOne
    {
        return $this->hasOne(Calendar::class);
    }

    public function hours(): HasMany
    {
        return $this->hasMany(LocationHour::class);
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }

    public function todaysReservations(): HasMany
    {
        return $this->hasMany(Reservation::class)->whereDate('reservation_date', today());
    }

    public function todaysHours(): HasMany
    {
        return $this->hasMany(LocationHour::class)->where('day_of_week', today()->dayOfWeek);
    }
}
