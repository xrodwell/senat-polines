<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#003366">
    <title>{{ $title ?? 'Admin Sekretariat — Senat Akademik Polines' }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css'])
    @livewireStyles

    @include('components.senat.styles')
</head>
<body class="min-h-screen bg-senat-surface font-sans text-senat-body antialiased">
    {{ $slot ?? '' }}
    @include('components.senat.role-switcher')
    @livewireScripts
</body>
</html>
