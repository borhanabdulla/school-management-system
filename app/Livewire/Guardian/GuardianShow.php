<?php

namespace App\Livewire\Guardian;

use App\Domains\Academic\Student\Models\Guardian;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('layouts.app')]
#[Title('عرض ولي الأمر')]
class GuardianShow extends Component
{
    public Guardian $guardian;

    // Link Student Modal State
    public $showLinkModal = false;
    public $searchQuery = '';
    public $selectedStudentId = null;

    // Pivot Data
    public $relationship = 'father';
    public $isEmergencyContact = false;
    public $isFinancialSponsor = false;
    public $livesWith = true;
    public $hasPortalAccess = true;

    protected ?\App\Domains\Academic\Student\Services\GuardianLookupService $guardianLookup = null;
    protected ?\App\Domains\Academic\Student\Services\StudentLookupService $studentLookup = null;

    protected function guardianLookup(): \App\Domains\Academic\Student\Services\GuardianLookupService
    {
        return $this->guardianLookup ??= app(\App\Domains\Academic\Student\Services\GuardianLookupService::class);
    }

    protected function studentLookup(): \App\Domains\Academic\Student\Services\StudentLookupService
    {
        return $this->studentLookup ??= app(\App\Domains\Academic\Student\Services\StudentLookupService::class);
    }

    public function mount($id)
    {
        // ⚡ Performance Optimization: Eager load relationships to avoid N+1 queries
        $this->guardian = $this->guardianLookup()->findForShow($id);
    }

    // ⚡ Performance Optimization: Computed property for search results
    // This ensures we only query when necessary and keeps the component lightweight
    public function getSearchResultsProperty()
    {
        if (strlen($this->searchQuery) < 2) {
            return [];
        }

        return $this->studentLookup()->searchStudentsForGuardianLink(
            query: $this->searchQuery,
            excludeGuardianId: $this->guardian->id
        );
    }

    public function openLinkModal()
    {
        $this->reset(['searchQuery', 'selectedStudentId', 'relationship', 'isEmergencyContact', 'isFinancialSponsor']);
        $this->dispatch('open-modal', 'link-student');
    }

    public function selectStudent($studentId)
    {
        $this->selectedStudentId = $studentId;
    }

    public function linkStudent()
    {
        $this->validate([
            'selectedStudentId' => 'required|exists:students,id',
            'relationship' => 'required|in:father,mother,brother,uncle,other',
        ]);

        $this->guardian->students()->attach($this->selectedStudentId, [
            'relationship' => $this->relationship,
            'is_emergency_contact' => $this->isEmergencyContact,
            'is_financial_sponsor' => $this->isFinancialSponsor,
            'lives_with' => $this->livesWith,
            'has_portal_access' => $this->hasPortalAccess,
        ]);

        $this->dispatch('close-modal', 'link-student');
        $this->dispatch('notify', message: 'تم ربط الطالب بنجاح', type: 'success');

        // Refresh the guardian data
        $this->guardian->refresh();
    }

    public function unlinkStudent($studentId)
    {
        $this->guardian->students()->detach($studentId);
        $this->dispatch('notify', message: 'تم فك ارتباط الطالب بنجاح', type: 'success');

        // Refresh the guardian data
        $this->guardian->refresh();
    }

    public function render()
    {
        return view('livewire.guardian.guardian-show');
    }
}
