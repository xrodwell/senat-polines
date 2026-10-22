<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="description" content="@yield('description', 'Portal resmi Senat Akademik Politeknik Negeri Semarang — informasi kebijakan akademik, produk hukum, agenda sidang, dan kanal aspirasi civitas akademika.')">
    <meta name="theme-color" content="#003c75">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Senat Akademik Politeknik Negeri Semarang')</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css'])
    @livewireStyles

    @include('components.senat.styles')

    @stack('head')
</head>
<body class="flex min-h-screen flex-col bg-senat-surface font-sans text-senat-body antialiased">

    @include('components.senat.header')

    <main id="konten" class="flex-1">
        @yield('content')
    </main>

    @include('components.senat.footer')

    @include('components.senat.chat-widget')

    @livewireScripts
    @stack('scripts')
</body>
</html>
