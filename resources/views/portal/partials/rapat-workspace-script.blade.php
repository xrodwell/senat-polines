{{-- Inisialisasi konfigurasi server untuk workspace sidang --}}
<script>
    window.__senatMeetings = @json($meetings ?? []);
    window.__senatUserId = {{ (int) ($idUser ?? 2) }};
    window.__senatBase = '{{ url('/') }}';
</script>

{{-- Modul JavaScript eksternal untuk workspace anggota senat --}}
<script src="{{ asset('js/portal/member-attendance.js') }}"></script>
