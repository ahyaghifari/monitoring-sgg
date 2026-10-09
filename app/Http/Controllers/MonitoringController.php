<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Pic;
use App\Models\Project;
use Illuminate\View\View;

class MonitoringController extends Controller
{
    /**
     * Tampilkan halaman utama Monitoring Project SGG (read-only).
     */
    public function index(): View
    {
        // Mengambil data project beserta relasi tugas, perusahaan, dan PIC, terbaru di atas
        $projects = Project::with(['company', 'pic', 'checklists'])
            ->orderBy('created_at', 'desc')
            ->get()
            // Ubah ke array ringan untuk view dan atribut data-project di kartu
            ->map(fn (Project $p) => [
                'id' => $p->id,
                'company_id' => $p->company_id,
                'company_name' => $p->company?->name ?? '-',
                'title' => $p->title,
                'description' => $p->description,
                'pic_id' => $p->pic_id,
                'pic_name' => $p->pic?->nama,
                'pemohon' => $p->pemohon,
                'catatan_pic' => $p->catatan_pic,
                'logo' => $p->logo,
                'urgency' => $p->urgency,
                'status' => $p->status,
                // Persentase sesuai mode (auto/manual), dihitung oleh accessor di model Project
                'progress_percent' => $p->progress_percent,
                'checklists' => $p->checklists->map(fn ($c) => [
                    'title' => $c->title,
                    'is_completed' => $c->is_completed,
                ])->values()->toArray(),
            ]);

        // Opsi dropdown filter dashboard
        $companies = Company::orderBy('name')->get(['id', 'name']);
        $pics = Pic::orderBy('nama')->get(['id', 'nama']);

        return view('monitoring', compact('projects', 'companies', 'pics'));
    }
}
