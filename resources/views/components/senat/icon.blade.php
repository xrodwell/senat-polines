@php
    /**
     * Icon system — Figma "PBL - Senat Polines" memakai glyph Unicode (☰ ▾ ▦ ◫ ✓ ≡ ◉ ▣ ◎ 🔒)
     * sebagai ikon. Diganti set SVG authored (24×24, stroke 1.75, currentColor) sesuai
     * craft-floor: "Icons are drawn, from a real library or authored SVG, in one consistent
     * stroke and weight" — Unicode/emoji tidak boleh berdiri sebagai ikon.
     */
    $size = $size ?? 'h-5 w-5';
    $paths = [
        'menu' => '<path d="M4 7h16M4 12h16M4 17h16"/>',
        'close' => '<path d="M6 6l12 12M18 6L6 18"/>',
        'chevron-down' => '<path d="M6 9.5l6 6 6-6"/>',
        'chevron-right' => '<path d="M9.5 6l6 6-6 6"/>',
        'chevron-left' => '<path d="M14.5 6l-6 6 6 6"/>',
        'arrow-right' => '<path d="M4 12h15m0 0l-6-6m6 6l-6 6"/>',
        'lock' => '<rect x="4.75" y="10.5" width="14.5" height="9.75" rx="2.25"/><path d="M8.25 10.5V8a3.75 3.75 0 017.5 0v2.5"/>',
        'bell' => '<path d="M18 15.5V11a6 6 0 10-12 0v4.5L4.5 18h15L18 15.5z"/><path d="M10.25 21a2 2 0 003.5 0"/>',
        'search' => '<circle cx="11" cy="11" r="6.25"/><path d="M15.6 15.6L20 20"/>',
        'filter' => '<path d="M4 6h16M7 12h10M10 18h4"/>',
        'download' => '<path d="M12 4v11m0 0l-4-4m4 4l4-4"/><path d="M5 19h14"/>',
        'external' => '<path d="M14 5h5v5"/><path d="M19 5l-7.5 7.5"/><path d="M18 14v4.25A1.75 1.75 0 0116.25 20H5.75A1.75 1.75 0 014 18.25V7.75A1.75 1.75 0 015.75 6H10"/>',
        'dashboard' => '<rect x="4" y="4" width="7" height="7" rx="1.75"/><rect x="13" y="4" width="7" height="7" rx="1.75"/><rect x="4" y="13" width="7" height="7" rx="1.75"/><rect x="13" y="13" width="7" height="7" rx="1.75"/>',
        'calendar' => '<rect x="4" y="5.5" width="16" height="14.5" rx="2.25"/><path d="M4 10h16M8.5 3.75v3.5M15.5 3.75v3.5"/>',
        'vote' => '<path d="M12 3.5l7 3.25v5.5c0 4.1-2.8 7.4-7 8.25-4.2-.85-7-4.15-7-8.25v-5.5l7-3.25z"/><path d="M9.25 12l2 2 3.5-3.75"/>',
        'minutes' => '<path d="M6 3.75h9.5L19 7.25v13H6z"/><path d="M15 4v3.75h3.75"/><path d="M9 12h7M9 15.5h5"/>',
        'aspiration' => '<path d="M4 10.5v3l2.5.75v3.5A1.25 1.25 0 007.75 19H9l.5-3.5"/><path d="M6.5 10.75L17 6v12l-10.5-4.75z"/><path d="M19.5 10.25v3.5"/>',
        'folder' => '<path d="M4 7.75A1.75 1.75 0 015.75 6h3.1a1.75 1.75 0 011.4.7l.9 1.2h7.1A1.75 1.75 0 0120 9.65v8.6A1.75 1.75 0 0118.25 20H5.75A1.75 1.75 0 014 18.25z"/>',
        'user' => '<circle cx="12" cy="8.5" r="3.75"/><path d="M4.75 20a7.25 7.25 0 0114.5 0"/>',
        'scan' => '<path d="M4 8.5V6.25A1.75 1.75 0 015.75 4.5H8.5M15.5 4.5h2.75A1.75 1.75 0 0120 6.25V8.5M20 15.5v2.25A1.75 1.75 0 0118.25 19.5H15.5M8.5 19.5H5.75A1.75 1.75 0 014 17.75V15.5"/><path d="M4 12h16"/>',
        'chart' => '<path d="M4 20V4"/><path d="M4 20h16"/><path d="M8 17V11M12.5 17V7M17 17v-4"/>',
        'check' => '<path d="M5 12.75l4.5 4.5L19 7"/>',
        'clock' => '<circle cx="12" cy="12" r="7.75"/><path d="M12 7.75V12l3 1.75"/>',
        'users' => '<circle cx="9.25" cy="9" r="3"/><path d="M3.75 19.25a5.5 5.5 0 0111 0"/><path d="M16 6.5a2.75 2.75 0 010 5.4"/><path d="M17.75 14.6a5 5 0 012.5 4.65"/>',
        'warning' => '<path d="M12 4.75l8.25 14.5H3.75z"/><path d="M12 10v4M12 16.75v.5"/>',
        'eye' => '<path d="M2.75 12S6 6.75 12 6.75 21.25 12 21.25 12 18 17.25 12 17.25 2.75 12 2.75 12z"/><circle cx="12" cy="12" r="2.75"/>',
        'eye-off' => '<path d="M4 4l16 16"/><path d="M9.5 5.2A9.6 9.6 0 0112 5c6 0 9.25 7 9.25 7a17 17 0 01-2.4 3.3"/><path d="M6.3 7.6A17.5 17.5 0 002.75 12S6 19 12 19a9.4 9.4 0 003.3-.6"/>',
        'inbox' => '<path d="M4 13.5V6.75A1.75 1.75 0 015.75 5h12.5A1.75 1.75 0 0120 6.75v6.75"/><path d="M4 13.5h4l.75 2.25h6.5L16 13.5h4v3.75A1.75 1.75 0 0118.25 19H5.75A1.75 1.75 0 014 17.25z"/>',
        'file-text' => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/>',
        'plus' => '<path d="M12 5v14M5 12h14"/>',
        'edit' => '<path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>',
        'trash' => '<polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/>',
        'upload' => '<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/>',
        'image' => '<rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/>',
        'dot' => '<circle cx="12" cy="12" r="3.25"/>',
    ];
    $d = $paths[$name] ?? $paths['dot'];
@endphp

<svg {{ $attributes->class($size) }} viewBox="0 0 24 24" fill="none" stroke="currentColor"
     stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"
     aria-hidden="true" focusable="false">{!! $d !!}</svg>
