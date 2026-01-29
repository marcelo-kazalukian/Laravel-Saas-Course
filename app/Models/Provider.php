<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property-read string $id
 * @property-read string $organization_id
 * @property-read string $name
 * @property-read string|null $email
 * @property-read string|null $phone
 */
class Provider extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'organization_id',
        'name',
        'email',
        'phone',
    ];

    public function services()
    {
        return $this->belongsToMany(Service::class, 'service_provider', 'provider_id', 'service_id')
            ->using(ServiceProvider::class)
            ->withPivot('id', 'day_of_week', 'start_time', 'end_time')
            ->withTimestamps();
    }
}
