# DESIGN_SPEC — Implementasi Figma "PBL - Senat Polines" ke kode Laravel

Dokumen kerja untuk agen implementasi. **Baca ini dulu sebelum menulis view.**

## 1. Sumber desain (Figma MCP)

- `fileKey`: `6LJSjDuCcOWgneDcN7LPHZ`
- `sessionId`: `8d4e51eb-e1ad-4232-baf4-b4119239730f`
- Ambil desain per layar:
  1. `tool_search` dengan query `figma design context`
  2. `tool_describe` untuk `mcp__figma__get_design_context`
  3. `tool_call` → `mcp__figma__get_design_context` dengan argumen:
     ```json
     { "fileKey": "6LJSjDuCcOWgneDcN7LPHZ", "nodeId": "<NODE_ID>",
       "sessionId": "8d4e51eb-e1ad-4232-baf4-b4119239730f",
       "clientFrameworks": "vanilla", "clientLanguages": "html,css,tailwind" }
     ```
- Hasil berupa **React + Tailwind absolut** + screenshot. Konversi ke Blade + Tailwind, jangan salin React.
- Node `section` hanya mengembalikan metadata稀疏 — selalu minta per **frame** (layar), bukan per section.
- Koordinat absolut dari Figma **harus diterjemahkan** ke flex/grid responsif. Jangan pakai `absolute left-[...]` hasil salinan mentah kecuali untuk elemen dekoratif yang memang absolut.

## 2. Stack target (WAJIB diikuti)

- Laravel 13 + Blade + Livewire 4. Tailwind CSS v4 dimuat lewat build Vite (`@vite(['resources/css/app.css'])`), bukan CDN.
- Interaksi memakai Alpine yang dibundel Livewire (tanpa script CDN, tanpa `alpinejs` standalone).
- Tidak ada React/Vue.

## 3. Design token (didefinisikan di `resources/css/app.css` via `@theme`)

Gunakan kelas `senat-*` berikut — jangan hardcode hex baru kalau token sudah ada:

| Token | Nilai | Kelas |
|---|---|---|
| Hero navy | `#003c75` | `bg-senat-hero` |
| Primary | `#003366` | `bg-senat-primary` / `text-senat-primary` |
| Primary hover | `#002850` | `hover:bg-senat-primaryHover` |
| Link | `#0963bd` | `text-senat-link` |
| Orange | `#f37021` | `text-senat-orange` |
| Ink | `#172133` (publik) / `#13233a` (portal) | `text-senat-ink` |
| Body | `#52637d` | `text-senat-body` |
| Muted | `#637082` / `#667085` | `text-senat-muted` |
| Line | `#e2e8ee` | `border-senat-line` |
| Surface | `#f7f9fc` | `bg-senat-surface` |
| Sky / Sky soft | `#8fc7ff` / `#cfe0ff` | `text-senat-sky` / `text-senat-skySoft` |
| Badge bg / line | `#f2f9ff` / `#8cb8e3` | `bg-senat-badgeBg` / `border-senat-badgeLine` |
| Border input | `#cbd5e1` | `border-senat-border` |

Tipografi: publik = **Plus Jakarta Sans**; portal = **Inter**. Sudah dimuat di layout.

## 4. Layout & komponen bersama (JANGAN diubah, hanya dipakai)

- Publik: `@extends('layouts.public')` → sudah otomatis render Header/Public (2006:2) + Footer/Public (2006:14).
  - `@section('title', '...')`, `@section('description', '...')`, `@section('content')`
- Portal: `@extends('layouts.portal')` → topbar 2269:728 + sidebar.
  - Default sidebar anggota. Untuk sekretariat: sebelum `@extends` tidak bisa — pakai di dalam: `@section('content')` normal, dan set variabel lewat `@php $sidebar = 'sekretariat'; @endphp` **sebelum** `@extends` → Blade tidak mengizinkan. Solusi: tulis `@extends('layouts.portal', ['sidebar' => 'sekretariat'])`.
- Shell publik: `class="senat-shell"` (max-width 1440, padding 88px di desktop).
- Komponen Blade yang sudah ada:
  - `<x-senat.button variant="primary|secondary|ghost" size="sm|md|lg" :href="..." icon="🔒" trailing="›">Teks</x-senat.button>`
  - `<x-senat.badge state="info|success|warning|danger|neutral">Teks</x-senat.badge>`
  - `<x-senat.stat label="..." value="..." hint="..." icon="▦" />`
  - `<x-senat.page-hero eyebrow="..." title="..." subtitle="..." />`
  - `<x-senat.empty title="..." message="..." actionLabel="..." :actionHref="..." />`

## 5. Halaman, route, view, dan kontrak data

Semua controller **sudah** menyediakan data berikut. Jangan mengubah controller/route — kalau butuh data tambahan, laporkan.

### Portal Publik
| Layar Figma | Node ID | Route name | View | Variabel |
|---|---|---|---|---|
| 01 — Beranda | `2013:3` | `public.home` | `public/home.blade.php` | `$posts, $activeSession, $upcomingMeetings, $committees, $features, $direktori` |
| 02A — Profil · Visi, Misi & Tujuan | `2019:2` | `public.profile` | `public/profile/vision.blade.php` | `$committees, $direktori` |
| 02B — Profil · Tugas & Wewenang | `2204:147` | `public.profile.tasks` | `public/profile/tasks.blade.php` | `$committees, $features` |
| 02C — Profil · Struktur Organisasi | `2204:304` | `public.profile.structure` | `public/profile/structure.blade.php` | `$committees, $leadership, $period, $direktori` |
| 03 — Direktori Komisi & Anggota | `2075:2` | `public.directory` | `public/directory.blade.php` | `$committees, $members, $leadership, $period, $direktori` |
| 04 — Detail Anggota | `2061:2` | `public.directory.member` (`{slug}`) | `public/member.blade.php` | `$member, $committee, $peers, $period` |
| 05A — Produk Hukum · Peraturan | `2019:120` | `public.regulations` | `public/regulations.blade.php` | `$regulations` |
| 05B — Produk Hukum · Keputusan | `2233:286` | `public.regulations.decrees` | `public/regulations-decrees.blade.php` | `$regulations` |
| 05C — Produk Hukum · Standar | `2233:376` | `public.regulations.standards` | `public/regulations-standards.blade.php` | `$standards` |
| 06 — Kanal Aspirasi | `2019:211` | `public.aspirations` | `public/aspirations.blade.php` | `$aspirations, $committees` |
| 09 — Pelacak Salah tiket | `2020:2` | `public.aspirations.track` | `public/track-aspiration.blade.php` | `$ticket, $aspiration` |
| 11A — Arsip · Risalah Sidang | `2020:147` | `public.archives` | `public/archives.blade.php` | `$archives, $meetings` |
| 11B — Arsip · Laporan & LPJ | `2236:366` | `public.archives.reports` | `public/archives-reports.blade.php` | `$archives` |
| 14 — Kanal Berita & Warta | `2218:211` | `public.news` | `public/news.blade.php` | `$posts` |
| 14B — Detail Warta | `2224:252` | `public.news.show` (`{slug}`) | `public/news-show.blade.php` | `$post, $related` |

### Portal Internal — Anggota
| Layar | Node ID | Route | View | Variabel |
|---|---|---|---|---|
| 11 — Login Portal | `2073:2` | `portal.login` | `portal/login.blade.php` | — |
| 12A — Dashboard Anggota | `2021:2` | `portal.dashboard` | `portal/dashboard.blade.php` | `$stats, $meetings, $aspirations, $announcements` |
| 12B — Agenda & Presensi | `2279:763` | `portal.agenda` | `portal/agenda.blade.php` | `$stats, $meetings` |
| 12C — Notulensi & Risalah | `2279:970` | `portal.minutes` | `portal/minutes.blade.php` | `$meetings, $archives` |
| 12D — E-Voting | `2279:866` | `portal.voting` | `portal/voting.blade.php` | `$stats, $meetings` |
| 12E — Aspirasi & RUU Kampus | `2279:1075` | `portal.aspirations` | `portal/aspirations.blade.php` | `$aspirations` |
| 12F — Dokumen & Arsip | `2279:1181` | `portal.documents` | `portal/documents.blade.php` | `$archives, $regulations` |
| 12G — Profil Anggota | `2281:1477` | `portal.profile` | `portal/profile.blade.php` | `$stats, $leadership, $committees` |

### Portal Internal — Sekretariat (13x)
| Layar | Node ID | Route | View | Variabel |
|---|---|---|---|---|
| 13A — Dashboard Sekretariat | `2021:118` | `portal.secretariat.dashboard` | `portal/secretariat/dashboard.blade.php` | `$stats, $meetings, $aspirations, $archives, $regulations` |
| 13B — Manajemen Sidang | `2280:938` | `portal.secretariat.sessions` | `portal/secretariat/sessions.blade.php` | `$stats, $meetings` |
| 13C — Notulensi & Tindak Lanjut | `2280:939` | `portal.secretariat.minutes` | `portal/secretariat/minutes.blade.php` | `$meetings, $archives` |
| 13D — Presensi & Kuorum | `2281:1043` | `portal.secretariat.attendance` | `portal/secretariat/attendance.blade.php` | `$stats, $meetings` |
| 13E — E-Voting | `2281:1150` | `portal.secretariat.voting` | `portal/secretariat/voting.blade.php` | `$stats, $meetings` |
| 13F — Aspirasi & RUU Kampus | `2281:1259` | `portal.secretariat.aspirations` | `portal/secretariat/aspirations.blade.php` | `$aspirations` |
| 13G — Produk, Arsip & Berita | `2281:1376` | `portal.secretariat.publications` | `portal/secretariat/publications.blade.php` | `$regulations, $archives, $posts` |

## 6. Skema data (storage/mock/*.json — sudah ada, jangan diubah)

- `committees.json`: `id, code (KOM-I..V), name, lead, secretary, members_count, members[], focus, tupoksi`
- `features.json`: `id, code, slug, title, subtitle, points[{label,detail}], flow[]`
- `aspirations.json`: `id, ticket_code, sender_category, jurusan, committee_target, subject, description, status, submitted_at, response`
- `meetings.json`: `id, title, type, date, time, room, status, session_code, qr_token, total_invited, present_count`
- `posts.json`: `id, title, slug, category, date, excerpt, body[], thumbnail, author`
- `regulations.json`: `id, nomor, title, year, category, status, file_size, download_url`
- `archives.json`: `id, title, year, category, file_size, download_url`
- `SenatDirectory` (`app/Support/SenatDirectory.php`): `chair()`, `secretary()`, `bph()`, `committees()`, `members()`, `findBySlug()`, `stats()`, `PERIOD`, `committeeLabel($code)`

## 7. Verifikasi (wajib, sebelum lapor)

```bash
npm run build                                                      # wajib sukses
php artisan view:clear
php artisan serve --host=127.0.0.1 --port=8099 &
curl -s -o /dev/null -w "%{http_code}\n" http://127.0.0.1:8099/<path>   # wajib 200
```

## 8. Larangan

- Jangan mengubah `routes/web.php`, `app/Http/Controllers/*`, `app/Support/*`, `components/senat/*`, `layouts/*` (kalau perlu, laporkan).
- Jangan mengubah file `storage/mock/*.json`.
- Jangan menambah dependency baru di luar stack Vite/Tailwind v4/Livewire yang sudah terpasang.
- Jangan mengarang data kelembagaan baru. Nama orang, komisi, nomor surat, NIP hanya dari data mock/SenatDirectory yang tersedia. Kalau desain menampilkan data yang tidak ada di mock, gunakan data mock terdekat dan jangan mengarang angka resmi.
