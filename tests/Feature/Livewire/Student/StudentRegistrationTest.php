<?php

namespace Tests\Feature\Livewire\Student;

use App\Livewire\Student\StudentRegistration;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class StudentRegistrationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_validates_step_1_fields()
    {
        Livewire::test(StudentRegistration::class)
            ->set('form.first_name_ar', '') // Invalid
            ->call('nextStep')
            ->assertHasErrors(['form.first_name_ar'])
            ->assertSet('currentStep', 1); // Should not advance
    }

    /** @test */
    public function it_advances_to_step_2_with_valid_data()
    {
        Livewire::test(StudentRegistration::class)
            ->set('form.first_name_ar', 'أحمد')
            ->set('form.family_name_ar', 'محمد')
            ->set('form.date_of_birth', '2015-01-01')
            ->set('form.gender', 'male')
            ->set('form.nationality_id', null) // nullable
            ->set('form.blood_type', null) // nullable
            ->set('form.national_id', null) // nullable
            ->call('nextStep')
            ->assertHasNoErrors()
            ->assertSet('currentStep', 2);
    }
}
