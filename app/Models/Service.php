<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property-read string $id
 * @property-read string $organization_id
 * @property-read string $calendar_id
 * @property-read string|null $service_category_id
 * @property-read string $name
 * @property-read string|null $description
 * @property-read bool $active
 */
class Service extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'organization_id',
        'calendar_id',
        'service_category_id',
        'name',
        'description',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function category()
    {
        return $this->belongsTo(ServiceCategory::class, 'service_category_id');
    }

    public function serviceOptions()
    {
        return $this->hasMany(ServiceOption::class, 'service_id');
    }

    public function providers()
    {
        return $this->belongsToMany(Provider::class, 'service_provider', 'service_id', 'provider_id')
            ->using(ServiceProvider::class)
            ->withPivot('day_of_week', 'start_time', 'end_time')
            ->withTimestamps();
    }
}
