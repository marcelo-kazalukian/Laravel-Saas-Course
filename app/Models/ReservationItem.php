<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property-read string $id
 * @property-read string $organization_id
 * @property-read string $reservation_id
 * @property-read string $service_id
 * @property-read string $service_option_id
 * @property-read string $service_name
 * @property-read string $service_option_name
 * @property-read int $duration
 * @property-read int $quantity
 * @property-read int $price
 * @property-read Service $service
 */
final class ReservationItem extends Model
{
    protected $guarded = [];

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}
