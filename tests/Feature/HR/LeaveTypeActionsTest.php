<?php

namespace Tests\Feature\HR;

use App\Domains\HR\Leave\Actions\CreateLeaveTypeAction;
use App\Domains\HR\Leave\Actions\UpdateLeaveTypeAction;
use App\Domains\HR\Leave\Actions\DeleteLeaveTypeAction;
use App\Domains\HR\Leave\Models\LeaveType;
use App\Domains\HR\Leave\Services\LeaveLookupService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class LeaveTypeActionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_leave_type_clears_cache(): void
    {
        Cache::put(LeaveLookupService::CACHE_KEY_LEAVE_TYPES, ['cached'], 3600);

        $data = [
            'name' => 'Annual Leave',
            'days_per_year' => 20,
            'excludes_holidays' => true,
            'requires_proof' => false,
            'is_paid' => true,
            'is_active' => true,
        ];

        $type = app(CreateLeaveTypeAction::class)->execute($data);

        $this->assertDatabaseHas('leave_types', [
            'id' => $type->id,
            'name' => 'Annual Leave',
        ]);
        $this->assertNull(Cache::get(LeaveLookupService::CACHE_KEY_LEAVE_TYPES));
    }

    public function test_update_leave_type_clears_cache(): void
    {
        $type = LeaveType::factory()->create(['name' => 'Old Name']);

        Cache::put(LeaveLookupService::CACHE_KEY_LEAVE_TYPES, ['cached'], 3600);

        $data = [
            'name' => 'Updated Name',
            'days_per_year' => 15,
            'excludes_holidays' => false,
            'requires_proof' => true,
            'is_paid' => false,
            'is_active' => false,
        ];

        $updated = app(UpdateLeaveTypeAction::class)->execute($type, $data);

        $this->assertDatabaseHas('leave_types', [
            'id' => $updated->id,
            'name' => 'Updated Name',
        ]);
        $this->assertNull(Cache::get(LeaveLookupService::CACHE_KEY_LEAVE_TYPES));
    }

    public function test_delete_leave_type_clears_cache(): void
    {
        $type = LeaveType::factory()->create();

        Cache::put(LeaveLookupService::CACHE_KEY_LEAVE_TYPES, ['cached'], 3600);

        app(DeleteLeaveTypeAction::class)->execute($type);

        $this->assertDatabaseMissing('leave_types', ['id' => $type->id]);
        $this->assertNull(Cache::get(LeaveLookupService::CACHE_KEY_LEAVE_TYPES));
    }
}
