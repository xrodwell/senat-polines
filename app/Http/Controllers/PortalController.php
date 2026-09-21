<?php

namespace App\Http\Controllers;

use App\Contracts\MeetingRepositoryInterface;
use Illuminate\View\View;

class PortalController extends Controller
{
    public function __construct(
        private MeetingRepositoryInterface $meetings,
    ) {}

    public function memberAttendance(): View
    {
        return view('portal.member-attendance', [
            'activeSession' => $this->meetings->getActiveSession(),
            'upcomingMeetings' => $this->meetings->getUpcoming(),
        ]);
    }

    public function adminMeetingSession(): View
    {
        return view('portal.admin-meeting-session', [
            'activeSession' => $this->meetings->getActiveSession(),
            'meetings' => $this->meetings->all(),
        ]);
    }
}
