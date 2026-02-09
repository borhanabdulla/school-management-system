<?php

namespace App\Domains\Shared\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    use HasFactory;

    protected static function newFactory()
    {
        return \Database\Factories\CountryFactory::new();
    }

    protected $fillable = ['name_ar', 'name_en', 'code', 'phone_code'];
}
