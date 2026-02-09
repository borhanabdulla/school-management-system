<?php

namespace Tests\Feature\Livewire\Admin\Control;

use App\Livewire\Admin\Control\ResultsViewer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class ResultsViewerTest extends TestCase
{
    use RefreshDatabase;

    public function test_results_viewer_handles_missing_session(): void
    {
        Livewire::test(ResultsViewer::class, ['sessionId' => 999999])
            ->assertSet('results', collect())
            ->assertSet('stats', [
                'total' => 0,
                'passed' => 0,
                'failed' => 0,
                'absent' => 0,
                'pass_rate' => 0,
                'average' => 0,
            ]);
    }
}
