<?php

declare(strict_types=1);

namespace App\Domains\Finance\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Infrastructure\Traits\HandlesSafeDelete;

class Discount extends Model
{
    use HasFactory;
    use HandlesSafeDelete;

    protected $fillable = [
        'name',
        'type', // percentage, fixed
        'value',
        'is_active',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Scope: الخصومات النشطة فقط
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * التطبيقات لهذا الخصم
     */
    public function applications()
    {
        return $this->hasMany(DiscountApplication::class);
    }
}
