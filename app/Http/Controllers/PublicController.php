<?php

namespace App\Http\Controllers;

use App\Contracts\ArchiveRepositoryInterface;
use App\Contracts\AspirationRepositoryInterface;
use App\Contracts\CommitteeRepositoryInterface;
use App\Contracts\FeatureRepositoryInterface;
use App\Contracts\MeetingRepositoryInterface;
use App\Contracts\PostRepositoryInterface;
use App\Contracts\RegulationRepositoryInterface;
use App\Support\SenatDirectory;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Http;


class PublicController extends Controller
{
    public function __construct(
        private PostRepositoryInterface $posts,
        private MeetingRepositoryInterface $meetings,
        private RegulationRepositoryInterface $regulations,
        private CommitteeRepositoryInterface $committees,
        private FeatureRepositoryInterface $features,
        private AspirationRepositoryInterface $aspirations,
        private ArchiveRepositoryInterface $archives,
    ) {}

    /** 01 — Beranda */
    public function home(): View
    {
        return view('public.home', [
            'posts' => $this->posts->all(),
            'activeSession' => $this->meetings->getActiveSession(),
            'upcomingMeetings' => $this->meetings->getUpcoming(),
            'committees' => $this->committees->all(),
            'features' => $this->features->all(),
            'direktori' => SenatDirectory::stats(),
        ]);
    }

    /** 02A — Profil · Visi, Misi & Tujuan */
    public function profile(): View
    {
        $baseUrl = config('services.backend_api.url');

        try {
            $response = Http::timeout(3)->get("{$baseUrl}/api/struktur-organisasi");
            $struktur = $response->json()['data'] ?? [];
        } catch (\Throwable $e) {
            $struktur = [];
        }

        $ketua = collect($struktur)->firstWhere('jabatan', 'Ketua');
        $sekretaris = collect($struktur)->firstWhere('jabatan', 'Sekretaris');

        return view('public.profile.vision', [
            'committees' => $this->committees->all(),
            'direktori' => SenatDirectory::stats(),
            'struktur'   => $struktur,
            'ketua'      => $ketua,
            'sekretaris' => $sekretaris,
        ]);
    }

    /** 02B — Profil · Tugas & Wewenang */
    public function tasks(): View
    {
        return view('public.profile.tasks', [
            'committees' => $this->committees->all(),
            'features' => $this->features->all(),
        ]);
    }

    /** 02C — Profil · Struktur Organisasi */
    public function structure(): View
    {
        return view('public.profile.structure', [
            'committees' => $this->committees->all(),
            'leadership' => [
                'chair' => SenatDirectory::chair(),
                'secretary' => SenatDirectory::secretary(),
                'bph' => SenatDirectory::bph(),
            ],
            'period' => SenatDirectory::PERIOD,
            'direktori' => SenatDirectory::stats(),
        ]);
    }

    /** 03 — Direktori Komisi & Anggota */
    public function directory(): View
    {
        return view('public.directory', [
            'committees' => $this->committees->all(),
            'members' => SenatDirectory::members(),
            'leadership' => [
                'chair' => SenatDirectory::chair(),
                'secretary' => SenatDirectory::secretary(),
                'bph' => SenatDirectory::bph(),
            ],
            'period' => SenatDirectory::PERIOD,
            'direktori' => SenatDirectory::stats(),
        ]);
    }

    /** 04 — Detail Anggota */
    public function member(string $slug): View
    {
        $member = SenatDirectory::findBySlug($slug);

        abort_if($member === null, 404);

        $committee = isset($member['committee_code'])
            ? SenatDirectory::committeeByCode($member['committee_code'])
            : null;

        $peers = collect(SenatDirectory::members())
            ->filter(fn ($person) => ($person['committee_code'] ?? null) === ($member['committee_code'] ?? null)
                && $person['slug'] !== $member['slug'])
            ->values()
            ->all();

        return view('public.member', [
            'member' => $member,
            'committee' => $committee,
            'peers' => $peers,
            'period' => SenatDirectory::PERIOD,
        ]);
    }

    /** 05A — Produk Hukum · Peraturan Senat */
    public function regulations(): View
    {
        return view('public.regulations', [
            'regulations' => $this->regulations->all(),
        ]);
    }

    /** 05B — Produk Hukum · Keputusan Senat */
    public function decrees(): View
    {
        $all = $this->regulations->all();
        $filtered = array_values(array_filter($all, fn ($item) => str_contains($item['category'] ?? '', 'SK')
            || str_contains($item['category'] ?? '', 'Keputusan')));

        return view('public.regulations-decrees', [
            'regulations' => $filtered ?: $all,
        ]);
    }

    /** 05C — Produk Hukum · Standar & Pedoman Akademik */
    public function standards(): View
    {
        $standards = array_values(array_filter(
            $this->regulations->all(),
            fn ($item) => ! str_contains($item['category'] ?? '', 'SK')
        ));

        return view('public.regulations-standards', [
            'standards' => $standards ?: $this->regulations->all(),
        ]);
    }

    /** 06 — Kanal Aspirasi */
    public function aspirations(): View
    {
        return view('public.aspirations', [
            'aspirations' => $this->aspirations->all(),
            'committees' => $this->committees->all(),
        ]);
    }

    /** 09 — Pelacak Status Tiket */
    public function trackAspiration(Request $request): View
    {
        $ticket = $request->input('ticket', $request->route('ticket'));

        return view('public.track-aspiration', [
            'ticket' => $ticket,
            'aspiration' => $ticket ? $this->aspirations->findByTicket($ticket) : null,
        ]);
    }

    /** 11A — Arsip · Risalah Sidang */
    public function archives(): View
    {
        return view('public.archives', [
            'archives' => $this->archives->all(),
            'meetings' => $this->meetings->all(),
        ]);
    }

    /** 11B — Arsip · Laporan Tahunan & LPJ */
    public function archivesReports(): View
    {
        $archives = $this->archives->all();
        $reports = array_values(array_filter($archives, fn ($item) => str_contains($item['category'] ?? '', 'Laporan')));

        return view('public.archives-reports', [
            'archives' => $reports ?: $archives,
        ]);
    }

    /** 14 — Kanal Berita & Warta */
    public function news(): View
    {
        return view('public.news', [
            'posts' => $this->posts->all(),
        ]);
    }

    /** 14B — Detail Warta */
    public function newsShow(string $slug): View
    {
        $post = collect($this->posts->all())->firstWhere('slug', $slug);

        abort_if($post === null, 404);

        $related = collect($this->posts->all())
            ->reject(fn ($item) => $item['slug'] === $slug)
            ->take(3)
            ->values()
            ->all();

        return view('public.news-show', [
            'post' => $post,
            'related' => $related,
        ]);
    }
}
