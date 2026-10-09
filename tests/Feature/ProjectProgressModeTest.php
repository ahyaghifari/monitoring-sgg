<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectProgressModeTest extends TestCase
{
    use RefreshDatabase;

    private function makeProject(array $attributes = []): Project
    {
        $project = Project::create([
            'company_id' => Company::create(['name' => 'SGG'])->id,
            'title' => 'Website',
            'urgency' => 'Medium',
            'status' => 'In Progress',
            ...$attributes,
        ]);
        $project->checklists()->createMany([
            ['title' => 'Desain UI', 'is_completed' => true],
            ['title' => 'Setup server', 'is_completed' => false],
        ]);

        return $project;
    }

    public function test_checking_task_in_auto_mode_changes_progress_percent(): void
    {
        $project = $this->makeProject();
        $this->assertSame(50, $project->fresh()->progress_percent);

        $project->checklists()->where('title', 'Setup server')->first()->update(['is_completed' => true]);

        $project = $project->fresh();
        $this->assertSame(100, $project->progress_percent);
        $this->assertSame('Completed', $project->status);
    }

    public function test_checking_task_in_manual_mode_does_not_change_progress_percent(): void
    {
        $project = $this->makeProject(['progress_mode' => 'manual', 'manual_progress' => 30]);
        $this->assertSame(30, $project->fresh()->progress_percent);

        $project->checklists()->where('title', 'Setup server')->first()->update(['is_completed' => true]);
        $project->checklists()->create(['title' => 'Deploy', 'is_completed' => true]);

        $project = $project->fresh();
        $this->assertSame(30, $project->progress_percent);
        $this->assertSame('In Progress', $project->status);
        $this->assertCount(3, $project->checklists);
    }

    public function test_public_detail_modal_shows_progress_bar_and_task_list_without_tabs(): void
    {
        $this->makeProject(['progress_mode' => 'manual', 'manual_progress' => 30]);

        $this->get(route('monitoring'))
            ->assertOk()
            ->assertSeeHtml('id="detail-progress-fill"')
            ->assertSeeHtml('id="detail-checklist-container"')
            ->assertDontSeeHtml('sgg-modal-tab')
            // Data kartu untuk modal: progress aktif (manual) + daftar tugas
            ->assertSee('"progress_percent":30', false)
            ->assertSee('Setup server');
    }
}
