<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Portal resmi Senat Akademik Politeknik Negeri Semarang — informasi kebijakan akademik, produk hukum, agenda sidang, dan kanal aspirasi civitas akademika.">
    <title>@yield('title', 'Senat Akademik Politeknik Negeri Semarang')</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400&display=swap" rel="stylesheet">

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ["'Plus Jakarta Sans'", 'ui-sans-serif', 'system-ui', 'sans-serif'],
                    },
                    colors: {
                        polines: {
                            navy: '#003366',
                            navyDark: '#00284D',
                            blue: '#0A66C2',
                            orange: '#F37021',
                        },
                    },
                },
            },
        };
    </script>

    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        [x-cloak] { display: none !important; }
    </style>

    @stack('head')
</head>
<body class="bg-slate-50 text-slate-800 antialiased font-sans flex flex-col min-h-screen">

    @include('components.navbar')

    <main class="flex-1">
        @yield('content')
    </main>

    {{-- Floating Demo Role Switcher untuk Pengawas / Presentasi --}}
    <aside class="fixed bottom-4 right-4 z-50">
        <div class="rounded-2xl border border-slate-700/80 bg-slate-900/95 p-3 shadow-2xl backdrop-blur-md text-white flex items-center gap-2">
            <span class="text-[11px] font-mono text-slate-400 uppercase tracking-wider px-2 font-semibold">Demo Role:</span>
            
            <a href="{{ route('public.home') }}" 
               class="px-2.5 py-1.5 rounded-lg text-xs font-semibold transition-all {{ request()->routeIs('public.*') ? 'bg-polines-blue text-white shadow-xs' : 'text-slate-300 hover:bg-slate-800' }}">
                🌐 Publik
            </a>

            <a href="{{ route('portal.attendance') }}" 
               class="px-2.5 py-1.5 rounded-lg text-xs font-semibold transition-all {{ request()->routeIs('portal.attendance') ? 'bg-emerald-600 text-white shadow-xs' : 'text-slate-300 hover:bg-slate-800' }}">
                👤 Anggota Senat
            </a>

            <a href="{{ route('portal.session') }}" 
               class="px-2.5 py-1.5 rounded-lg text-xs font-semibold transition-all {{ request()->routeIs('portal.session') ? 'bg-polines-orange text-white shadow-xs' : 'text-slate-300 hover:bg-slate-800' }}">
                ⚙️ Admin Sekretariat
            </a>
        </div>
    </aside>

    @include('components.footer')

    @stack('scripts')
</body>
</html>
