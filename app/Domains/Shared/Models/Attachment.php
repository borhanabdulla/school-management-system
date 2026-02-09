<?php

namespace App\Domains\Shared\Models;

use Illuminate\Database\Eloquent\Model;

class Attachment extends Model
{
    protected $fillable = [
        'attachable_type',
        'attachable_id',
        'document_type',
        'file_path',
        'mime_type',
        'original_name',
        'expiry_date',
        'is_verified'
    ];

    public function attachable()
    {
        return $this->morphTo();
    }
}
