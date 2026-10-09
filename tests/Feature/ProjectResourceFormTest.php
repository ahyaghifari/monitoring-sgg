<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Filament\Resources\ProjectResource\Pages\ListProjects;
use App\Models\Company;
use App\Models\Project;
use App\Models\User;
use Filament\Actions\EditAction;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ProjectResourceFormTest extends TestCase
{
    use RefreshDatabase;

    private const RANGE_MESSAGE = 'Nilai persentase harus berada di antara 0 hingga 100. Silakan sesuaikan kembali nilai isian Anda.';

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create(['role' => UserRole::SUPER_ADMIN]));
    }

    private function makeProject(array $attributes = []): Project
    {
        return Project::create([
            'company_id' => Company::create(['name' => 'SGG'])->id,
            'title' => 'Website',
            'urgency' => 'Medium',
            'status' => 'In Progress',
            ...$attributes,
        ]);
    }

    public function test_edit_form_adds_checklist_tasks_and_saves_single_manual_percentage(): void
    {
        $project = $this->makeProject();
        $edit = TestAction::make(EditAction::class)->table($project);

        Livewire::test(ListProjects::class)
            ->callAction($edit, ['checklists' => [['title' => 'Tugas baru', 'is_completed' => false]]])
            ->assertHasNoFormErrors();

        $this->assertSame(['Tugas baru'], $project->checklists()->pluck('title')->all());

        Livewire::test(ListProjects::class)
            ->callAction($edit, ['progress_mode' => 'manual', 'manual_progress' => 101])
            ->assertHasFormErrors(['manual_progress' => self::RANGE_MESSAGE]);

        $this->assertSame('auto', $project->fresh()->progress_mode);

        Livewire::test(ListProjects::class)
            ->callAction($edit, ['progress_mode' => 'manual', 'manual_progress' => 45])
            ->assertHasNoFormErrors();

        $project = $project->fresh();
        $this->assertSame('manual', $project->progress_mode);
        $this->assertSame(45, $project->progress_percent);
    }

    public function test_manage_tasks_modal_saves_manual_percentage_only_within_0_to_100(): void
    {
        $project = $this->makeProject(['progress_mode' => 'manual', 'manual_progress' => 30]);
        $manageTasks = TestAction::make('manageTasks')->table($project);

        foreach ([101, -1] as $invalid) {
            Livewire::test(ListProjects::class)
                ->callAction($manageTasks, ['manual_progress' => $invalid])
                ->assertHasFormErrors(['manual_progress' => self::RANGE_MESSAGE]);
        }

        $this->assertSame(30, $project->fresh()->progress_percent);

        Livewire::test(ListProjects::class)
            ->mountAction($manageTasks)
            ->assertSchemaStateSet(['progress_mode' => 'manual', 'manual_progress' => 30])
            ->fillForm(['manual_progress' => 100])
            ->callMountedAction()
            ->assertHasNoFormErrors();

        $project = $project->fresh();
        $this->assertSame(100, $project->progress_percent);
        $this->assertSame('Completed', $project->status);
    }

    public function test_manage_tasks_modal_switches_mode_and_keeps_manual_value(): void
    {
        $project = $this->makeProject(['manual_progress' => 30]);
        $manageTasks = TestAction::make('manageTasks')->table($project);

        // Mode awal mengikuti project (auto): checklist tampil, input manual tidak
        Livewire::test(ListProjects::class)
            ->mountAction($manageTasks)
            ->assertSchemaStateSet(['progress_mode' => 'auto'])
            ->assertMountedActionModalSee('Checklist Task')
            ->assertMountedActionModalDontSee('Persentase Progress (%)');

        Livewire::test(ListProjects::class)
            ->callAction($manageTasks, ['progress_mode' => 'manual'])
            ->assertHasNoFormErrors();

        $project = $project->fresh();
        $this->assertSame('manual', $project->progress_mode);
        $this->assertSame(30, $project->progress_percent);

        // Kembali ke auto: nilai manual tidak terhapus
        Livewire::test(ListProjects::class)
            ->callAction($manageTasks, ['progress_mode' => 'auto'])
            ->assertHasNoFormErrors();

        $project = $project->fresh();
        $this->assertSame('auto', $project->progress_mode);
        $this->assertSame(30, $project->manual_progress);
    }

    /**
     * @return array<string, array{0: string|class-string}>
     */
    public static function projectActions(): array
    {
        return [
            'form Ubah' => [EditAction::class],
            'modal Kelola Tugas' => ['manageTasks'],
        ];
    }

    #[DataProvider('projectActions')]
    public function test_each_mode_shows_only_its_own_field_and_switching_keeps_saved_data(string $actionName): void
    {
        $project = $this->makeProject(['manual_progress' => 30]);
        $project->checklists()->createMany([
            ['title' => 'Desain UI', 'is_completed' => true],
            ['title' => 'Setup server', 'is_completed' => false],
        ]);
        $action = TestAction::make($actionName)->table($project);

        // Auto -> Manual: checklist tersembunyi, hanya input persentase
        Livewire::test(ListProjects::class)
            ->mountAction($action)
            ->assertMountedActionModalSee('Checklist Task')
            ->assertMountedActionModalDontSee('Persentase Progress (%)')
            ->fillForm(['progress_mode' => 'manual'])
            ->assertMountedActionModalSee('Persentase Progress (%)')
            ->assertMountedActionModalDontSee('Checklist Task')
            ->fillForm(['manual_progress' => 40])
            ->callMountedAction()
            ->assertHasNoFormErrors();

        $project = $project->fresh();
        $this->assertSame('manual', $project->progress_mode);
        $this->assertSame(40, $project->progress_percent);
        $this->assertSame(['Desain UI', 'Setup server'], $project->checklists()->pluck('title')->all());

        // Manual -> Auto: angka manual tetap tersimpan, progress kembali dari checklist
        Livewire::test(ListProjects::class)
            ->callAction($action, ['progress_mode' => 'auto'])
            ->assertHasNoFormErrors();

        $project = $project->fresh();
        $this->assertSame('auto', $project->progress_mode);
        $this->assertSame(40, $project->manual_progress);
        $this->assertSame(50, $project->progress_percent);
        $this->assertCount(2, $project->checklists);
    }
}
