<?php

namespace App\Domains\HR\Shared\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class HrAmendment extends Model
{
    protected $fillable = [
        'staff_id',
        'amendable_type',
        'amendable_id',
        'kind',
        'reason',
        'payload',
        'status',
        'requested_by',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'approved_at' => 'datetime',
    ];

    public function amendable(): MorphTo
    {
        return $this->morphTo();
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(\App\Domains\HR\Staff\Models\Staff::class);
    }

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'requested_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'approved_by');
    }
}
