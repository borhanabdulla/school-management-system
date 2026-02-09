<?php

namespace App\Domains\Academic\Term\Actions;

use App\Domains\Academic\Term\Models\Term;
use App\Domains\Academic\Term\Services\TermService;
use Illuminate\Support\Facades\DB;

class DeleteTermAction
{
    public function execute(Term $term): void
    {
        DB::transaction(function () use ($term) {
            $term->delete();
        });
    }
}
