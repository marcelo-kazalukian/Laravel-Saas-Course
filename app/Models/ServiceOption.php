<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property-read string $id
 * @property-read string $name
 * @property-read int $price
 * @property-read int $duration
 * @property-read bool $active
 */
class ServiceOption extends Model
{
    protected $fillable = [
        'organization_id',
        'service_id',
        'name',
        'price',
        'duration',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];
}
