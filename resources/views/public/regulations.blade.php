@extends('layouts.public')

@section('title', 'Produk Hukum · Peraturan Senat — Senat Akademik Politeknik Negeri Semarang')
@section('description', 'Himpunan peraturan mengikat, statuta institusi, kode etik sivitas, dan regulasi tata kelola akademik Politeknik Negeri Semarang.')

@section('content')
    @include('public.partials.regulation-browser', [
        'items' => $regulations,
        'pageSize' => 4,
        'eyebrow' => 'Regulasi Normatif Senat',
        'heading' => 'Peraturan Senat Akademik',
        'subtitle' => 'Himpunan peraturan mengikat, statuta institusi, kode etik sivitas, dan regulasi tata kelola akademik Polines.',
        'searchLabel' => 'Cari peraturan senat',
        'searchPlaceholder' => 'Cari nomor atau judul peraturan...',
        'listTitle' => 'Daftar Peraturan Senat Aktif',
        'countSuffix' => 'produk hukum terdokumentasi di katalog regulasi.',
        'emptyTitle' => 'Belum ada peraturan senat',
        'emptyMessage' => 'Sekretariat belum memublikasikan dokumen peraturan pada repositori ini.',
    ])
@endsection
