<?php

namespace App\Domains\HR\Payroll\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContractItem extends Model
{
    use \Illuminate\Database\Eloquent\Factories\HasFactory;

    protected $fillable = [
        'contract_id',
        'name',
        'amount',
        'type',
        'is_one_time',
        'consumed_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'is_one_time' => 'boolean',
        'consumed_at' => 'datetime',
    ];

    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }

    public function scopeActive($query)
    {
        // For one-time items, they are active if NOT consumed.
        // For recurring items, they are always active (as long as contract is active).
        return $query->where(function ($q) {
            $q->where('is_one_time', false)
                ->orWhereNull('consumed_at');
        });
    }
}
