<?php

namespace App\Domains\HR\Staff\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobPosition extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'description', 'name', 'is_teaching_position'];
    //  protected $guarded = []; 

    // protected $fillable = ['title', 'description','name','is_teaching_position'];
    //  protected $guarded = []; 
}
