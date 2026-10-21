{{--
    Sistem responsif Senat Polines — kontrak dari Figma "Responsive reference" (2352:1277, 2352:1306, 2352:1335):
      Breakpoints: mobile 0–767 · tablet 768–1023 · desktop ≥1024
      Gutter: 20px mobile · 24px tablet · 88px desktop
      Sidebar → drawer 320px dari kanan, backdrop 40%, tutup via X / backdrop / Escape / pilih menu
      Tap target min. 44×44px · Focus ring 2px #155EAD
      KPI: 2 kolom di mobile & tablet, 4 kolom di desktop
      Tabel → stacked rows di mobile · KPI/helper maks 2 baris (ellipsis)
    Satu sumber kebenaran: dipakai layouts/public.blade.php dan layouts/portal.blade.php.
--}}
<style>
    /* Alpine: cegah flash elemen terkontrol sebelum runtime siap */
    [x-cloak] { display: none !important; }

    :root {
        --senat-focus: #155EAD;
        --senat-gutter: 1.25rem;
    }
    @media (min-width: 768px)  { :root { --senat-gutter: 1.5rem; } }
    @media (min-width: 1024px) { :root { --senat-gutter: 88px; } }

    /* ---------- Shell ---------- */
    .senat-shell {
        width: 100%;
        max-width: 1440px;
        margin-inline: auto;
        padding-inline: var(--senat-gutter);
    }

    /* ---------- Fluid type scale ---------- */
    .senat-display {
        font-size: clamp(1.625rem, 1.05rem + 2.6vw, 2.625rem);
        line-height: 1.1;
        letter-spacing: -0.02em;
        text-wrap: balance;
    }
    .senat-h2 {
        font-size: clamp(1.3125rem, 1.08rem + 1.05vw, 1.75rem);
        line-height: 1.2;
        letter-spacing: -0.015em;
        text-wrap: balance;
    }
    .senat-h3 {
        font-size: clamp(1rem, 0.96rem + 0.3vw, 1.125rem);
        line-height: 1.3;
        text-wrap: balance;
    }
    .senat-eyebrow {
        font-size: 0.6875rem;
        font-weight: 700;
        letter-spacing: 0.1em;
        text-transform: uppercase;
    }
    .senat-prose   { text-wrap: pretty; }
    .senat-measure { max-width: 68ch; }

    /* ---------- Tap target (pointer kasar = jari) ---------- */
    @media (pointer: coarse) {
        .senat-tap { min-height: 44px; min-width: 44px; }
    }
    .senat-tap { -webkit-tap-highlight-color: transparent; }

    /* ---------- Focus ring (2px #155EAD) ---------- */
    :where(a, button, input, select, textarea, summary, [tabindex]):focus-visible {
        outline: 2px solid var(--senat-focus);
        outline-offset: 2px;
        border-radius: 6px;
    }

    /* ---------- Clamp helper (KPI/helper maks 2 baris) ---------- */
    .senat-clamp-2 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    /* ---------- Tabel → stacked rows di mobile ---------- */
    @media (max-width: 767px) {
        .senat-table { display: block; }
        .senat-table thead { display: none; }
        .senat-table tbody, .senat-table tr, .senat-table td { display: block; width: 100%; }
        .senat-table tr {
            border: 1px solid #e2e8ee;
            border-radius: 12px;
            background: #fff;
            padding: 0.875rem;
            margin-bottom: 0.75rem;
        }
        .senat-table td {
            display: flex;
            align-items: baseline;
            justify-content: space-between;
            gap: 1rem;
            border: 0;
            padding: 0.3rem 0;
            text-align: right;
        }
        .senat-table td::before {
            content: attr(data-label);
            flex: 0 0 auto;
            text-align: left;
            font-size: 0.6875rem;
            font-weight: 700;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            color: #52637d;
        }
        /* Tabel tanpa data-label: biarkan mengalir */
        .senat-table td:not([data-label]) { display: block; text-align: left; }
    }

    /* ---------- Scroll container (hindari overflow halaman) ---------- */
    .senat-scroll-x {
        overflow-x: auto;
        overscroll-behavior-x: contain;
        -webkit-overflow-scrolling: touch;
    }

    /* ---------- Drawer (320px dari kanan, backdrop 40%) ---------- */
    .senat-drawer-backdrop { background-color: rgb(13 26 41 / 0.4); }
    .senat-drawer-x { animation: senat-drawer-in 220ms cubic-bezier(0.16, 1, 0.3, 1); }
    @keyframes senat-drawer-in {
        from { transform: translateX(16px); opacity: 0; }
        to   { transform: translateX(0);    opacity: 1; }
    }

    /* ---------- Bottom navigation (mobile portal) ---------- */
    .senat-bottomnav {
        padding-bottom: env(safe-area-inset-bottom, 0);
    }

    /* ---------- Browser surfaces ikut palette ---------- */
    ::selection { background: #cfe0ff; color: #0b2545; }
    input, select, textarea { caret-color: var(--senat-focus); }
    :root { scrollbar-color: #9fb3c8 transparent; scrollbar-width: thin; }
    ::-webkit-scrollbar { width: 10px; height: 10px; }
    ::-webkit-scrollbar-thumb {
        background: #c3d2e2;
        border: 3px solid transparent;
        background-clip: content-box;
        border-radius: 999px;
    }
    ::-webkit-scrollbar-thumb:hover { background: #9fb3c8; background-clip: content-box; }

    /* ---------- Motion: satu bahasa easing, hormati preferensi ---------- */
    @media (prefers-reduced-motion: reduce) {
        *, *::before, *::after {
            animation-duration: 0.01ms !important;
            animation-iteration-count: 1 !important;
            transition-duration: 0.01ms !important;
            scroll-behavior: auto !important;
        }
    }
</style>
