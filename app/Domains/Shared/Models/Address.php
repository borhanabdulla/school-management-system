<?php

namespace App\Domains\Shared\Models;

use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    protected $fillable = [
        'addressable_type',
        'addressable_id',
        'city',
        'district',
        'street_name',
        'building_number',
        'national_address_code',
        'latitude',
        'longitude',
        'is_primary'
    ];

    public function addressable()
    {
        return $this->morphTo();
    }
}
