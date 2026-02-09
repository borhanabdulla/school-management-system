<?php

namespace App\Domains\Academic\Term\Events;

use App\Domains\Academic\Term\Models\Term;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TermActivated
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Term $term
    ) {
    }
}
