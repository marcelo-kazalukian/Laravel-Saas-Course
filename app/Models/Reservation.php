<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property-read string $id
 * @property-read string $organization_id
 * @property-read string $customer_id
 * @property-read string $provider_id
 * @property-read Collection<int, ReservationItem> $reservationItems
 * @property-read Carbon $reservation_date
 * @property-read int $day_of_week
 * @property-read int $duration
 * @property-read string $status
 */
final class Reservation extends Model
{
    protected $guarded = [];

    protected $casts = [
        'reservation_date' => 'datetime',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }

    public function reservationItems(): HasMany
    {
        return $this->hasMany(ReservationItem::class);
    }
}
