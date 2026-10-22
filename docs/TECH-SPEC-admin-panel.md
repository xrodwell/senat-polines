# Technical Spec — Proposal UI Admin Panel Sekretariat Tunggal

Status: implemented UI preview; bukan rilis operasional. Target terverifikasi: `/home/ubuntu/projek/coba/senat-polines`, branch `feature/admin-panel`. Dokumen ini membedakan baseline lama dari target stack dan scope UI-only saat ini.

## 1. Context, scope, dan fakta checkout

Tujuan: visual panel bagi satu identitas `Admin Sekretariat` untuk meninjau dua area kerja. Tidak ada role lain, Super Admin, atau manajemen user.

Tahap proposal hanya mendesain UI. Tidak membuat atau mengubah auth, DB, migrasi, CRUD/persistence, upload, voting, audit, API/event, route, controller, view, layout, source aplikasi, atau dependency.

Fakta checkout terverifikasi saat dokumen ini direvisi:
- PHP `^8.3`, Laravel `^13.0`, Livewire `^4.4`, PHPUnit `^12.0`.
- Frontend dibangun dengan Vite `^8.0`, `@tailwindcss/vite` + Tailwind CSS `^4.3`, `laravel-vite-plugin ^3.1`. Tidak ada lagi Tailwind/Alpine CDN maupun `alpinejs` standalone.
- Token tema ada di `resources/css/app.css` (`@import "tailwindcss"` + `@theme` `senat-*`).
- Layout `layouts/public`, `layouts/portal`, dan `portal/login` memakai `@vite(['resources/css/app.css'])`.
- Route `GET /admin` → `App\Livewire\AdminPreview` (`admin.preview`), layout `layouts.admin-preview`, view `livewire/admin-preview.blade.php`.
- `vendor/` dan `node_modules/` terpasang; `composer update`, `npm install`, test, dan build sudah dijalankan.

### Fungsi stack target
| Stack | Fungsi |
|---|---|
| PHP `^8.3` | Runtime aplikasi Laravel. |
| Composer | Resolver dan lock dependency PHP. |
| Laravel `^13.0` | HTTP lifecycle, routing, Blade integration, test harness. |
| Blade | Render HTML server-side untuk public, portal, dan admin preview. |
| Livewire `^4.4` | State `active` pada panel `/admin`; tidak menyimpan data bisnis. |
| Alpine bundled | Runtime Alpine bawaan Livewire 4 untuk halaman Livewire; Alpine CDN/package standalone dihapus. |
| Tailwind `^4.3` | Utility CSS dan token `senat-*` dalam `resources/css/app.css`. |
| Vite `^8.0` | Build/load CSS lewat `@vite`; `laravel-vite-plugin ^3.1` menghubungkan asset Laravel. |
| PHPUnit `^12.0` | Test route, label, kelompok workspace, dan state preview. |

Stack ini tidak berarti backend fitur nyata tersedia: auth, RBAC, schema, CRUD, upload, voting, audit, QR, dan publish tetap di luar scope.

## 2. Arsitektur konteks

```text
Bos / Admin Sekretariat
  membuka proposal visual
    Dashboard global opsional
    Bagian Rapat
    Bagian Urus Website
      shell baca-saja + heading + status
      source terverifikasi saat build, bila disetujui
      atau state “Segera dihubungkan”

Tidak ada request write, auth boundary aplikasi, DB, API baru, event, upload, atau persistence.
```

Komponen proposal:
- Shell: identitas, sidebar, area konten, banner batas preview.
- Sidebar: `<aside>` + `<nav>` + dua `<section>` berlabel `Bagian Rapat` (7 layar) dan `Bagian Urus Website` (9 layar); Dashboard di atas kelompok. Responsif: sidebar penuh di atas konten pada mobile, kolom tetap 288px pada `lg`.
- State aktif: properti Livewire `$active` (sementara, tidak dipersistensi) diubah lewat `select()`; item aktif ditandai `aria-current="page"` plus label teks `Aktif`, bukan warna saja.
- Status: `Preview visual` untuk layar yang punya sumber, `Segera dihubungkan` untuk sisanya; saat ini semua layar memakai `Segera dihubungkan`.
- Screen shell: heading persis, label status teks, empty/unavailable state, affordance non-aksi.
- Source adapter masa depan: hanya dipertimbangkan setelah ACC dan verifikasi ulang checkout; tidak dirancang sebagai contract saat ini.

## 3. IA dan daftar layar

Sidebar menampilkan `Admin Sekretariat`, lalu tepat dua kelompok berikut. `Dashboard` global opsional berada di atas kelompok dan bukan kelompok ketiga.

| Kelompok | Layar | Heading persis |
|---|---|---|
| Global opsional | Dashboard | Dashboard |
| Bagian Rapat | Dashboard Rapat | Dashboard Rapat |
| Bagian Rapat | Jadwal & Buat Rapat | Jadwal & Buat Rapat |
| Bagian Rapat | Agenda | Agenda |
| Bagian Rapat | Presensi & QR | Presensi & QR |
| Bagian Rapat | Kuorum | Kuorum |
| Bagian Rapat | Notulensi & Tindak Lanjut | Notulensi & Tindak Lanjut |
| Bagian Rapat | E-Voting | E-Voting |
| Bagian Urus Website | Beranda, Hero & Informasi | Beranda, Hero & Informasi |
| Bagian Urus Website | Profil, Tugas & Struktur | Profil, Tugas & Struktur |
| Bagian Urus Website | Komisi & Direktori Anggota | Komisi & Direktori Anggota |
| Bagian Urus Website | Berita & Warta | Berita & Warta |
| Bagian Urus Website | Produk Hukum | Produk Hukum |
| Bagian Urus Website | Arsip & Laporan | Arsip & Laporan |
| Bagian Urus Website | Aspirasi, Tanggapan & Tracker | Aspirasi, Tanggapan & Tracker |
| Bagian Urus Website | Media & Dokumen | Media & Dokumen |
| Bagian Urus Website | Pengaturan Konten Publik | Pengaturan Konten Publik |

Tidak ada item role, user, permission, atau kelompok lain.

## 4. UX behavior dan content map

Memilih workspace membuka/menonjolkan daftar layarnya. Memilih layar menampilkan heading tabel IA, item aktif dengan teks/indikator bentuk selain warna, dan status. Dashboard global, bila dipakai, merangkum dua kelompok dan modul belum tersambung; tidak menyatakan status operasional.

Status teks:
- `Preview visual`: shell tersedia; tidak berarti data atau aksi aktif.
- `Segera dihubungkan`: source belum diverifikasi/tersedia atau aksi belum disambungkan.
- `Belum ada data dari sumber saat ini`: hanya setelah source nyata diverifikasi dan hasil baca kosong.
- `Sumber tidak tersedia`: source nyata yang telah dipilih gagal dibaca; bukan daftar kosong.

Affordance “Buat”, “Edit”, “Simpan”, “Unggah”, “Publikasi”, “Scan”, “Tetapkan”, atau “Vote” tidak boleh menjalankan aksi. Bila ditampilkan untuk demonstrasi, kontrol menjelaskan `Preview visual — aksi belum dihubungkan`; tidak mengirim request mutasi, mengubah state permanen, atau mengklaim sukses.

| Bagian | Cakupan visual | Batas |
|---|---|---|
| Rapat | Dashboard Rapat; jadwal/buat rapat; agenda; presensi/QR; kuorum; notulensi/tindak lanjut; e-voting | Tidak ada jadwal, check-in QR, kuorum, catatan, assignment, ballot, suara, atau hasil nyata. |
| Urus Website | beranda/hero; profil/struktur/tugas; komisi/direktori; berita; regulasi; arsip/laporan; aspirasi; media/dokumen; pengaturan tampilan/kontak | Tidak ada edit, publish/unpublish, upload, download baru, tanggapan, atau perubahan kontak/tampilan nyata. |

Data dummy tidak dipakai. Saat build nanti source existing hanya boleh tampil baca-saja jika keberadaan, kesesuaian, dan field aman diverifikasi ulang. Selain itu layar tetap shell dengan `Segera dihubungkan`.

## 5. Data model, API/event contract, dan migrasi

Tahap ini: tidak ada data model, DB, migrasi, API, event, request payload, response contract, atau persistence. Tidak mengasumsikan contract repository, mock JSON, `SenatDirectory`, route, atau field existing sebagai source of truth.

Opsi integrasi setelah ACC: evaluator memeriksa source yang benar-benar ada di checkout saat build, pemilik data, field aman render, serta batas baca-saja. Hasil verifikasi harus dicatat sebelum data tampil. Bila salah satu belum ada, gunakan unavailable state.

## 6. Auth, authorization, dan backend gate masa depan

Proposal tidak memiliki auth atau authorization. Label `Admin Sekretariat` adalah identitas visual, bukan sesi atau bukti akses. Tidak ada RBAC, Super Admin, role hierarchy, atau user management dalam desain ini.

Sebelum aksi nyata, gate wajib:
1. Bos/pemilik data menetapkan akses, pemilik data, klasifikasi, retensi, dan penghapusan.
2. PRD fungsi nyata terpisah disetujui per domain.
3. Desain auth/authorization per aksi dibuat tanpa memperluas scope proposal ini.
4. Desain schema, validasi server, transaksi, error handling, dan audit diputuskan sebelum write.
5. Upload butuh storage, validasi file, pemindaian, akses unduh, dan retensi.
6. QR, kuorum, dan e-voting butuh aturan integritas, eligibility, kerahasiaan, persetujuan hasil, dan review keamanan terpisah.

## 7. Error boundary, accessibility, responsive, observability

Error boundary UI:
- Gagal memilih layar tidak menghapus sidebar atau heading.
- Source belum diperiksa/tidak ada: `Segera dihubungkan`.
- Source sudah disetujui lalu hasil kosong: empty state jelas.
- Source sudah disetujui lalu gagal: unavailable state; jangan tampilkan detail internal, token, PII, atau fallback rekaan.
- Tidak ada klaim tersimpan, terunggah, tervalidasi, terautentikasi, atau vote tercatat.

Accessibility/responsive:
- Struktur heading logis; `nav` dan nama kontrol semantik.
- Semua item sidebar dan affordance dapat dicapai keyboard; fokus terlihat; urutan fokus sesuai baca.
- Item aktif dan status tidak dibedakan warna saja.
- Kontras cukup; target sentuh minimum 44px.
- Desktop sidebar; mobile navigasi tidak menutup akses layar. Konten satu kolom atau overflow terkelola pada layar sempit.

Observability: tidak ada telemetry/log/event baru. Jika fungsi nyata kelak dibuat, kebutuhan log, metrik, retention, akses log, dan larangan PII/token diputuskan bersama desain backend.

## 8. Dependency dan environment

- `composer.json`: `php ^8.3`, `laravel/framework ^13.0`, `livewire/livewire ^4.4`, `phpunit/phpunit ^12.0`.
- `package.json`: `@tailwindcss/vite ^4.3`, `tailwindcss ^4.3`, `vite ^8.0`, `laravel-vite-plugin ^3.1`; `alpinejs` standalone dihapus (Alpine dibundel Livewire).
- Perintah: `composer update`, `npm install`, `npm run build`, `./vendor/bin/phpunit`.
- `tailwind.config.js` tidak lagi dipakai; konfigurasi ada di `resources/css/app.css`.

## 9. Risiko dan trade-off

| Keputusan | Trade-off | Risiko / mitigasi |
|---|---|---|
| Shell tanpa data dummy | Preview kurang kaya | Jujur terhadap source; gunakan `Segera dihubungkan`. |
| Dua workspace tetap | Tidak memodelkan pembagian role | Sesuai keputusan Bos; satu identitas visual. |
| Tidak ada aksi nyata | Tidak dapat uji workflow operasional | Cegah aksi palsu dan data salah. |
| Existing route/view hanya opsi | Integrasi ditunda | Hindari klaim source/contract yang belum cocok. |
| Tanpa observability baru | Tidak ada metrik proposal | Tambah setelah data/access decision. |

Risiko utama: proposal dianggap sistem operasional; mock dianggap resmi; data sensitif terbuka bila future integration ceroboh; QR/kuorum/e-voting dianggap sah tanpa aturan integritas. Gate section 6 menahan risiko ini.

## 10. File source masa depan — proposal setelah ACC

Tidak ada perubahan source sekarang. Kandidat minimal berikut harus dikonfirmasi ulang, bukan instruksi file pasti:

| Kandidat `to-be-confirmed` | Tujuan jika ada ACC | Syarat |
|---|---|---|
| view baru di bawah `resources/views/` | shell/sidebar/panel visual | nama/path/layout dipilih setelah inspeksi checkout terbaru. |
| perubahan view/layout existing | integrasi visual bila Bos setuju | hanya jika layout existing cocok; approval eksplisit. |
| `routes/web.php` | entry UI bila benar-benar dibutuhkan | PRD baru mengizinkan route; belum diusulkan sebagai route/path tertentu. |
| controller/support class | hanya bila shell tidak dapat dibuat tanpa backend | verifikasi dan design review baru; tidak ada contract/data claim. |

Tidak mengusulkan model, migration, repository, API, controller, route, atau layout tertentu sebagai existing atau pasti dibuat.

## 11. Testing dan verification plan

```bash
composer update
npm install
npm run build
./vendor/bin/phpunit
php artisan serve --port=8099   # lalu cek GET /admin = 200
```

Cakupan otomatis: `GET /admin` 200, sidebar tepat dua kelompok + 16 layar, state `$active` berpindah via `select()`, layar tak dikenal diabaikan, label persis sesuai tabel IA.

## 12. Rollout dan rollback

Rollout sekarang: review/ACC dokumen proposal saja. Tidak ada deployment, flag, data, atau rollback aplikasi.

Setelah ACC: build visual shell terpisah, review Bos, lalu UAT checklist. Fungsi nyata hanya melalui PRD/desain baru dan gate section 6. Rollback UI kelak: hapus/nonaktifkan shell proposal tanpa menghapus data, sebab proposal tidak mencipta data.

## 13. Implementation plan dependency-ordered

| Urutan | Pekerjaan sesudah ACC | PRD / AC | Bukti |
|---|---|---|---|
| 1 | Konfirmasi IA dan batas UI-only dengan Bos. | FR-01, FR-02; AC-01 | ACC tercatat. |
| 2 | Inspeksi checkout terbaru; pilih atau tolak source baca-saja per layar. | FR-04; AC-03, AC-05, AC-08 | Matriks layar-source atau unavailable state. |
| 3 | Bangun visual shell dari file `to-be-confirmed`; jangan tambah backend. | FR-01–FR-03, FR-07; AC-01–AC-04, AC-07, AC-10 | Screenshot semua layar dan sidebar. |
| 4 | Tambah no-false-action behavior, empty/unavailable state. | FR-04–FR-07; AC-03, AC-05, AC-06, AC-08–AC-10 | Inspeksi request dan state checklist. |
| 5 | Uji responsive/accessibility. | FR-08; AC-02, AC-11 | Keyboard/mobile evidence. |
| 6 | Review Bos sebelum perluasan fungsi. | Semua FR/AC | Sign-off atau daftar revisi. |
| 7 | Jika fungsi nyata diminta, buat PRD/desain keamanan baru dan lewati backend gates. | Di luar scope proposal | Approval terpisah. |

## 14. Reconciliation dengan spec sebelumnya dan handoff

Spec sebelumnya harus dianggap digantikan pada poin berikut:
- Hapus klaim `/admin`, `AdminPreviewController`, `AdminModuleRegistry`, route map, 13 modul/anchor, injected contracts, registry data fields, dan file implementasi sebagai desain yang disetujui.
- Hapus klaim layout/sidebar existing tertentu dapat dipakai, source mock/API sebagai data panel, serta route operasi sebagai tujuan panel. Semua hanya opsi integrasi setelah verifikasi baru.
- Hapus desain auth/RBAC produksi yang menyebut Super Admin/role. Scope hanya satu identitas visual `Admin Sekretariat`; fungsi nyata menunggu keputusan akses terpisah.
- Hapus klaim runtime/HTTP/unit-test Laravel; `vendor/` tidak ada pada checkout saat verifikasi.

Handoff pengembang:
- Spec: `docs/TECH-SPEC-admin-panel.md`; PRD: `docs/PRD-admin-panel.md`.
- Branch: `feature/admin-panel`; jangan push dalam task ini.
- Batas: docs-only sekarang; jangan ubah source/dependency; jangan mengarang route, contract, field, layout, atau source.
- Verifikasi: empat command section 11.
- Risk register: section 9; backend/security gate: section 6.
