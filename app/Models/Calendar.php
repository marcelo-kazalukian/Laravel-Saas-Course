<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property-read string $id
 * @property-read string $organization_id
 * @property-read string $location_id
 * @property-read int $slot_duration
 * @property-read bool $show_providers
 */
class Calendar extends Model
{
    protected $fillable = [
        'organization_id',
        'location_id',
        'slot_duration',
        'show_providers',
    ];

    protected $casts = [
        'show_providers' => 'boolean',
    ];

    public function services()
    {
        return $this->hasMany(Service::class);
    }
}
