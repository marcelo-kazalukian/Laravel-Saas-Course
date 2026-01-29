<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property-read int $id
 * @property-read string $name
 * @property-read string $email
 * @property-read string|null $phone
 * @property-read int $organization_id
 */
final class Customer extends Model
{
    protected $fillable = [
        'organization_id',
        'name',
        'email',
        'phone',
    ];
}
