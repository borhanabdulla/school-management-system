<?php

namespace Tests\Feature\Student;

use App\Domains\Academic\Student\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentModelTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_access_full_name_attributes()
    {
        $student = Student::factory()->create([
            'first_name_ar' => 'أحمد',
            'family_name_ar' => 'محمد',
            'first_name_en' => 'Ahmed',
            'family_name_en' => 'Mohamed',
        ]);

        $this->assertEquals('أحمد محمد', $student->full_name_ar);
        $this->assertEquals('Ahmed Mohamed', $student->full_name_en);
    }

    /** @test */
    public function it_can_access_email_via_user_relationship()
    {
        $user = User::factory()->create(['email' => 'student@example.com']);
        $student = Student::factory()->create(['user_id' => $user->id]);

        $this->assertEquals('student@example.com', $student->email);
    }

    /** @test */
    public function it_can_search_students()
    {
        $student1 = Student::factory()->create(['first_name_ar' => 'أحمد', 'admission_number' => '12345']);
        $student2 = Student::factory()->create(['first_name_ar' => 'محمد', 'admission_number' => '67890']);

        $results = Student::search('أحمد')->get();
        $this->assertCount(1, $results);
        $this->assertEquals($student1->id, $results->first()->id);

        $results = Student::search('12345')->get();
        $this->assertCount(1, $results);
        $this->assertEquals($student1->id, $results->first()->id);
    }
}
