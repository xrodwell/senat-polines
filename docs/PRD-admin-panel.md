# PRD: Proposal UI Admin Panel Sekretariat Tunggal

## 1. Rekam keputusan
- Status: implementasi UI preview disetujui; bukan rilis fitur operasional.
- Owner: Bos. Tanggal: 22 Oktober 2026. Versi: 1.0.
- Checkout terverifikasi: branch `feature/admin-panel`; `composer.json`; `package.json`; `routes/web.php`; layout Blade; dan daftar `storage/mock/*.json`.
- Baseline lama: PHP `^8.2`, Laravel 11, Blade, Tailwind CDN, Alpine CDN, tanpa build aset.
- Target terpasang: PHP `^8.3`, Laravel `^13.0`, Blade, Livewire `^4.4`, Tailwind CSS `^4.3` melalui Vite `^8.0` + `laravel-vite-plugin ^3.1`; `GET /admin` tersedia sebagai preview.
- Keputusan Bos: satu identitas UI bernama `Admin Sekretariat`. Tidak ada Super Admin, RBAC, hierarki role, sidebar role, atau manajemen user.
- Keputusan Bos: proposal hanya tampilan UI. Tidak ada auth, database, CRUD, persistence, upload, audit, atau voting nyata. Pratinjau diimplementasikan sebagai komponen Livewire `AdminPreview` di `/admin`.

## 1.1 Fungsi stack target
- PHP menjalankan runtime aplikasi.
- Composer menyelesaikan dan mengunci dependency PHP.
- Laravel memberi routing, HTTP lifecycle, view, dan test harness.
- Blade merender HTML server-side.
- Livewire mengelola state navigasi `/admin` sementara di browser/server request tanpa persistence.
- Alpine berasal dari bundle Livewire 4 pada halaman Livewire; tidak ada CDN atau package Alpine terpisah.
- Tailwind menyediakan utility CSS dan token `senat-*` melalui `@theme`.
- Vite memproses dan memuat CSS Tailwind pada layout Blade.
- PHPUnit menjalankan regression test UI route dan kontrak label.

Target stack tidak menambahkan backend fitur nyata: tidak ada auth, RBAC, database schema, CRUD, upload, QR scan, voting, audit, atau publish.

## 2. Problem dan target user
- Masalah: cakupan kerja sekretariat rapat dan pengelolaan isi situs belum dipresentasikan sebagai satu panel dengan batas kemampuan preview yang jujur.
- Target user: `Admin Sekretariat` tunggal.
- Job-to-be-done: meninjau peta kerja rapat dan isi situs dari satu sidebar, lalu memahami modul yang hanya visual atau belum tersambung.
- Nilai: Bos dapat menilai IA dan tampilan sebelum menyetujui build nyata.

## 3. Outcome
- Goal: proposal UI menampilkan dua kelompok kerja sidebar secara jelas, lengkap, dan tanpa aksi palsu.
- Sinyal sukses: pada UAT proposal, 100% layar pada daftar layar tampil; sidebar memuat tepat dua kelompok kerja; 100% modul tanpa sumber terverifikasi menampilkan `Segera dihubungkan`; 0 kontrol menyatakan perubahan tersimpan, unggahan selesai, vote tercatat, atau akses terautentikasi.
- Window ukur: satu sesi review/ACC Bos.
- Guardrail: data dummy tidak boleh dibuat untuk mengisi modul. Data yang dipakai hanya source existing yang diverifikasi saat build; jika tidak tersedia, gunakan visual shell/empty state.

## 4. Scope

### In scope
1. Shell proposal admin dengan landing Dashboard ringkas opsional. Dashboard bukan kelompok kerja ketiga.
2. Sidebar dengan tepat dua kelompok: `Bagian Rapat` dan `Bagian Urus Website`.
3. Layar visual, heading, label status, empty state, unavailable state, dan pratinjau baca-saja untuk seluruh modul pada daftar layar.
4. Pemakaian data/source existing hanya bila keberadaannya diverifikasi saat build. Modul lain memakai shell kosong berlabel `Segera dihubungkan`.
5. Tombol atau affordance visual non-aksi yang menjelaskan batas preview.

### Non-goals eksak
- Tidak ada Super Admin, multi-role, RBAC, role hierarchy, login, session, permission, atau manajemen user.
- Tidak ada auth, database, model, migrasi, API, CRUD, persistence, upload, download baru, audit log, notifikasi, integrasi penyimpanan, atau telemetry baru. State layar aktif hanya sementara di memori komponen Livewire.
- Tidak ada create/edit/jadwal sidang nyata, check-in QR nyata, kalkulasi/penetapan kuorum nyata, penulisan notulensi, tindak lanjut, atau e-voting nyata.
- Tidak ada publish, unpublish, edit konten, unggah media/dokumen, atau perubahan isi situs nyata.
- Dependency frontend/backend sudah dinaikkan (Laravel 13, Livewire 4, Tailwind 4, Vite 8, PHPUnit 12) sebagai prasyarat pratinjau; tidak ada paket tambahan di luar itu.
- Tidak ada klaim bahwa route, contract, field, layout, atau source tertentu tersedia selain yang diverifikasi di checkout saat build.

### Asumsi dan dependensi
- Asumsi diterima: semua aksi yang lazimnya menulis data adalah affordance visual non-aksi atau tidak ditampilkan.
- Asumsi diterima: `Segera dihubungkan` berarti source/aksi belum disambungkan; bukan kegagalan data dan bukan janji tanggal.
- Asumsi diterima: source existing hanya boleh dipakai baca-saja dan hanya setelah verifikasi checkout saat build.
- Dependensi: ACC Bos atas IA, cakupan layar, dan batas preview sebelum build UI.
- Open decision OD-01: Bos menyetujui atau merevisi daftar layar/IA proposal. Dampak: material; memblokir handoff build nyata.
- Open decision OD-02: Bos menetapkan batas akses dan pemilik data sebelum panel memproses data non-publik atau aksi nyata. Dampak: material; memblokir rilis produksi dan semua fungsi tulis.

## 5. Information architecture dan daftar layar UI

Sidebar menampilkan identitas tunggal `Admin Sekretariat`, lalu:

| Kelompok | Layar UI | Tujuan preview |
|---|---|---|
| Global opsional | Dashboard | Ringkasan dua kelompok kerja, status preview, dan daftar modul belum tersambung. Bukan kelompok kerja ketiga. |
| Bagian Rapat | Dashboard Rapat | Ringkasan rapat dan status modul rapat. |
| Bagian Rapat | Jadwal & Buat Rapat | Shell daftar/jadwal dan affordance buat/edit tanpa aksi nyata. |
| Bagian Rapat | Agenda | Shell agenda rapat. |
| Bagian Rapat | Presensi & QR | Shell presensi dan QR; tanpa scan/check-in nyata. |
| Bagian Rapat | Kuorum | Shell status kuorum; tanpa penetapan/kalkulasi operasional nyata. |
| Bagian Rapat | Notulensi & Tindak Lanjut | Shell catatan dan tindak lanjut; tanpa simpan atau assignment nyata. |
| Bagian Rapat | E-Voting | Shell voting; tanpa ballot, suara, atau hasil nyata. |
| Bagian Urus Website | Beranda, Hero & Informasi | Shell pengaturan tampilan konten beranda. |
| Bagian Urus Website | Profil, Tugas & Struktur | Shell konten kelembagaan. |
| Bagian Urus Website | Komisi & Direktori Anggota | Shell daftar komisi/anggota. |
| Bagian Urus Website | Berita & Warta | Shell editorial berita. |
| Bagian Urus Website | Produk Hukum | Shell regulasi/produk hukum. |
| Bagian Urus Website | Arsip & Laporan | Shell arsip dan laporan. |
| Bagian Urus Website | Aspirasi, Tanggapan & Tracker | Shell aspirasi dan status tanggapan. |
| Bagian Urus Website | Media & Dokumen | Shell media/dokumen; tanpa upload nyata. |
| Bagian Urus Website | Pengaturan Konten Publik | Shell tampilan, kontak, dan pengaturan konten publik. |

## 6. User journeys

### US-01: Memilih area kerja
Sebagai `Admin Sekretariat`, ingin memilih Bagian Rapat atau Bagian Urus Website, agar pekerjaan terkelompok tanpa role berbeda.

Alur utama:
1. Admin membuka proposal panel.
2. Admin melihat Dashboard opsional, lalu dua kelompok sidebar.
3. Admin memilih satu layar dalam kelompok.
4. Panel menampilkan heading layar dan status preview.

Alur alternatif:
1. Admin membuka layar yang belum memiliki source terverifikasi.
2. Panel menampilkan visual shell/empty state `Segera dihubungkan`.

Acceptance criteria:
- [ ] AC-01 Given panel terbuka, when sidebar dirender, then identitas `Admin Sekretariat` terlihat dan sidebar memuat tepat `Bagian Rapat` serta `Bagian Urus Website`; Dashboard, bila ada, tidak diberi label kelompok kerja.
- [ ] AC-02 Given admin memilih item sidebar, when layar ditampilkan, then heading persis cocok dengan nama layar pada daftar IA dan item aktif dapat dibedakan tidak hanya dengan warna.
- [ ] AC-03 Given source layar belum diverifikasi atau tidak tersedia, when layar dibuka, then terlihat `Segera dihubungkan`, penjelasan singkat batas preview, dan tidak ada data pengganti rekaan.

### US-02: Meninjau pekerjaan rapat
Sebagai `Admin Sekretariat`, ingin meninjau semua domain rapat dalam satu kelompok, agar Bos dapat menilai cakupan UI rapat.

Alur utama:
1. Admin membuka `Bagian Rapat`.
2. Admin membuka Dashboard Rapat, Jadwal & Buat Rapat, Agenda, Presensi & QR, Kuorum, Notulensi & Tindak Lanjut, atau E-Voting.
3. Admin membaca status source dan batas preview tiap layar.

Alur alternatif:
1. Admin menekan affordance buat, edit, scan, tetapkan kuorum, simpan notulen, tindak lanjuti, atau vote.
2. Panel tidak menjalankan mutasi dan menjelaskan bahwa aksi belum dihubungkan.

Acceptance criteria:
- [ ] AC-04 Given `Bagian Rapat` dibuka, when daftar layar dirender, then ketujuh layar rapat pada IA tersedia.
- [ ] AC-05 Given layar rapat memiliki source existing yang diverifikasi, when data ditampilkan, then tampilan hanya baca-saja dan asal source ditandai.
- [ ] AC-06 Given admin mengaktifkan affordance tulis rapat, when interaksi dilakukan, then tidak ada request mutasi, perubahan UI permanen, klaim sukses, atau klaim vote/presensi/kuorum tersimpan; batas preview terlihat.

### US-03: Meninjau pengelolaan isi situs
Sebagai `Admin Sekretariat`, ingin meninjau domain isi situs dalam satu kelompok, agar cakupan editorial terlihat sebelum fungsi nyata disetujui.

Alur utama:
1. Admin membuka `Bagian Urus Website`.
2. Admin memilih satu dari sembilan layar konten situs.
3. Admin meninjau shell atau data existing baca-saja yang terverifikasi.

Alur alternatif:
1. Layar perlu data/media/document yang belum ada.
2. Panel menampilkan empty/unavailable state `Segera dihubungkan`.

Acceptance criteria:
- [ ] AC-07 Given `Bagian Urus Website` dibuka, when daftar layar dirender, then kesembilan layar website pada IA tersedia.
- [ ] AC-08 Given source konten belum tersedia, when layar terkait dibuka, then state `Segera dihubungkan` membedakan unavailable dari daftar kosong dan tidak menampilkan data palsu.
- [ ] AC-09 Given admin mengaktifkan affordance edit, publikasi, atau upload, when interaksi dilakukan, then tidak ada persistence, upload, perubahan publikasi, atau klaim berhasil.

### US-04: Memahami batas proposal
Sebagai Bos atau `Admin Sekretariat`, ingin melihat status preview tiap layar, agar ACC tidak menganggap UI sebagai sistem operasional.

Acceptance criteria:
- [ ] AC-10 Given layar UI mana pun dibuka, when status dibaca, then label status menyatakan `Preview visual` atau `Segera dihubungkan`, serta tidak mengklaim auth, CRUD, audit, atau integrasi aktif.
- [ ] AC-11 Given keyboard saja digunakan, when sidebar dan affordance visual dinavigasi, then fokus terlihat, urutan fokus logis, dan kontrol memiliki nama semantik.

## 7. Functional requirements
| ID | Requirement | Priority | Traceability | Bukti rilis |
|---|---|---|---|---|
| FR-01 | Panel must memakai satu identitas `Admin Sekretariat` tanpa konsep role atau user management. | Must | US-01, AC-01 | Screenshot UAT sidebar/identitas. |
| FR-02 | Panel must menyediakan tepat dua kelompok kerja sidebar dan seluruh layar IA di bawah kelompoknya. | Must | US-01, AC-01; US-02, AC-04; US-03, AC-07 | Checklist IA dan screenshot tiap kelompok. |
| FR-03 | Panel must menampilkan heading tepat serta state aktif yang dapat dibedakan selain warna. | Must | US-01, AC-02 | Uji keyboard dan screenshot state aktif. |
| FR-04 | Panel must memakai data existing hanya setelah source diverifikasi; selain itu menampilkan shell/empty state `Segera dihubungkan`. | Must | US-01, AC-03; US-02, AC-05; US-03, AC-08 | Matriks layar-source atau screenshot unavailable. |
| FR-05 | Panel must menjaga semua domain rapat sebagai preview baca-saja tanpa mutasi nyata. | Must | US-02, AC-04–AC-06 | Rekaman interaksi dan inspeksi request jaringan. |
| FR-06 | Panel must menjaga semua domain website sebagai preview baca-saja tanpa persistence, publish, atau upload nyata. | Must | US-03, AC-07–AC-09 | Rekaman interaksi dan inspeksi request jaringan. |
| FR-07 | Panel must menampilkan batas `Preview visual`/`Segera dihubungkan` pada layar terkait tanpa klaim kemampuan backend. | Must | US-04, AC-10 | Checklist semua layar. |
| FR-08 | Panel must mendukung navigasi keyboard, fokus terlihat, label semantik, dan status yang tidak hanya dibedakan warna. | Must | US-04, AC-11 | Uji keyboard dan inspeksi semantik. |

## 8. Non-functional requirements
- Performance: halaman `/admin` dirender server (Livewire) tanpa request tambahan saat pertama dibuka; aset CSS dibangun lewat Vite.
- Security/privacy: preview bukan kontrol akses. Tidak tampilkan kredensial atau data pribadi baru. Kebijakan akses dan klasifikasi data adalah OD-02.
- Reliability: source unavailable menghasilkan state eksplisit, bukan fallback data rekaan. Tidak ada transaksi, retry, backup, atau audit baru.
- Accessibility: keyboard, fokus terlihat, label semantik, kontras memadai, status bukan warna saja, dan responsif mobile/desktop.
- Compatibility: tetap pada fakta stack terverifikasi. Target browser/device belum ditetapkan.

## 9. Batas data dan eksternal
- Source of truth produksi, pemilik data, kontrak API, retensi, dan penghapusan belum dikonfirmasi. Tidak boleh disimpulkan dari mock, contracts, atau desain.
- Checkout memuat `storage/mock/*.json`, repository contracts, serta `SenatDirectory`; keberadaan ini bukan persetujuan untuk memakainya pada setiap layar dan bukan bukti source of truth produksi.
- `routes/web.php` dan `DESIGN_SPEC.md` ada di checkout, namun proposal ini tidak menambah atau mengubah route, controller, view, layout, atau contract.
- Input/output tahap proposal: navigasi dan tampilan baca-saja saja. Tidak ada import, export, upload, write API, atau persistence.

## 10. Release dan definition of done
- Release saat ini: artefak proposal UI untuk review/ACC, bukan rilis operasional atau produksi.
- Setiap FR Must harus memiliki story, AC, dan bukti seperti tabel FR sebelum proposal dinyatakan siap dipresentasikan.
- Bukti wajib: screenshot Dashboard bila dipakai; screenshot dua kelompok/sidebar; screenshot tiap layar atau matriks yang mencakup seluruh layar; contoh `Segera dihubungkan`; rekaman/inspeksi network untuk affordance non-aksi; uji keyboard; checklist label status.
- Cek repository: `git diff --check` lulus, `npm run build` lulus, `./vendor/bin/phpunit` lulus (5 test).
- Pratinjau UI sudah dibangun setelah permintaan eksplisit; rilis produksi/fungsi nyata tetap menunggu OD-02.

## 11. Roadmap setelah ACC
1. ACC Bos atas IA, daftar layar, dan batas preview.
2. Arsitek memverifikasi checkout terkini dan memilih hanya source existing yang benar-benar dapat dibaca; sisanya tetap `Segera dihubungkan`.
3. Build proposal UI sesuai PRD, tanpa menambah fungsi nyata.
4. Review Bos atas proposal UI.
5. Sebelum fungsi nyata: putuskan akses/pemilik data, lalu tulis PRD terpisah untuk kebutuhan auth, persistence, workflow, dan keamanan domain rapat/e-voting bila diminta.

## 12. Risiko dan open decisions
| ID | Risiko/keputusan | Dampak | Owner | Batas keputusan |
|---|---|---|---|---|
| R-01 | UI disalahpahami sebagai sistem operasional. | Tinggi: aksi palsu atau ekspektasi salah. | Bos + arsitek | Sebelum review UI. |
| R-02 | Source existing tidak cocok atau tidak tersedia bagi suatu layar. | Sedang: layar harus shell kosong. | Arsitek | Saat verifikasi build. |
| R-03 | Data mock dianggap data resmi. | Tinggi: informasi salah. | Bos + pemilik data | Sebelum penggunaan produksi. |
| R-04 | QR, kuorum, dan e-voting dibangun tanpa aturan integritas. | Tinggi: hasil operasional tidak sah. | Bos + pemilik kebijakan | Sebelum tahap fungsi nyata. |
| OD-01 | ACC Bos atas IA dan scope proposal. | Material: blokir handoff build UI. | Bos | Sebelum build. |
| OD-02 | Batas akses, pemilik data, klasifikasi, retensi, dan aturan aksi nyata. | Material: blokir produksi/fungsi tulis. | Bos + pemilik data | Sebelum tahap fungsi nyata. |

## Handoff arsitek-teknis
- PRD: `docs/PRD-admin-panel.md` pada branch `feature/admin-panel`.
- Asumsi diterima: UI-only; satu `Admin Sekretariat`; dua kelompok sidebar; data hanya bila source existing diverifikasi; lainnya `Segera dihubungkan`.
- Open decision: OD-01 dan OD-02. OD-01 memblokir build UI sampai ACC; OD-02 memblokir produksi dan semua fungsi nyata.
- Risiko utama: R-01, R-03, R-04.
- Acceptance criteria wajib: AC-01 s.d. AC-11. Jangan mengarang source, route, contract, field, atau data untuk memenuhi AC.
