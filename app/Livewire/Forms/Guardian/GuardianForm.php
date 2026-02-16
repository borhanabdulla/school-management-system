<?php

namespace App\Livewire\Forms\Guardian;

use Livewire\Attributes\Rule;
use Livewire\Form;

class GuardianForm extends Form
{
    public $first_name = '';
    public $last_name = '';
    public $email = '';
    public $phone = '';
    public $national_id = '';
    public $nationality_id = null;
    public $employer = '';
    public $work_phone = '';

    // Address Fields
    public $city = '';
    public $district = '';
    public $street_name = '';

    public ?int $guardianId = null;
    public ?int $userId = null;

    public function rules()
    {
        return [
            'first_name' => 'required|string|min:2|max:50',
            'last_name' => 'required|string|min:2|max:50',
            'email' => 'nullable|email|unique:users,email' . ($this->userId ? ',' . $this->userId : ''),
            'phone' => 'required|numeric|digits_between:9,15|unique:guardians,phone' . ($this->guardianId ? ',' . $this->guardianId : ''),
            'national_id' => 'required|string|unique:guardians,national_id' . ($this->guardianId ? ',' . $this->guardianId : ''),
            'nationality_id' => 'nullable|exists:countries,id',
            'employer' => 'nullable|string|max:255',
            'work_phone' => 'nullable|string|max:20',
            'city' => 'nullable|string|max:100',
            'district' => 'nullable|string|max:100',
            'street_name' => 'nullable|string|max:255',
        ];
    }

    public function setGuardian(?\App\Domains\Academic\Student\Models\Guardian $guardian = null): void
    {
        if (!$guardian) {
            return;
        }

        $this->guardianId = $guardian->id;
        $this->userId = $guardian->user_id;
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