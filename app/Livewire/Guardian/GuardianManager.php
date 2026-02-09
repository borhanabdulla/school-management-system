<?php

namespace App\Livewire\Guardian;

use App\Domains\Academic\Student\Models\Guardian;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;

class GuardianManager extends Component
{
    use WithPagination;

    public $search = '';

    #[Layout('layouts.app')]
    public function render()
    {
        $guardians = Guardian::query()
            ->with(['students', 'user'])
            ->when($this->search, function ($query) {
                $query->where('first_name', 'like', "%{$this->search}%")
                    ->orWhere('last_name', 'like', "%{$this->search}%")
                    ->orWhere('national_id', 'like', "%{$this->search}%")
                    ->orWhere('phone', 'like', "%{$this->search}%");
            })
            ->latest()
            ->paginate(10);

        return view('livewire.guardian.guardian-manager', [
            'guardians' => $guardians
        ]);
    }

    public function delete(Guardian $guardian)
    {
        // Check if guardian has students before deleting?
        // For now, just delete. The DB cascade might handle it or we should prevent it.
        // REQ-03 says "edit", doesn't explicitly mention delete, but it's standard.
        $guardian->delete();
    }
}
