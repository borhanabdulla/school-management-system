<?php

namespace App\Livewire\Student;

use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Computed;
use App\Domains\Academic\Student\Models\Guardian;
use App\Domains\Academic\ClassSection\Services\ClassSectionLookupService;
use App\Domains\Academic\Student\Actions\RegisterStudentAction;
use App\Livewire\Forms\Student\StudentRegistrationForm;
use App\Domains\Academic\Student\Data\StudentRegistrationData;
use App\Domains\Finance\Models\FeeStructure;
use App\Domains\Academic\Grade\Services\GradeLookupService;
use App\Domains\Shared\Services\SharedLookupService;
use App\Domains\Academic\Student\Services\StudentLookupService;

#[Layout('layouts.app')]
class StudentRegistration extends Component
{
    use WithFileUploads;

    public StudentRegistrationForm $form;

    public $currentStep = 1;

    // Step 2: Guardian Info (UI State)
    public $searchQuery = '';
    public $selectedGuardianId = null;
    public $isNewGuardian = false;
    public $addedGuardians = [];
    public $relationship = 'father';
    public $isFinancialSponsor = false;
    public $isEmergencyContact = false;

    // Health Info (Optional)
    public $healthConditions = []; // Array of ['condition_id' => '', 'notes' => '']

    // Step 4: Documents
    public $documents = [
        ['type' => '', 'file' => null] // Start with one empty document
    ];

    public $isTransferStudent = false;

    // Fee Preview
    public $totalFees = 0;
    public $feeDetails = [];

    #[Computed]
    public function grades()
    {
        return app(GradeLookupService::class)->getGradesList();
    }

    #[Computed]
    public function countries()
    {
        return app(SharedLookupService::class)->getCountries();
    }

    #[Computed]
    public function healthTypes()
    {
        return app(StudentLookupService::class)->getHealthConditionTypes();
    }

    public function render()
    {
        $guardians = [];
        if (strlen($this->searchQuery) > 2) {
            $guardians = Guardian::query()
                ->where(function ($q) {
                    $q->where('national_id', 'like', $this->searchQuery . '%')
                        ->orWhere('phone', 'like', '%' . $this->searchQuery . '%')
                        ->orWhere('first_name', 'like', '%' . $this->searchQuery . '%')
                        ->orWhere('last_name', 'like', '%' . $this->searchQuery . '%');
                })
                ->orderBy('first_name')
                ->limit(5)
                ->get();
        }

        $sections = [];
        if ($this->form->grade_id) {
            $activeYear = school()->activeYear();
            if ($activeYear) {
                $sections = app(ClassSectionLookupService::class)
                    ->getSections((int) $this->form->grade_id, $activeYear->id);
            }
        }

        return view('livewire.student.student-registration', [
            'guardians' => $guardians,
            'grades' => $this->grades,
            'sections' => $sections,
            'countries' => $this->countries,
            'healthTypes' => $this->healthTypes,
        ]);
    }

    public function selectGuardian($id)
    {
        $this->selectedGuardianId = $id;
        $this->isNewGuardian = false;
    }

    public function createNewGuardian()
    {
        $this->selectedGuardianId = null;
        $this->isNewGuardian = true;
        if (is_numeric($this->searchQuery)) {
            if (strlen($this->searchQuery) >= 10) {
                $this->form->guardian_national_id = $this->searchQuery;
            } else {
                $this->form->guardian_phone = $this->searchQuery;
            }
        }
    }

    public function updatedDocuments($value, $key)
    {
        // Handle array updates: documents.0.file
        $parts = explode('.', $key);
        if (count($parts) === 2 && $parts[1] === 'file') {
            $index = $parts[0];
            $file = $this->documents[$index]['file'];

            if ($file && !$this->form->validateDocument($file)) {
                $this->documents[$index]['file'] = null; // Reset if invalid
            }
        }
    }

    public function addGuardianToList()
    {
        $data = [];
        if ($this->isNewGuardian) {
            $data = [
                'guardian_first_name' => $this->form->guardian_first_name,
                'guardian_last_name' => $this->form->guardian_last_name,
                'guardian_national_id' => $this->form->guardian_national_id,
                'guardian_phone' => $this->form->guardian_phone,
                'guardian_nationality_id' => $this->form->guardian_nationality_id ?: null,
                'relationship' => $this->relationship,
            ];
        } else {
            $data = [
                'selectedGuardianId' => $this->selectedGuardianId,
                'relationship' => $this->relationship,
            ];
        }

        if (!$this->form->validateGuardianData($data, $this->isNewGuardian)) {
            return;
        }

        // Check for duplicates in the local list
        if (!$this->isNewGuardian) {
            foreach ($this->addedGuardians as $g) {
                if ($g['type'] === 'existing' && $g['id'] == $this->selectedGuardianId) {
                    $this->addError('guardian', 'ولي الأمر هذا مضاف بالفعل.');
                    return;
                }
            }
            $guardian = Guardian::find($this->selectedGuardianId);
            $displayName = $guardian->first_name . ' ' . $guardian->last_name;
        } else {
            $displayName = $this->form->guardian_first_name . ' ' . $this->form->guardian_last_name;
        }

        $this->addedGuardians[] = [
            'type' => $this->isNewGuardian ? 'new' : 'existing',
            'id' => $this->isNewGuardian ? null : $this->selectedGuardianId,
            'data' => $this->isNewGuardian ? [
                'first_name' => $this->form->guardian_first_name,
                'last_name' => $this->form->guardian_last_name,
                'national_id' => $this->form->guardian_national_id,
                'phone' => $this->form->guardian_phone,
                'nationality_id' => $this->form->guardian_nationality_id ?: null,
                'preferred_language' => $this->form->guardian_preferred_language,
            ] : null,
            'relationship' => $this->relationship,
            'is_financial_sponsor' => $this->isFinancialSponsor,
            'is_emergency_contact' => $this->isEmergencyContact,
            'display_name' => $displayName,
        ];

        $this->resetGuardianInputs();
    }

    public function removeGuardian($index)
    {
        unset($this->addedGuardians[$index]);
        $this->addedGuardians = array_values($this->addedGuardians);
    }

    private function resetGuardianInputs()
    {
        $this->selectedGuardianId = null;
        $this->isNewGuardian = false;
        $this->searchQuery = '';

        $this->form->guardian_first_name = '';
        $this->form->guardian_last_name = '';
        $this->form->guardian_national_id = '';
        $this->form->guardian_phone = '';
        $this->form->guardian_nationality_id = '';
        $this->form->guardian_preferred_language = 'ar';

        $this->relationship = 'father';
        $this->isFinancialSponsor = false;
        $this->isEmergencyContact = false;
    }

    public function addHealthCondition()
    {
        $this->healthConditions[] = ['condition_id' => '', 'notes' => ''];
    }

    public function removeHealthCondition($index)
    {
        unset($this->healthConditions[$index]);
        $this->healthConditions = array_values($this->healthConditions);
    }

    public function addDocument()
    {
        $this->documents[] = ['type' => '', 'file' => null];
    }

    public function removeDocument($index)
    {
        unset($this->documents[$index]);
        $this->documents = array_values($this->documents);
    }

    // Financial Step State
    public $applicable_fees = [];
    public $selected_fee_types = [];
    public $discount_amount = 0;
    public $final_total = 0;
    public $create_invoice = true;

    public function updatedFormGradeId()
    {
        // Reset financials if grade changes
        $this->applicable_fees = [];
        $this->selected_fee_types = [];
        $this->calculateTotal();
    }

    public function loadFinancials()
    {
        // Get active academic year
        $academicYear = school()->activeYear();

        // Removed dump for cleanup

        if (!$academicYear || !$this->form->grade_id) {
            $this->applicable_fees = [];
            return;
        }

        $this->applicable_fees = FeeStructure::where('academic_year_id', $academicYear->id)
            ->where(function ($query) {
                $query->where('grade_id', $this->form->grade_id)
                    ->orWhereNull('grade_id');
            })
            ->with('feeType')
            ->get();
        // Removed dump for cleanup

        // Default: Select all mandatory fees (you might want to refine this logic)
        $this->selected_fee_types = $this->applicable_fees->pluck('id')->map(fn($id) => (string) $id)->toArray();

        $this->calculateTotal();
    }

    public function updatedSelectedFeeTypes()
    {
        $this->calculateTotal();
    }

    public function updatedDiscountAmount()
    {
        $this->calculateTotal();
    }

    public function calculateTotal()
    {
        if (empty($this->applicable_fees)) {
            $this->final_total = 0;
            return;
        }

        $baseTotal = $this->applicable_fees
            ->whereIn('id', $this->selected_fee_types)
            ->sum('amount');

        // Ensure discount doesn't exceed total
        if ($this->discount_amount > $baseTotal) {
            $this->discount_amount = $baseTotal;
        }

        $this->final_total = max(0, $baseTotal - $this->discount_amount);
    }

    public function nextStep()
    {
        // dd($this->currentStep);
        $this->form->validateStep($this->currentStep);

        $this->currentStep++;

        // Load financials when entering step 5
        if ($this->currentStep === 5) {
            $this->loadFinancials();
        }
    }

    public function previousStep()
    {
        if ($this->currentStep > 1) {
            $this->currentStep--;
        }
    }

    public function validateStep($step)
    {
        if ($step == 2) { // Guardian Info
            return $this->form->validateGuardians($this->addedGuardians);
        }

        return $this->form->validateStep($step, $this->isTransferStudent);
    }

    public function submit(RegisterStudentAction $action)
    {
        $this->form->validate();

        if (!$this->form->validateGuardians($this->addedGuardians)) {
            return;
        }

        if (!$this->form->validateAgeForGrade()) {
            return;
        }

        $this->form->validateStep(4, $this->isTransferStudent);

        try {
            // Create Data Object
            $data = StudentRegistrationData::fromLivewire(
                $this->form,
                $this->addedGuardians,
                $this->healthConditions,
                [
                    'city' => $this->form->city,
                    'district' => $this->form->district,
                    'street_name' => $this->form->street_name,
                    'building_number' => $this->form->building_number,
                    'google_maps_link' => $this->form->google_maps_link,
                ],
                $this->documents,
                $this->isTransferStudent,
                [
                    'school_name' => $this->form->school_name,
                    'previous_curriculum' => $this->form->previous_curriculum,
                    'last_grade_completed' => $this->form->last_grade_completed,
                    'completion_year' => $this->form->completion_year,
                    'last_gpa' => $this->form->last_gpa,
                    'reason_for_transfer' => $this->form->reason_for_transfer,
                ],
                $this->create_invoice && $this->final_total > 0
            );

            // Execute Action
            $student = $action->execute($data);

            $this->dispatch('notify', message: 'تم تسجيل الطالب بنجاح!');
            return redirect()->route('students.show', $student->id);

        } catch (\App\Domains\Academic\Student\Exceptions\DuplicateStudentException $e) {
            $this->addError('national_id', $e->getMessage());
            $this->currentStep = 1;

        } catch (\App\Domains\Academic\Student\Exceptions\InvalidStudentAgeException $e) {
            $this->addError('date_of_birth', $e->getMessage());
            $this->currentStep = 1;

        } catch (\App\Domains\Academic\ClassSection\Exceptions\ClassroomFullException $e) {
            $this->addError('class_section_id', $e->getMessage());
            $this->currentStep = 3;

        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->setErrorBag($e->validator->getMessageBag());

        } catch (\Exception $e) {
            $this->addError('general', 'حدث خطأ غير متوقع: ' . $e->getMessage());
            \Log::error('Student Registration Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
}
