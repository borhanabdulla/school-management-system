<?php

namespace App\Domains\Finance\Models;

use Illuminate\Database\Eloquent\Model;
use App\Domains\Academic\AcademicYear\Models\AcademicYear;
use App\Domains\Shared\Models\User;

class FinancialClosingLog extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'financial_closing_logs';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'academic_year_id',
        'closed_by',
        'closed_at',
        'reason',
        'report_snapshot',
        'report_version',
        'snapshot_hash',
        'metadata',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'closed_at' => 'datetime',
        'report_snapshot' => 'array',
        'metadata' => 'array',
    ];

    /**
     * Get the academic year that owns the log.
     */
    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    /**
     * Get the user who closed the year.
     */
    public function closer()
    {
        return $this->belongsTo(User::class, 'closed_by');
    }
}
