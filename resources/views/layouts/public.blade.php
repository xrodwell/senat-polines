<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="@yield('description', 'Portal resmi Senat Akademik Politeknik Negeri Semarang — informasi kebijakan akademik, produk hukum, agenda sidang, dan kanal aspirasi civitas akademika.')">
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
                            orangeHover: '#d95f14',
                        },
                        /* Design tokens — Figma "PBL - Senat Polines" (file 6LJSjDuCcOWgneDcN7LPHZ) */
                        senat: {
                            hero: '#003c75',
                            primary: '#003366',
                            primaryHover: '#002850',
                            link: '#0963bd',
                            orange: '#f37021',
                            ink: '#172133',
                            body: '#52637d',
                            muted: '#637082',
                            line: '#e2e8ee',
                            surface: '#f7f9fc',
                            sky: '#8fc7ff',
                            skySoft: '#cfe0ff',
                            badgeBg: '#f2f9ff',
                            badgeLine: '#8cb8e3',
                            border: '#cbd5e1',
                        },
                    },
                },
            },
        };
    </script>

    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        [x-cloak] { display: none !important; }
        .senat-shell { max-width: 1440px; margin-inline: auto; padding-inline: 1.5rem; }
        @media (min-width: 1024px) { .senat-shell { padding-inline: 88px; } }
    </style>

    @stack('head')
</head>
<body class="bg-senat-surface text-senat-body antialiased font-sans flex flex-col min-h-screen">

    @include('components.senat.header')

    <main class="flex-1">
        @yield('content')
    </main>

    @include('components.senat.footer')

    @stack('scripts')
</body>
</html>
