<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Company;
use App\Models\Pic;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectTaskReorderTest extends TestCase
{
    use RefreshDatabase;

    private function makeProjectOwnedBy(User $user): Project
    {
        $pic = Pic::create(['user_id' => $user->id, 'nama' => 'Budi']);

        $project = Project::create([
            'company_id' => Company::create(['name' => 'SGG'])->id,
            'title' => 'Website',
            'pic_id' => $pic->id,
            'urgency' => 'Medium',
            'status' => 'In Progress',
        ]);
        $project->checklists()->createMany([
            ['title' => 'Desain UI', 'is_completed' => false],
            ['title' => 'Setup server', 'is_completed' => false],
            ['title' => 'Deploy', 'is_completed' => false],
        ]);

        return $project;
    }

    public function test_new_tasks_are_appended_to_the_end_of_the_list(): void
    {
        $project = $this->makeProjectOwnedBy(User::factory()->create(['role' => UserRole::PIC]));

        $this->assertSame(['Desain UI', 'Setup server', 'Deploy'], $project->checklists()->pluck('title')->all());
        $this->assertSame([1, 2, 3], $project->checklists()->pluck('sort_order')->all());
    }

    public function test_pic_can_reorder_tasks_of_own_project(): void
    {
        $user = User::factory()->create(['role' => UserRole::PIC]);
        $project = $this->makeProjectOwnedBy($user);
        [$desain, $server, $deploy] = $project->checklists()->pluck('id')->all();

        $this->actingAs($user)
            ->putJson(route('tasks.reorder', $project), ['ids' => [$deploy, $desain, $server]])
            ->assertOk()
            ->assertJsonPath('project.checklists.0.title', 'Deploy')
            ->assertJsonPath('project.checklists.2.title', 'Setup server');

        $this->assertSame(['Deploy', 'Desain UI', 'Setup server'], $project->checklists()->pluck('title')->all());
    }

    public function test_reorder_rejects_ids_that_do_not_match_project_tasks(): void
    {
        $user = User::factory()->create(['role' => UserRole::PIC]);
        $project = $this->makeProjectOwnedBy($user);
        [$desain, $server] = $project->checklists()->pluck('id')->all();

        $this->actingAs($user)
            ->putJson(route('tasks.reorder', $project), ['ids' => [$server, $desain]])
            ->assertStatus(422);

        $this->assertSame(['Desain UI', 'Setup server', 'Deploy'], $project->checklists()->pluck('title')->all());
    }

    public function test_other_pic_cannot_reorder_tasks(): void
    {
        $project = $this->makeProjectOwnedBy(User::factory()->create(['role' => UserRole::PIC]));
        $ids = $project->checklists()->pluck('id')->reverse()->values()->all();

        $this->putJson(route('tasks.reorder', $project), ['ids' => $ids])->assertUnauthorized();

        $otherUser = User::factory()->create(['role' => UserRole::PIC]);
        Pic::create(['user_id' => $otherUser->id, 'nama' => 'Sari']);

        $this->actingAs($otherUser)
            ->putJson(route('tasks.reorder', $project), ['ids' => $ids])
            ->assertForbidden();
    }
}
