<?php

namespace App\Livewire\Forms\Guardian;

use Livewire\Attributes\Rule;
use Livewire\Form;

class GuardianForm extends Form
{
    #[Rule('required|string|min:2|max:50')]
    public $first_name = '';

    #[Rule('required|string|min:2|max:50')]
    public $last_name = '';

    #[Rule('nullable|email|unique:users,email')]
    public $email = '';

    #[Rule('required|numeric|digits_between:9,15|unique:guardians,phone')]
    public $phone = '';

    #[Rule('required|string|unique:guardians,national_id')]
    public $national_id = '';

    #[Rule('nullable|exists:countries,id')]
    public $nationality_id = null;

    #[Rule('nullable|string|max:255')]
    public $employer = '';

    #[Rule('nullable|string|max:20')]
    public $work_phone = '';

    // Address Fields
    #[Rule('nullable|string|max:100')]
    public $city = '';

    #[Rule('nullable|string|max:100')]
    public $district = '';

    #[Rule('nullable|string|max:255')]
    public $street_name = '';

    public function setGuardian(?\App\Domains\Academic\Student\Models\Guardian $guardian = null): void
    {
        if (!$guardian) {
            return;
        }

        $this->first_name = $guardian->first_name;
        $this->last_name = $guardian->last_name;
        $this->national_id = $guardian->national_id;
        $this->phone = $guardian->phone;
        $this->email = $guardian->user?->email ?? '';
        $this->nationality_id = $guardian->nationality_id;
        $this->employer = $guardian->employer;
        $this->work_phone = $guardian->work_phone;

        if ($address = $guardian->addresses()->where('is_primary', true)->first()) {
            $this->city = $address->city;
            $this->district = $address->district;
            $this->street_name = $address->street_name;
        }
    }

    public function toServiceArray(): array
    {
        return [
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'national_id' => $this->national_id,
            'nationality_id' => $this->nationality_id,
            'employer' => $this->employer,
            'work_phone' => $this->work_phone,
            'address' => [
                'city' => $this->city,
                'district' => $this->district,
                'street_name' => $this->street_name,
            ],
        ];
    }
}