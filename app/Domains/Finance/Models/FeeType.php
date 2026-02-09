<?php

namespace App\Domains\Finance\Models;

use Illuminate\Database\Eloquent\Model;
use App\Infrastructure\Traits\HandlesSafeDelete;
use App\Infrastructure\Traits\HasModelLabels;

class FeeType extends Model
{
    use HandlesSafeDelete, HasModelLabels;

    protected $fillable = ['name', 'is_recurring', 'is_tuition'];

    protected static string $modelLabel = 'نوع رسوم';
    protected static string $modelPluralLabel = 'أنواع الرسوم';
}
