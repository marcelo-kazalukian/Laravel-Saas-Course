<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property-read string $id
 * @property-read string $organization_id
 * @property-read string $name
 * @property-read string|null $description
 */
class ServiceCategory extends Model
{
    protected $fillable = ['name', 'description', 'organization_id'];
}
