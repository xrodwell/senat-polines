@extends('layouts.public')

@section('title', 'Produk Hukum · Standar & Pedoman Akademik — Senat Akademik Politeknik Negeri Semarang')
@section('description', 'Dokumen acuan teknis SPMI, buku pedoman kurikulum teaching factory, instrumen evaluasi, serta SOP tata kelola institusi Politeknik Negeri Semarang.')

@section('content')
    @include('public.partials.regulation-browser', [
        'items' => $standards,
        'pageSize' => 6,
        'eyebrow' => 'Manual Mutu & Panduan Kerja',
        'heading' => 'Standar & Pedoman Mutu Akademik',
        'subtitle' => 'Dokumen acuan teknis SPMI, buku pedoman kurikulum teaching factory, instrumen evaluasi, serta SOP tata kelola institusi.',
        'searchLabel' => 'Cari standar & pedoman akademik',
        'searchPlaceholder' => 'Cari standar, pedoman, atau SOP...',
        'listTitle' => 'Daftar Manual Mutu & SOP Akademik',
        'countSuffix' => 'instrumen SPMI, pedoman, dan SOP akademik terdokumentasi.',
        'emptyTitle' => 'Belum ada standar & pedoman',
        'emptyMessage' => 'Sekretariat belum memublikasikan instrumen SPMI atau buku pedoman pada repositori ini.',
    ])
@endsection
