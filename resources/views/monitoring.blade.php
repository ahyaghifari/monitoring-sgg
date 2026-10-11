<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monitoring Project | Syifa Global Group</title>
    <meta name="description" content="Sistem Monitoring Project Syifa Global Group (SGG). Pantau progress proyek secara real-time.">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Geist:wght@400;500;600;700;800&family=Geist+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}?v={{ filemtime(public_path('css/app.css')) }}">
    {{-- Terapkan tema tersimpan sebelum render agar tidak berkedip --}}
    <script>try{var t=localStorage.getItem('sgg-theme');if(t)document.documentElement.dataset.theme=t;}catch(e){}</script>
</head>
<body class="monitoring-page-wrapper" data-base="{{ url('/') }}">
    {{-- Latar belakang: grid, lantai perspektif, glow hijau/pink (CSS di public/css/app.css) --}}
    <x-tech-background />

    <main class="sgg-main" id="app-main">
        {{-- Ringkasan: dihitung dari data project yang sama dengan daftar --}}
        @php
            $total = $projects->count();
            $done = $projects->where('status', 'Completed')->count();
            $avg = $total > 0 ? (int) round($projects->avg('progress_percent')) : 0;
        @endphp

        <section class="sgg-stage">
            {{-- Monitor layar penuh: navigasi (tab) dan daftar project ada di dalam layar --}}
            <div class="sgg-monitor" id="monitor">
                <div class="sgg-monitor-frame">
                    <span class="sgg-hud sgg-hud--tl" aria-hidden="true"></span>
                    <span class="sgg-hud sgg-hud--tr" aria-hidden="true"></span>
                    <span class="sgg-hud sgg-hud--bl" aria-hidden="true"></span>
                    <span class="sgg-hud sgg-hud--br" aria-hidden="true"></span>

                    <div class="sgg-screen">
                        {{-- Title bar: logo + judul + status live --}}
                        <div class="sgg-screen-bar">
                            {{-- Lingkaran dekoratif: di desktop tampil di sini, di mobile pindah ke bar bawah --}}
                            <span class="sgg-screen-dots" aria-hidden="true"><i></i><i></i><i></i></span>
                            {{-- Logo sistem disembunyikan sementara lewat CSS (.sgg-logo-img) --}}
                            <img src="{{ asset('images/logo-sgg.svg') }}" alt="Syifa Global Group Logo" class="sgg-logo-img">
                            <div class="sgg-logo-text">
                                <h1 class="brand">Syifa Global Group</h1>
                                <span class="sub">Monitoring Project</span>
                            </div>
                            <span class="sgg-live" title="Data diperbarui otomatis tiap 10 detik">
                                <span class="sgg-live-dot"></span>Live
                            </span>
                            <button type="button" class="sgg-theme sgg-team-btn" id="pic-open" aria-haspopup="dialog">PIC</button>
                            {{-- Tombol tema berupa ikon: bulan = pindah ke gelap, matahari = pindah ke terang (diatur app.js) --}}
                            <button type="button" class="sgg-theme sgg-theme--icon" id="theme-toggle" data-current="light" aria-label="Ganti ke mode gelap" title="Ganti ke mode gelap">
                                <svg class="ico-moon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.72 9.72 0 0 1 18 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 0 0 3 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 0 0 9.002-5.998Z"/>
                                </svg>
                                <svg class="ico-sun" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386-1.591 1.591M21 12h-2.25m-.386 6.364-1.591-1.591M12 18.75V21m-4.773-4.227-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z"/>
                                </svg>
                            </button>
                            <span class="sgg-screen-progress" aria-hidden="true"></span>
                        </div>

                        {{-- Teks berjalan (satu-satunya marquee di halaman) --}}
                        <div class="sgg-marquee" aria-hidden="true">
                            <div class="sgg-marquee-track">
                                @for($i = 0; $i < 8; $i++)
                                    <span>Syifa Global Group</span><i></i><span>Monitoring Project</span><i></i><span>Progress Real-time</span><i></i><span>Satu Layar</span><i></i>
                                @endfor
                            </div>
                        </div>

                        @if(count($projects) > 0)
                            {{-- Tab ringkasan sekaligus filter status (logika di app.js) --}}
                            <div class="sgg-tabs" role="tablist" aria-label="Filter status project">
                                <button type="button" role="tab" class="sgg-tab is-active" data-status="" aria-selected="true">
                                    <span>Semua</span><b class="sgg-tab-count" data-count="all">{{ $total }}</b>
                                </button>
                                <button type="button" role="tab" class="sgg-tab" data-status="In Progress" aria-selected="false">
                                    <span>Dalam Proses</span><b class="sgg-tab-count" data-count="progress">{{ $total - $done }}</b>
                                </button>
                                <button type="button" role="tab" class="sgg-tab" data-status="Completed" aria-selected="false">
                                    <span>Selesai</span><b class="sgg-tab-count" data-count="done">{{ $done }}</b>
                                </button>
                                <div class="sgg-tab sgg-tab--stat">
                                    <span>Rata-rata</span><b class="sgg-tab-count is-pink" data-count="avg">{{ $avg }}%</b>
                                </div>
                            </div>
                        @endif

                        <div class="sgg-screen-scroll">
                            {{-- Daftar kartu project: 1 baris 1 project --}}
                            <div class="sgg-project-list" id="project-list">
                                @foreach($projects as $index => $project)
                                    @php
                                        [$urgencyClass, $urgencyLabel] = match($project['urgency']) {
                                            'Medium' => ['sgg-badge--medium', 'Sedang'],
                                            'High'   => ['sgg-badge--high', 'Tinggi'],
                                            'Urgent' => ['sgg-badge--urgent', 'Darurat'],
                                            default  => ['sgg-badge--low', 'Rendah'],
                                        };
                                        $statusClass = $project['status'] === 'Completed' ? 'sgg-badge--completed' : 'sgg-badge--progress';
                                        $statusLabel = $project['status'] === 'Completed' ? 'Selesai' : 'Dalam Proses';
                                    @endphp
                                    <div class="sgg-project-card{{ $project['color'] ? ' has-color' : '' }}{{ $project['pic_photo'] ? ' has-photo' : '' }}"
                                         @if($project['color']) style="--project-color: {{ $project['color'] }}" @endif
                                         role="button" tabindex="0"
                                         data-project='@json($project)'
                                         data-company="{{ $project['company_id'] }}"
                                         data-pic="{{ $project['pic_id'] }}"
                                         data-status="{{ $project['status'] }}">
                                        {{-- Foto PIC besar sebagai latar di sisi kanan kartu (dekoratif; nama PIC sudah tertulis) --}}
                                        @if($project['pic_photo'])
                                            <img src="{{ asset('storage/' . $project['pic_photo']) }}" alt="" class="sgg-card-bgphoto" aria-hidden="true" loading="lazy">
                                        @elseif($project['pic_name'])
                                            <span class="sgg-card-bgphoto sgg-card-bgphoto--initial" aria-hidden="true">{{ mb_strtoupper(mb_substr($project['pic_name'], 0, 1)) }}</span>
                                        @endif
                                        {{-- Keterangan PIC: desktop di bawah foto kolom kiri, mobile di bawah avatar kanan atas --}}
                                        @if($project['pic_name'])
                                            <span class="sgg-card-piccap" aria-hidden="true"><b>PIC</b><span>{{ $project['pic_name'] }}</span></span>
                                        @endif
                                        {{-- 1. Logo, perusahaan, dan nama project --}}
                                        <div class="sgg-card-info">
                                            @if($project['logo'])
                                                <img src="{{ asset('storage/' . $project['logo']) }}"
                                                     alt="Logo {{ $project['title'] }}"
                                                     class="sgg-card-logo">
                                            @endif
                                            <div class="sgg-card-text">
                                                <div class="sgg-card-company">{{ $project['company_name'] }}</div>
                                                <div class="sgg-card-title">{{ $project['title'] }}</div>
                                            </div>
                                        </div>
                                        <div class="sgg-card-body">
                                            @if($project['description'])
                                                <p class="sgg-card-desc">{{ $project['description'] }}</p>
                                            @endif
                                            @if($project['pic_name'])
                                                <div class="sgg-card-meta">
                                                    @if($project['pic_name'])
                                                        <span class="sgg-pic">
                                                            <span class="sgg-pic-text">
                                                                <span class="sgg-pic-line"><strong>PIC</strong> {{ $project['pic_name'] }}</span>
                                                                {{-- Catatan PIC tepat di bawah nama PIC, dikutip dan miring --}}
                                                                @if(!empty($project['catatan_pic']))
                                                                    <em class="sgg-pic-note">“{{ $project['catatan_pic'] }}”</em>
                                                                @endif
                                                            </span>
                                                        </span>
                                                    @endif
                                                </div>
                                            @endif
                                            {{-- Catatan tanpa PIC: tetap ditampilkan, tanpa label --}}
                                            @if(!empty($project['catatan_pic']) && !$project['pic_name'])
                                                <p class="sgg-pic-note sgg-pic-note--solo"><em>“{{ $project['catatan_pic'] }}”</em></p>
                                            @endif
                                        </div>
                                        {{-- Badge urgensi dan status --}}
                                        <div class="sgg-card-badges">
                                            <span class="sgg-badge {{ $urgencyClass }}"><span class="sgg-badge-dot"></span>{{ $urgencyLabel }}</span>
                                            <span class="sgg-badge {{ $statusClass }}"><span class="sgg-badge-dot"></span>{{ $statusLabel }}</span>
                                            {{-- Mobile: hanya avatar PIC di samping status; nama ada di tooltip dan untuk pembaca layar --}}
                                            @if($project['pic_name'])
                                                <span class="sgg-card-picinline" title="PIC: {{ $project['pic_name'] }}">
                                                    <span class="sgg-card-picsep" aria-hidden="true">|</span>
                                                    @if($project['pic_photo'])
                                                        <img src="{{ asset('storage/' . $project['pic_photo']) }}" alt="" class="sgg-card-picavatar" loading="lazy">
                                                    @else
                                                        <span class="sgg-card-picavatar sgg-card-picavatar--initial" aria-hidden="true">{{ mb_strtoupper(mb_substr($project['pic_name'], 0, 1)) }}</span>
                                                    @endif
                                                    <span class="sgg-card-picname">{{ $project['pic_name'] }}</span>
                                                </span>
                                            @endif
                                        </div>
                                        {{-- Progress bar --}}
                                        <div class="sgg-card-progress">
                                            <div class="sgg-progress-track">
                                                <div class="sgg-progress-fill" style="--percent: {{ $project['progress_percent'] }}%"></div>
                                            </div>
                                            <span class="sgg-progress-text">{{ $project['progress_percent'] }}%</span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            {{-- Pesan saat filter tidak menemukan project --}}
                            <p class="sgg-filter-empty" id="filter-empty" hidden>Tidak ada project yang sesuai filter.</p>

                            {{-- Tampilan kosong jika belum ada project --}}
                            @if(count($projects) === 0)
                                <div class="sgg-empty">
                                    <h3>Belum Ada Project</h3>
                                    <p>Tambahkan project lewat Admin Panel, daftar akan muncul di layar ini.</p>
                                </div>
                            @endif
                        </div>

                        {{-- Bar bawah layar: filter Perusahaan & PIC (logika di app.js) + titik loading dekoratif --}}
                        <div class="sgg-filters">
                            @if(count($projects) > 0)
                                <label class="sgg-filter">
                                    <span>Perusahaan</span>
                                    <select id="filter-company">
                                        <option value="">Semua Perusahaan</option>
                                        @foreach($companies as $company)
                                            <option value="{{ $company->id }}">{{ $company->name }}</option>
                                        @endforeach
                                    </select>
                                </label>
                                <label class="sgg-filter">
                                    <span>PIC</span>
                                    <select id="filter-pic">
                                        <option value="">Semua PIC</option>
                                        @foreach($pics as $pic)
                                            <option value="{{ $pic->id }}">{{ $pic->nama }}</option>
                                        @endforeach
                                    </select>
                                </label>
                            @endif
                            {{-- Tiga lingkaran dekoratif: tidak bisa diklik, berkedip seperti indikator loading --}}
                            <span class="sgg-bar-dots" aria-hidden="true"><i></i><i></i><i></i></span>
                            {{-- Desktop: tombol masuk/keluar PIC di pojok kanan bar, berseberangan dengan filter --}}
                            @if($viewer)
                                <button type="button" class="sgg-theme sgg-bar-auth" data-logout title="Masuk sebagai {{ $viewer->name }}">Keluar</button>
                            @else
                                <button type="button" class="sgg-theme sgg-login-btn sgg-bar-auth" data-login-open aria-haspopup="dialog">Masuk PIC</button>
                            @endif
                        </div>
                    </div>
                </div>
                {{-- Stand monitor --}}
                <div class="sgg-monitor-neck" aria-hidden="true"></div>
                {{-- Tombol masuk/keluar PIC diletakkan di tengah kaki monitor --}}
                <div class="sgg-monitor-base">
                    @if($viewer)
                        <button type="button" class="sgg-theme sgg-base-btn" data-logout title="Masuk sebagai {{ $viewer->name }}">Keluar</button>
                    @else
                        <button type="button" class="sgg-theme sgg-login-btn sgg-base-btn" data-login-open aria-haspopup="dialog">Masuk PIC</button>
                    @endif
                </div>
            </div>
        </section>
    </main>

    {{-- Modal daftar PIC / programmer beserta foto --}}
    <div class="sgg-modal-overlay" id="pic-overlay">
        <div class="sgg-modal sgg-modal--team" role="dialog" aria-modal="true" aria-labelledby="pic-title">
            <div class="sgg-modal-header">
                <div class="sgg-modal-header-info">
                    <div class="sgg-modal-company">{{ $pics->count() }} orang</div>
                    <div class="sgg-modal-title" id="pic-title">PIC</div>
                </div>
                <button type="button" class="sgg-modal-close" data-close-pic aria-label="Tutup">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <div class="sgg-modal-body">
                @if($pics->isNotEmpty())
                    {{-- Slider horizontal: bergeser otomatis tiap beberapa detik (diatur app.js) --}}
                    <div class="sgg-team-track" id="pic-track" tabindex="0" role="list" aria-label="Daftar PIC">
                @endif
                @forelse($pics as $pic)
                    <div class="sgg-team-item" role="listitem">
                        @if($pic->foto)
                            <img src="{{ asset('storage/' . $pic->foto) }}" alt="Foto {{ $pic->nama }}" class="sgg-team-photo">
                        @else
                            <span class="sgg-team-photo sgg-team-photo--initial" aria-hidden="true">{{ mb_strtoupper(mb_substr($pic->nama, 0, 1)) }}</span>
                        @endif
                        <div class="sgg-team-text">
                            <div class="sgg-team-name">{{ $pic->nama }}</div>
                            @if($pic->jabatan || $pic->divisi)
                                <div class="sgg-team-role">{{ collect([$pic->jabatan, $pic->divisi])->filter()->implode(', ') }}</div>
                            @endif
                        </div>
                        <span class="sgg-badge sgg-badge--completed">{{ $pic->projects_count }} project</span>
                    </div>
                @empty
                    <p class="sgg-modal-empty">Belum ada PIC yang terdaftar.</p>
                @endforelse
                @if($pics->isNotEmpty())
                    </div>
                @endif
            </div>

            <div class="sgg-modal-footer">
                <button type="button" class="sgg-modal-close-btn" data-close-pic>Tutup</button>
            </div>
        </div>
    </div>

    {{-- Modal login PIC (hanya untuk tamu) --}}
    @unless($viewer)
        <div class="sgg-modal-overlay" id="login-overlay">
            <div class="sgg-modal sgg-modal--login" role="dialog" aria-modal="true" aria-labelledby="login-title">
                <form id="login-form" novalidate>
                    <div class="sgg-modal-header">
                        <div class="sgg-modal-header-info">
                            <div class="sgg-modal-company">Akun PIC</div>
                            <div class="sgg-modal-title" id="login-title">Masuk</div>
                        </div>
                        <button type="button" class="sgg-modal-close" data-close-login aria-label="Tutup">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    <div class="sgg-form-body">
                        <label class="sgg-field">
                            <span>Email</span>
                            <input type="email" name="email" id="login-email" autocomplete="username" required>
                        </label>
                        <label class="sgg-field">
                            <span>Password</span>
                            <input type="password" name="password" autocomplete="current-password" required>
                        </label>
                        <p class="sgg-form-error" id="login-error" role="alert" hidden></p>
                    </div>

                    <div class="sgg-modal-footer">
                        <button type="submit" class="sgg-modal-close-btn" id="login-submit">Masuk</button>
                    </div>
                </form>
            </div>
        </div>
    @endunless

    {{-- Modal popup detail project --}}
    <div class="sgg-modal-overlay" id="detail-overlay">
        <div class="sgg-modal" role="dialog" aria-modal="true" aria-labelledby="detail-title">
            <div class="sgg-modal-header">
                <div class="sgg-modal-header-info">
                    <div class="sgg-modal-company" id="detail-company"></div>
                    <div class="sgg-modal-title" id="detail-title"></div>
                    <div class="sgg-modal-badges" id="detail-badges"></div>
                </div>
                <button type="button" class="sgg-modal-close" data-close-modal aria-label="Tutup">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- Progres project --}}
            <div class="sgg-modal-progress-section">
                <span class="sgg-modal-progress-label">Progres</span>
                <div class="sgg-progress-track">
                    <div class="sgg-progress-fill" id="detail-progress-fill"></div>
                </div>
                <span class="sgg-modal-progress-pct" id="detail-progress-pct">0%</span>
            </div>

            {{-- Daftar tugas checklist (diisi oleh monitoring.js) --}}
            <div class="sgg-modal-body">
                {{-- Deskripsi lengkap (di kartu hanya 1 baris) --}}
                <p class="sgg-modal-stakeholder" id="detail-stakeholder" hidden><strong>Stakeholder</strong> <span></span></p>
                <p class="sgg-modal-desc" id="detail-desc" hidden></p>
                <div class="sgg-modal-body-title">Daftar Tugas</div>
                <div id="detail-checklist-container"></div>
                <p class="sgg-form-error" id="task-error" role="alert" hidden></p>
            </div>

            <div class="sgg-modal-footer">
                <button type="button" class="sgg-modal-close-btn" data-close-modal>Tutup</button>
            </div>
        </div>
    </div>

    <script src="{{ asset('js/monitoring.js') }}?v={{ filemtime(public_path('js/monitoring.js')) }}"></script>
    <script src="{{ asset('js/app.js') }}?v={{ filemtime(public_path('js/app.js')) }}"></script>
</body>
</html>
