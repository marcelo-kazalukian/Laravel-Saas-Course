<?php

namespace App\Models;

use App\Enums\WeekDayEnum;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * @property-read string $id
 * @property-read string $organization_id
 * @property-read string $service_id
 * @property-read string $provider_id
 * @property-read string $day_of_week
 * @property-read string $day_of_week_name
 * @property-read string $start_time
 * @property-read string $end_time
 */
class ServiceProvider extends Pivot
{
    protected $table = 'service_provider';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'organization_id',
        'service_id',
        'provider_id',
        'day_of_week',
        'start_time',
        'end_time',
    ];

    protected function dayOfWeekName(): Attribute
    {
        return Attribute::get(fn () => WeekDayEnum::from($this->day_of_week)->label());
    }

    public function provider()
    {
        return $this->belongsToMany(Provider::class);
    }
}
