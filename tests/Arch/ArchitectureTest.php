<?php

use App\Domains\Academic\Student\Models\Student;
use App\Domains\Academic\Student\Services\StudentLookupService;

test('UI layers should not use Student model directly')
    ->expect('App\Domains\Academic\Student\Models\Student')
    ->not->toBeUsedIn('App\Livewire')
    ->not->toBeUsedIn('App\Http\Controllers');

// Specific rule: clearCache should not be called from UI
// (Read-only lookups are acceptable for Livewire components)
test('UI layers should not call clearCache')
    ->expect('App\Livewire')
    ->not->toUse('StudentLookupService::clearCache');

test('Academic domains should not use hardcoded status strings')
    ->expect('App\Domains\Academic')
    ->not->toUse("where('status', 'active')")
    ->not->toUse("where('status', 'completed')")
    ->not->toUse("where('status', 'absent')");
