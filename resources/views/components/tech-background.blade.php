{{-- Latar belakang bertema IT (grid, glow, ikon mengapung); CSS di public/css/app.css, hanya halaman monitoring --}}
<div class="tech-bg" aria-hidden="true">
    {{-- 1. Tech Grid & Dot Matrix Overlay --}}
    <div class="tech-bg-grid"></div>
    <div class="tech-bg-dots"></div>

    {{-- 2. Ambient Glow Neon Orbs (Teal / Emerald / Cyan) --}}
    <div class="tech-glow tech-glow--emerald"></div>
    <div class="tech-glow tech-glow--teal"></div>
    <div class="tech-glow tech-glow--cyan"></div>

    {{-- 3. Floating IT Icons (CSS murni floatIT bergantian) --}}
    <div class="tech-icons-layer">
        {{-- Icon 1: Code </> (Top-Left) -> animate-float-1 --}}
        <div class="tech-float-icon tech-icon-code" title="Code">
            <svg class="animate-float-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="16 18 22 12 16 6"></polyline>
                <polyline points="8 6 2 12 8 18"></polyline>
                <line x1="14" y1="4" x2="10" y2="20"></line>
            </svg>
        </div>

        {{-- Icon 2: CPU Chip (Top-Right) -> animate-float-2 --}}
        <div class="tech-float-icon tech-icon-cpu" title="CPU">
            <svg class="animate-float-2" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                <rect x="4" y="4" width="16" height="16" rx="2"></rect>
                <rect x="9" y="9" width="6" height="6" rx="1"></rect>
                <path d="M9 1v3M15 1v3M9 20v3M15 20v3M20 9h3M20 15h3M1 9h3M1 15h3"></path>
            </svg>
        </div>

        {{-- Icon 3: Database (Mid-Left) -> animate-float-3 --}}
        <div class="tech-float-icon tech-icon-database" title="Database">
            <svg class="animate-float-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                <ellipse cx="12" cy="5" rx="9" ry="3"></ellipse>
                <path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"></path>
                <path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"></path>
            </svg>
        </div>

        {{-- Icon 4: Server Rack (Mid-Right) -> animate-float-1 --}}
        <div class="tech-float-icon tech-icon-server" title="Server">
            <svg class="animate-float-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                <rect x="2" y="2" width="20" height="8" rx="2" ry="2"></rect>
                <rect x="2" y="14" width="20" height="8" rx="2" ry="2"></rect>
                <circle cx="6" cy="6" r="1" fill="currentColor"></circle>
                <circle cx="6" cy="18" r="1" fill="currentColor"></circle>
                <line x1="11" y1="6" x2="18" y2="6"></line>
                <line x1="11" y1="18" x2="18" y2="18"></line>
            </svg>
        </div>

        {{-- Icon 5: Terminal CLI (Bottom-Right) -> animate-float-2 --}}
        <div class="tech-float-icon tech-icon-terminal" title="Terminal">
            <svg class="animate-float-2" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="4 17 10 11 4 5"></polyline>
                <line x1="12" y1="19" x2="20" y2="19"></line>
            </svg>
        </div>
    </div>
</div>
