<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monitoring Project — Syifa Global Group</title>
    <meta name="description" content="Sistem Monitoring Project Syifa Global Group (SGG) — Pantau progress proyek secara real-time.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css'])
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
    {{-- Latar belakang modern bertema IT (Tech Grid, Ambient Glow, dan Floating Icons) --}}
    <x-tech-background />

    {{-- Header utama dan logo SGG --}}
    <header class="sgg-header" id="app-header">
        <div class="sgg-header-inner">
            <img src="{{ asset('images/logo-sgg.svg') }}" alt="Syifa Global Group Logo" class="sgg-logo-img">
            <div class="sgg-logo-text">
                <span class="brand">Syifa Global Group</span>
                <span class="sub">Monitoring Project</span>
            </div>
        </div>
    </header>

    {{-- Konten utama (read-only) --}}
    <main class="sgg-main" id="app-main">
        <div class="sgg-page-header">
            <h1 class="sgg-page-title">Dashboard Monitoring</h1>
            <p class="sgg-page-subtitle">Pantau seluruh progress proyek Syifa Global Group</p>
        </div>

        {{-- Filter Perusahaan & PIC (logika di app.js) --}}
        @if(count($projects) > 0)
            <div class="sgg-filters">
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
            </div>
        @endif

        {{-- Daftar kartu project: 1 baris 1 project --}}
        <div class="sgg-project-list" id="project-list">
            @foreach($projects as $index => $project)
                {{-- Kelas dan label badge urgensi/status --}}
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
                <div class="sgg-project-card bg-white/90 backdrop-blur-sm dark:bg-gray-900/90"
                     role="button" tabindex="0"
                     data-project='@json($project)'
                     data-company="{{ $project['company_id'] }}"
                     data-pic="{{ $project['pic_id'] }}"
                     style="--delay: {{ $index * 60 }}ms; animation-delay: var(--delay);"
                     onclick="openDetailModal(this)"
                     onkeydown="if(event.key==='Enter'||event.key===' '){event.preventDefault();openDetailModal(this);}">
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
                        {{-- 2. Deskripsi project --}}
                        @if($project['description'])
                            <p class="sgg-card-desc">{{ $project['description'] }}</p>
                        @endif
                        {{-- 3. PIC & Pemohon, melipat otomatis jika nama panjang --}}
                        @if($project['pic_name'] || $project['pemohon'])
                            <div class="sgg-card-meta">
                                @if($project['pic_name'])
                                    <span><strong>PIC:</strong> {{ $project['pic_name'] }}</span>
                                @endif
                                @if($project['pic_name'] && $project['pemohon'])
                                    <span class="sgg-card-meta-sep">|</span>
                                @endif
                                @if($project['pemohon'])
                                    <span><strong>Pemohon:</strong> {{ $project['pemohon'] }}</span>
                                @endif
                            </div>
                        @endif
                    </div>
                    {{-- Badge urgensi dan status --}}
                    <div class="sgg-card-badges">
                        <span class="sgg-badge {{ $urgencyClass }}"><span class="sgg-badge-dot"></span>{{ $urgencyLabel }}</span>
                        <span class="sgg-badge {{ $statusClass }}"><span class="sgg-badge-dot"></span>{{ $statusLabel }}</span>
                    </div>
                    {{-- Progress bar --}}
                    <div class="sgg-card-progress">
                        <div class="sgg-progress-track">
                            <div class="sgg-progress-fill" style="--percent: {{ $project['progress_percent'] }}%; width: var(--percent);"></div>
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
                <div class="sgg-empty-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <h3>Belum Ada Project</h3>
                <p>Belum ada project yang dapat dipantau saat ini.</p>
            </div>
        @endif
    </main>

    {{-- Modal popup detail project (Read Only) --}}
    <div class="sgg-modal-overlay" id="detail-overlay" onclick="if(event.target===this) closeDetailModal()">
        <div class="sgg-modal" role="dialog" aria-modal="true" aria-labelledby="detail-title">
            <div class="sgg-modal-header">
                <div class="sgg-modal-header-info">
                    <div class="sgg-modal-company" id="detail-company"></div>
                    <div class="sgg-modal-title" id="detail-title"></div>
                    <div class="sgg-modal-badges" id="detail-badges"></div>
                </div>
                <button class="sgg-modal-close" onclick="closeDetailModal()" aria-label="Tutup">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- Progres project --}}
            <div class="sgg-modal-progress-section">
                <span class="sgg-modal-progress-label">Progres</span>
                <div class="sgg-progress-track">
                    <div class="sgg-progress-fill" id="detail-progress-fill" style="width: 0%"></div>
                </div>
                <span class="sgg-modal-progress-pct" id="detail-progress-pct">0%</span>
            </div>

            {{-- Daftar tugas (diisi oleh app.js) --}}
            <div class="sgg-modal-body">
                <div class="sgg-modal-body-title">Daftar Tugas</div>
                <div id="detail-checklist-container"></div>
            </div>

            <div class="sgg-modal-footer">
                <button class="sgg-modal-close-btn" onclick="closeDetailModal()">Tutup</button>
            </div>
        </div>
    </div>

    <script src="{{ asset('js/app.js') }}"></script>
</body>
</html>
