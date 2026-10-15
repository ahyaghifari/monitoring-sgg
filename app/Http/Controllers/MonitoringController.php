<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Pic;
use App\Models\Project;
use App\Models\User;
use Illuminate\View\View;

class MonitoringController extends Controller
{
    /**
     * Tampilkan halaman utama Monitoring Project SGG.
     * Publik untuk dilihat; PIC yang login bisa mengelola tugas project miliknya.
     */
    public function index(): View
    {
        $viewer = auth()->user();

        // Mengambil data project beserta relasi tugas, perusahaan, dan PIC.
        // Project selesai tetap tampil tetapi di paling bawah; di tiap kelompok, terbaru di atas.
        $projects = Project::with(['company', 'pic', 'picKedua', 'checklists'])
            ->orderByRaw("CASE WHEN status = 'Completed' THEN 1 ELSE 0 END")
            ->orderBy('created_at', 'desc')
            ->get()
            // Ubah ke array ringan untuk view dan atribut data-project di kartu
            ->map(fn (Project $p) => self::payload($p, $viewer));

        // Opsi dropdown filter dashboard
        $companies = Company::orderBy('name')->get(['id', 'name']);
        // Data PIC lengkap untuk dropdown filter dan modal daftar tim
        $pics = Pic::withCount(['projects', 'projectsKedua'])->orderBy('nama')->get(['id', 'nama', 'foto', 'jabatan', 'divisi']);

        return view('monitoring', compact('projects', 'companies', 'pics', 'viewer'));
    }

    /**
     * Bentuk data satu project untuk kartu dan modal; dipakai juga oleh respons API tugas.
     *
     * @return array<string, mixed>
     */
    public static function payload(Project $p, ?User $viewer): array
    {
        return [
            'id' => $p->id,
            'company_id' => $p->company_id,
            'company_name' => $p->company?->name ?? '-',
            'title' => $p->title,
            'description' => $p->description,
            'pic_id' => $p->pic_id,
            'pic_name' => $p->pic?->nama,
            'pic_photo' => $p->pic?->foto,
            'pic_kedua_id' => $p->pic_kedua_id,
            'pic_kedua_name' => $p->picKedua?->nama,
            'pic_kedua_photo' => $p->picKedua?->foto,
            'pemohon' => $p->pemohon,
            'catatan_pic' => $p->catatan_pic,
            'logo' => $p->logo,
            // Hanya hex 6 digit yang diteruskan ke style inline
            'color' => preg_match('/^#[0-9a-fA-F]{6}$/', (string) $p->warna) ? $p->warna : null,
            'urgency' => $p->urgency,
            'status' => $p->status,
            // Persentase sesuai mode (auto/manual), dihitung oleh accessor di model Project
            'progress_percent' => $p->progress_percent,
            // Penentu tampilnya tombol tambah/hapus tugas; server tetap memeriksa ulang
            'can_edit' => (bool) $viewer?->canManageProject($p),
            'checklists' => $p->checklists->map(fn ($c) => [
                'id' => $c->id,
                'title' => $c->title,
                'is_completed' => $c->is_completed,
            ])->values()->toArray(),
        ];
    }
}
