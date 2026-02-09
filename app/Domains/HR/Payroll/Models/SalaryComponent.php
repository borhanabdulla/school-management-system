<?php

namespace App\Domains\HR\Payroll\Models;

use Illuminate\Database\Eloquent\Model;

class SalaryComponent extends Model
{
    use \Illuminate\Database\Eloquent\Factories\HasFactory;

    protected $fillable = ['name','type','is_percentage','percentage_value','fixed_value','is_active', ];

    protected $casts = [
        'is_percentage' => 'boolean', 'is_active' => 'boolean', 'percentage_value' => 'decimal:2', 'fixed_value' => 'decimal:2',
    ];
}
