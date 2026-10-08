{{-- 
    IT / Tech Background Component (Pure CSS & Inline SVG)
    - Layer paling belakang (-z-10)
    - Animasi CSS murni floatIT (6s, 8s, 10s)
    - Tech Grid & Dot Matrix
    - Neon Ambient Glow (Emerald, Teal, Cyan)
--}}
<style>
    @keyframes floatIT {
        0%, 100% {
            transform: translateY(0px) rotate(0deg);
            opacity: 0.08;
        }
        50% {
            transform: translateY(-25px) rotate(3deg);
            opacity: 0.18;
        }
    }
    .animate-float-1 { animation: floatIT 6s ease-in-out infinite; }
    .animate-float-2 { animation: floatIT 8s ease-in-out infinite 2s; }
    .animate-float-3 { animation: floatIT 10s ease-in-out infinite 4s; }

    /* Pastikan stacking context body & class -z-10 selalu aktif di semua browser */
    body {
        isolation: isolate;
    }

    .-z-10 {
        z-index: -10 !important;
    }

    /* 1. Tech Grid: Pola kisi-kisi digital */
    .tech-bg-grid {
        position: absolute;
        inset: 0;
        background-size: 38px 38px;
        background-image: 
            linear-gradient(to right, rgba(0, 138, 69, 0.045) 1px, transparent 1px),
            linear-gradient(to bottom, rgba(0, 138, 69, 0.045) 1px, transparent 1px);
        mask-image: radial-gradient(circle at 50% 35%, black 45%, rgba(0, 0, 0, 0.3) 80%, transparent 100%);
        -webkit-mask-image: radial-gradient(circle at 50% 35%, black 45%, rgba(0, 0, 0, 0.3) 80%, transparent 100%);
    }

    /* Dot Matrix aksen di persilangan grid */
    .tech-bg-dots {
        position: absolute;
        inset: 0;
        background-size: 38px 38px;
        background-image: radial-gradient(circle, rgba(16, 185, 129, 0.12) 1.2px, transparent 1.2px);
        background-position: -1px -1px;
        opacity: 0.85;
    }

    /* 2. Ambient Glow Neon (Vercel / Linear inspired) */
    .tech-glow {
        position: absolute;
        border-radius: 9999px;
        pointer-events: none;
        will-change: transform, opacity;
        filter: blur(80px);
        -webkit-filter: blur(80px);
    }

    /* Glow 1: Top-Left Neon Emerald (#008A45 / #10B981) */
    .tech-glow--emerald {
        top: -8vw;
        left: -8vw;
        width: 38vw;
        height: 38vw;
        min-width: 320px;
        min-height: 320px;
        background: radial-gradient(circle, rgba(16, 185, 129, 0.16) 0%, rgba(0, 138, 69, 0.08) 50%, transparent 75%);
        animation: techGlowPulse 10s ease-in-out infinite alternate;
    }

    /* Glow 2: Top-Right Neon Teal (#0D9488 / #14B8A6) */
    .tech-glow--teal {
        top: -6vw;
        right: -8vw;
        width: 40vw;
        height: 40vw;
        min-width: 340px;
        min-height: 340px;
        background: radial-gradient(circle, rgba(20, 184, 166, 0.15) 0%, rgba(13, 148, 136, 0.07) 50%, transparent 75%);
        animation: techGlowPulse 13s ease-in-out infinite alternate-reverse;
    }

    /* Glow 3: Bottom-Center/Right Neon Cyan (#06B6D4) */
    .tech-glow--cyan {
        bottom: -10vw;
        left: 25vw;
        width: 44vw;
        height: 36vw;
        min-width: 360px;
        min-height: 300px;
        background: radial-gradient(circle, rgba(6, 182, 212, 0.12) 0%, rgba(16, 185, 129, 0.05) 55%, transparent 75%);
        animation: techGlowPulse 11s ease-in-out infinite alternate;
    }

    @keyframes techGlowPulse {
        0% { transform: scale(0.96) translate(0, 0); opacity: 0.75; }
        50% { transform: scale(1.05) translate(10px, -10px); opacity: 1; }
        100% { transform: scale(0.98) translate(-5px, 8px); opacity: 0.85; }
    }

    /* 3. Layer Ikon IT Mengapung */
    .tech-icons-layer {
        position: absolute;
        inset: 0;
        pointer-events: none;
    }

    .tech-float-icon {
        position: absolute;
        color: #047857; /* Emerald-700 */
        pointer-events: none;
        will-change: transform, opacity;
    }

    .tech-float-icon svg {
        display: block;
        width: 100%;
        height: 100%;
        will-change: transform, opacity;
    }

    /* Posisi Tiap Ikon di Margin / Gutter Latar Belakang */
    .tech-icon-code {
        top: 10%;
        left: 3.5%;
        width: 76px;
        height: 76px;
    }

    .tech-icon-cpu {
        top: 14%;
        right: 4%;
        width: 82px;
        height: 82px;
    }

    .tech-icon-database {
        top: 52%;
        left: 2.8%;
        width: 70px;
        height: 70px;
    }

    .tech-icon-server {
        top: 58%;
        right: 3.2%;
        width: 74px;
        height: 74px;
    }

    .tech-icon-terminal {
        bottom: 8%;
        right: 5%;
        width: 72px;
        height: 72px;
    }

    /* Dark Mode Adaptive */
    .dark .tech-bg-grid,
    :root[class~="dark"] .tech-bg-grid {
        background-image: 
            linear-gradient(to right, rgba(16, 185, 129, 0.08) 1px, transparent 1px),
            linear-gradient(to bottom, rgba(16, 185, 129, 0.08) 1px, transparent 1px);
    }

    .dark .tech-bg-dots,
    :root[class~="dark"] .tech-bg-dots {
        background-image: radial-gradient(circle, rgba(52, 211, 153, 0.16) 1.2px, transparent 1.2px);
    }

    .dark .tech-float-icon,
    :root[class~="dark"] .tech-float-icon {
        color: #34d399; /* Emerald-400 */
    }

    .dark .tech-glow--emerald,
    :root[class~="dark"] .tech-glow--emerald {
        background: radial-gradient(circle, rgba(16, 185, 129, 0.22) 0%, rgba(5, 150, 105, 0.10) 50%, transparent 75%);
    }

    .dark .tech-glow--teal,
    :root[class~="dark"] .tech-glow--teal {
        background: radial-gradient(circle, rgba(20, 184, 166, 0.20) 0%, rgba(13, 148, 136, 0.09) 50%, transparent 75%);
    }

    .dark .tech-glow--cyan,
    :root[class~="dark"] .tech-glow--cyan {
        background: radial-gradient(circle, rgba(6, 182, 212, 0.18) 0%, rgba(16, 185, 129, 0.07) 55%, transparent 75%);
    }

    /* Responsif Layar Kecil */
    @media (max-width: 1024px) {
        .tech-icon-code     { width: 56px; height: 56px; left: 2%; top: 8%; }
        .tech-icon-cpu      { width: 60px; height: 60px; right: 2%; top: 12%; }
        .tech-icon-database { width: 52px; height: 52px; left: 1.5%; top: 50%; }
        .tech-icon-server   { width: 54px; height: 54px; right: 1.5%; top: 55%; }
        .tech-icon-terminal { width: 52px; height: 52px; right: 3%; bottom: 6%; }
    }

    @media (max-width: 640px) {
        .tech-icon-code     { width: 40px; height: 40px; left: 2%; top: 6%; }
        .tech-icon-cpu      { width: 44px; height: 44px; right: 2%; top: 8%; }
        .tech-icon-database { width: 38px; height: 38px; left: 2%; top: 50%; }
        .tech-icon-server   { width: 38px; height: 38px; right: 2%; top: 56%; }
        .tech-icon-terminal { width: 40px; height: 40px; right: 3%; bottom: 4%; }
    }
</style>

{{-- Container Utama: Layer Paling Belakang (-z-10) --}}
<div class="fixed inset-0 pointer-events-none -z-10 overflow-hidden">
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
