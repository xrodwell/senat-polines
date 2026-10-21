<?php

namespace App\Http\Controllers;

use App\Contracts\ArchiveRepositoryInterface;
use App\Contracts\AspirationRepositoryInterface;
use App\Contracts\CommitteeRepositoryInterface;
use App\Contracts\MeetingRepositoryInterface;
use App\Contracts\PostRepositoryInterface;
use App\Contracts\RegulationRepositoryInterface;
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
        private AspirationRepositoryInterface $aspirations,
        private ArchiveRepositoryInterface $archives,
    ) {}

    public function home(): View
    {
        return view('public.home', [
            'posts' => $this->posts->all(),
            'activeSession' => $this->meetings->getActiveSession(),
            'upcomingMeetings' => $this->meetings->getUpcoming(),
        ]);
    }

    public function profile(): View
    {
        $baseUrl = config('services.backend_api.url');

        $response = Http::get("{$baseUrl}/api/struktur-organisasi");
        $struktur = $response->json()['data'] ?? [];

        $ketua = collect($struktur)->firstWhere('jabatan', 'Ketua');
        $sekretaris = collect($struktur)->firstWhere('jabatan', 'Sekretaris');

        return view('public.profile', [
            'committees' => $this->committees->all(),
            'struktur'   => $struktur,
            'ketua'      => $ketua,
            'sekretaris' => $sekretaris,
        ]);
    }

    public function regulations(): View
    {
        return view('public.regulations', [
            'regulations' => $this->regulations->all(),
        ]);
    }

    public function aspirations(): View
    {
        return view('public.aspirations', [
            'aspirations' => $this->aspirations->all(),
        ]);
    }

    public function trackAspiration(Request $request): View
    {
        $ticket = $request->input('ticket', $request->route('ticket'));

        return view('public.track-aspiration', [
            'ticket' => $ticket,
            'aspiration' => $ticket ? $this->aspirations->findByTicket($ticket) : null,
        ]);
    }

    public function archives(): View
    {
        return view('public.archives', [
            'archives' => $this->archives->all(),
        ]);
    }
}
