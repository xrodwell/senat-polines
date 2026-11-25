@extends('layouts.public')

@section('title', 'Produk Hukum · Keputusan Senat — Senat Akademik Politeknik Negeri Semarang')
@section('description', 'Ketetapan resmi hasil sidang pleno, pertimbangan senat terhadap kebijakan direktur, dan pengesahan kuota akademik Politeknik Negeri Semarang.')

@section('content')
    @include('public.partials.regulation-browser', [
        'items' => $regulations,
        'pageSize' => 6,
        'eyebrow' => 'Penetapan Eksekutif Senat',
        'heading' => 'Keputusan & Ketetapan Senat',
        'subtitle' => 'Ketetapan resmi hasil sidang pleno, pertimbangan senat terhadap kebijakan direktur, dan pengesahan kuota akademik.',
        'searchLabel' => 'Cari keputusan senat',
        'searchPlaceholder' => 'Cari nomor SK atau judul keputusan...',
        'listTitle' => 'Daftar Keputusan & Ketetapan Pleno',
        'countSuffix' => 'ketetapan dan surat keputusan terdokumentasi.',
        'emptyTitle' => 'Belum ada keputusan senat',
        'emptyMessage' => 'Sekretariat belum memublikasikan surat keputusan atau ketetapan pleno pada repositori ini.',
    ])
@endsection