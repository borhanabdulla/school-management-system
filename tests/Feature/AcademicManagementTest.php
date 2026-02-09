<?php

use App\Domains\Shared\Models\User;
use App\Domains\Academic\AcademicYear\Models\AcademicYear;
use App\Domains\Academic\Term\Models\Term;
use App\Livewire\Academic\AcademicYearManager;
use App\Livewire\Academic\TermManager;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->admin = User::factory()->create();
    $role = Role::firstOrCreate(['name' => 'admin']);
    $this->admin->assignRole($role);
});

test('can create academic year', function () {
    Livewire::actingAs($this->admin)
        ->test(AcademicYearManager::class)
        ->call('create')
        ->set('form.name', '2025-2026')
        ->set('form.start_date', '2025-09-01')
        ->set('form.end_date', '2026-06-30')
        ->set('form.status', 'pending')
        ->call('save')
        ->assertHasNoErrors();

    expect(AcademicYear::where('name', '2025-2026')->exists())->toBeTrue();
});

test('activating a year closes others', function () {
    $year1 = AcademicYear::create([
        'name' => '2024-2025',
        'start_date' => '2024-09-01',
        'end_date' => '2025-06-30',
        'status' => 'active'
    ]);

    Livewire::actingAs($this->admin)
        ->test(AcademicYearManager::class)
        ->call('create')
        ->set('form.name', '2025-2026')
        ->set('form.start_date', '2025-09-01')
        ->set('form.end_date', '2026-06-30')
        ->set('form.status', 'active')
        ->set('form.terms', [
            ['name' => 'Term 1', 'start_date' => '2025-09-01', 'end_date' => '2026-01-01', 'order_index' => 1],
            ['name' => 'Term 2', 'start_date' => '2026-01-15', 'end_date' => '2026-06-30', 'order_index' => 2],
        ])
        ->call('save');

    expect($year1->fresh()->status->value)->toBe('active');
    expect(AcademicYear::where('name', '2025-2026')->first()->status->value)->toBe('pending');
});

test('can create term', function () {
    $year = AcademicYear::create([
        'name' => '2025-2026',
        'start_date' => '2025-09-01',
        'end_date' => '2026-06-30',
        'status' => 'active'
    ]);

    Livewire::actingAs($this->admin)
        ->test(TermManager::class)
        ->call('create')
        ->set('form.academic_year_id', $year->id)
        ->set('form.name', 'Term 1')
        ->set('form.start_date', '2025-09-01')
        ->set('form.end_date', '2025-12-31')
        ->set('form.order_index', 1)
        ->set('form.status', 'active')
        ->call('save')
        ->assertHasNoErrors();

    expect(Term::where('name', 'Term 1')->where('academic_year_id', $year->id)->exists())->toBeTrue();
});
