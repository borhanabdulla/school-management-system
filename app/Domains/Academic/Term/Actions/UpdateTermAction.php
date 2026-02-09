<?php

namespace App\Domains\Academic\Term\Actions;

use App\Domains\Academic\Term\Models\Term;
use App\Domains\Academic\Term\Data\TermData;
use App\Domains\Academic\Term\Services\TermService;
use Illuminate\Support\Facades\DB;

class UpdateTermAction
{
    public function execute(Term $term, TermData $data): Term
    {
        return DB::transaction(function () use ($term, $data) {
            $term->update($data->toArray());
            return $term;
        });
    }
}
