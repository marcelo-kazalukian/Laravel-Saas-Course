<?php

namespace App\Models;

use App\Enums\WeekDayEnum;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

/**
 * @property-read string $id
 * @property-read string $organization_id
 * @property-read string $day_of_week
 * @property-read string $start_time
 * @property-read string $end_time
 */
class LocationHour extends Model
{
    protected $fillable = [
        'organization_id',
        'location_id',
        'day_of_week',
        'start_time',
        'end_time',
    ];

    protected $casts = [
        'day_of_week' => WeekDayEnum::class,
    ];

    protected function startTime(): Attribute
    {
        return Attribute::make(
            get: fn (string $value) => substr($value, 0, 5),
            set: fn (string $value) => $value.':00',
        );
    }

    protected function endTime(): Attribute
    {
        return Attribute::make(
            get: fn (string $value) => substr($value, 0, 5),
            set: fn (string $value) => $value.':00',
        );
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }
}
