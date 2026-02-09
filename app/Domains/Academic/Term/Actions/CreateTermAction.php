<?php

namespace App\Domains\Academic\Term\Actions;

use App\Domains\Academic\Term\Models\Term;
use App\Domains\Academic\Term\Data\TermData;
use App\Domains\Academic\Term\Services\TermService;
use Illuminate\Support\Facades\DB;

class CreateTermAction
{
    public function execute(TermData $data): Term
    {
        return DB::transaction(function () use ($data) {
            // Create the term
            $term = Term::create($data->toArray());

            return $term;
        });
    }
}
