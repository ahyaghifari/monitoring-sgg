<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectChecklist;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

// PIC menambah, mencentang, dan menghapus tugas project miliknya dari halaman beranda
class ProjectTaskController extends Controller
{
    public function store(Request $request, Project $project): JsonResponse
    {
        $this->authorizeProject($request, $project);

        $data = $request->validate(['title' => ['required', 'string', 'max:255']]);

        $project->checklists()->create(['title' => trim($data['title']), 'is_completed' => false]);

        return $this->respond($request, $project);
    }

    public function update(Request $request, ProjectChecklist $checklist): JsonResponse
    {
        $this->authorizeProject($request, $checklist->project);

        $data = $request->validate(['is_completed' => ['required', 'boolean']]);

        $checklist->update(['is_completed' => $data['is_completed']]);

        return $this->respond($request, $checklist->project);
    }

    public function destroy(Request $request, ProjectChecklist $checklist): JsonResponse
    {
        $project = $checklist->project;
        $this->authorizeProject($request, $project);

        $checklist->delete();

        return $this->respond($request, $project);
    }

    private function authorizeProject(Request $request, Project $project): void
    {
        abort_unless($request->user()->canManageProject($project), 403, 'Anda tidak berhak mengubah project ini.');
    }

    // Kembalikan data project terbaru agar kartu dan modal bisa diperbarui tanpa reload
    private function respond(Request $request, Project $project): JsonResponse
    {
        $project = $project->fresh(['company', 'pic', 'picKedua', 'checklists']);

        return response()->json(['project' => MonitoringController::payload($project, $request->user())]);
    }
}
