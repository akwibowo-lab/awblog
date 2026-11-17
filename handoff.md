# handoff.md — Serah Terima Antar Sesi

> Ditimpa (bukan ditambah) di akhir setiap sesi. Maks. ±60 baris. Riwayat lama pindah ke `log-kegiatan.md`.

## Status Saat Ini

- **Fase**: 1 — Setup proyek (perencanaan, mockup, dan branding v1 selesai)
- **Branch aktif**: -
- **Fitur sesi terakhir**: -
- **Status**: sesi 1 hilang bersama drive D laptop (belum ter-push). Pemulihan data dihentikan; keputusan pemilik: bangun ulang proyek ini saja memakai `prompt-sesi-1.md`
- **Terakhir diperbarui**: <tanggal>

## Fitur Sesi Berikutnya (hanya 1)

- **Nama**: Inisialisasi proyek Laravel + Filament + Tailwind
- **Tujuan**: kerangka Laravel 13 + Filament 5 + Tailwind berjalan lokal di `D:\AntigravitySync\AW` (akar repo), tes dasar lolos. Di luar lingkup: staging Hostinger, impor WordPress, skema blog, homepage lengkap. Prompt pembuka: `prompt-sesi-1.md`.
- **Kriteria selesai**:
  - [ ] `php artisan serve` jalan, halaman `/` tampil placeholder
  - [ ] Pint + 1 tes Pest dasar lolos
  - [ ] 1 tes E2E Playwright (halaman `/` terbuka, ada judul)
  - [ ] Login admin Filament berfungsi
- **Perlu verifikasi manual pemilik?**: ya (buka `/` dan `/admin` di browser)
- **Butuh approval?**: dependensi sesi ini sudah disetujui di prompt pembuka (laravel ^13, filament ^5, pest, playwright); selain itu tetap minta

## Keputusan yang Sudah Final

| Tanggal | Keputusan | Alasan |
|---|---|---|
| - | Stack: Laravel + Blade + Tailwind + Alpine + Filament | SEO baik, sederhana, hemat token |
| 2026-11-12 | Fakta WordPress lama: `permalink_structure` = `/%postname%/` (artikel `/judul-artikel/`), category/tag base default (`/category/x/`, `/tag/x/`), home/siteurl `https://www.akutwibowo.com`, 235 artikel terbit. URL artikel, kategori, tag dipertahankan persis; selebihnya 301 | jaga SEO |
| 2026-11-10 | Komentar lama dibuang, tidak diimpor | dianggap tidak penting |
| 2026-11-10 | Domain `akutwibowo.com`; hosting di Hostinger Premium (sudah menjalankan Laravel lain di `galangbahari.com`); rencana VPS untuk web porto ditunda | tanpa risiko ke awork, lebih sederhana |
| 2026-11-10 | Homepage bilingual ID/EN; blog tetap Indonesia | target klien drone lokal + internasional |
| 2026-11-10 | Tujuan utama situs: drone (sertifikasi, layanan, tombol unduh portofolio PDF) | prioritas bisnis |
| 2026-11-10 | PDF portofolio diunggah via dashboard, disimpan di server | mudah diperbarui tanpa deploy |
| 2026-11-10 | Aplikasi: ringkasan di homepage; ODM Studio = landing page sendiri (apa itu, prasyarat Docker dll, repo GitHub, spek); awork = tautan ke awork.id | pemisahan fungsi |
| 2026-11-10 | Arah visual: sinematik (gelap, nuansa langit/udara); detail palet dan font masih dalam diskusi | kuat untuk portofolio drone |
| 2026-11-12 | Nama merek: Akut Wibowo (nama lengkap) | konsisten dengan domain akutwibowo.com |
| 2026-11-10 | Inisial merek: AW (juga asal nama AWork). Logo dipilih: konsep 2 "Orbit" (AW dalam cincin bercelah, titik amber di celah) | identitas drone, tetap terbaca kecil |

## Keputusan Terbuka (perlu jawaban pemilik)

- [x] Permalink WP pasti: artikel `/%postname%/`; halaman juga tanpa `.html` (dikonfirmasi pemilik)
- [ ] Host kanonik: usulan tetap `https://www.akutwibowo.com` (sama dengan WP sekarang), non-www 301 ke www; belum disetujui
- [ ] URL gambar lama `/wp-content/uploads/...`: usulan tetap berfungsi untuk file asli, varian ukuran (`-300x200`) 301 ke file asli; belum disetujui
- [ ] Cek Hostinger Premium (oleh pemilik, hasilnya dicatat di sini): SSH aktif; `php -v` lewat SSH = 8.3 (versi CLI bisa beda dari versi web); `composer -V` = 2.x; ekstensi PHP untuk Laravel 13 + Filament 5 (intl, mbstring, gd/imagick, zip, dll); document root bisa ke `public/`; cron tersedia; sisa disk dan batas proses/inode cukup untuk blog + foto
- [ ] Cara memisahkan dari `galangbahari.com` di akun yang sama (folder, database, dan versi PHP sendiri)
- [ ] Jika Hostinger tidak memenuhi syarat: kembali ke opsi VPS (compose terpisah di `/opt/akutwibowo`, MySQL sendiri, tanpa Redis/Horizon) dan perlu tahu penerima trafik 80/443 di VPS
- [ ] Metode bahasa: usulan = `/` (ID) + `/en/`, saran berdasar Accept-Language, tanpa redirect paksa; belum disetujui
- [ ] Letak blog: usulan artikel tetap di root (`/slug/`), indeks blog di `/blog`; belum disetujui

## Desain & Branding (v1, diterima sementara)

- Mockup v1 (referensi visual): https://claude.ai/artifact/C69xYp4BuTP7oRSrvGbekA — isi masih placeholder `[...]`, jangan disalin sebagai teks final.
- Arah: sinematik gelap. Nama merek: Akut Wibowo. Logo: AW "Orbit" (berkas di `branding/`: `aw-orbit-dark.svg`, `aw-orbit-light.svg`, `favicon.svg`; favicon 16px = cincin + titik tanpa huruf).
- Token (usulan, jadikan CSS variables/Tailwind theme di satu tempat):
  - `--bg #0B1220`, `--surface #111B2E`, `--border #1B2A44`, `--text #F4F6FA`, `--muted #8A94A7`
  - `--accent #F5A524` (drone, CTA utama), `--accent-2 #5CC8FF` (aplikasi/teknologi)
- Font: Sora (judul 500/600), Inter (isi 400/500), JetBrains Mono (kode). Muat lewat Google Fonts dengan fallback sistem.
- Struktur homepage v1 (urutan): Header (logo, menu, ID|EN) → Hero (tagline, tombol Lihat layanan + Unduh portofolio PDF, foto setengah badan) → Sertifikasi & layanan → Aplikasi (AWork → awork.id; ODM Studio → `/apps/odm-studio`) → Tulisan terbaru → Kontak (WhatsApp) → Footer.
- Aksesibilitas: kontras teks ≥4.5:1, target sentuh ≥44px, hormati `prefers-reduced-motion`.

## Prasyarat dari Pemilik Sebelum Sesi 1 (centang bila sudah)

- [x] SSH Hostinger aktif. Hasil: PHP CLI 8.2.35, Composer 2.9.8. Laravel 13 butuh PHP 8.3, jadi CLI harus memakai 8.3 (cek keberadaan `/opt/alt/php83/usr/bin/php`; versi web juga harus 8.3). Ditangani di sesi 1, sebelum `composer install`.
- [ ] Data lama diunduh ulang ke folder di luar repo (hanya dibaca): ekspor tabel `c71gfp8_*` dari phpMyAdmin (gzip) dan arsip uploads dari server (verifikasi sha256 di bagian Infrastruktur)
- [ ] Nilai `permalink_structure` WordPress
- [ ] Foto setengah badan terbaik (tinggi asli ≥2000px) dan satu foto udara untuk latar hero
- [ ] Bahan teks: sertifikasi (nama, penerbit, nomor, masa berlaku), 3 layanan drone, ringkasan AWork dan ODM Studio, kontak WhatsApp/email
- [ ] PDF portofolio (ID, dan EN bila ada)
- [ ] Repo GitHub private dibuat (remote `origin`) SEBELUM sesi 1 diulang
- [ ] Syncthing: aktifkan File Versioning di perangkat cadangan dan jangan biarkan folder kosong menyebar ke perangkat lain

## Infrastruktur (fakta)

- VPS Biznet Gio NEO Lite MS 4.4: 4 vCPU, RAM 3,8 GiB, disk 58 GB, swap 1 GB, Ubuntu 24.04, Docker + Compose (versi dikunci `apt-mark hold`).
- awork.id (JANGAN DISENTUH): `/opt/awork`, Docker Compose, Laravel 13 + Filament 5, PHP 8.3-FPM, MySQL 8.4, Redis 7, Horizon, scheduler, Nginx di port 8080 (lokal). Memori normal ±1,5–2 GB.
- Proyek ini: stack disamakan (Laravel 13, Filament 5, PHP 8.3). Tugas berat (impor WP, konversi gambar) dijalankan di mesin dev, bukan di VPS.
- Akses SSH dan kunci dikelola pemilik; agent tidak menyentuh kunci/kredensial.
- Workspace lokal: `D:\AntigravitySync\AW` (akar repo). Data WordPress lama di luar repo (mis. `D:\wp-legacy`), jangan disalin ke repo.
- Hostinger: akun ini berisi banyak situs lain (jangan disentuh). WordPress lama di `~/domains/akutwibowo.com/public_html`, table prefix `c71gfp8_` (bukan `wp_`).
- Data lama: database `u735114786_AwKdm2025` berisi beberapa instalasi WordPress: tabel akutwibowo ber-prefix `c71gfp8_` (hanya puluhan MB), sedangkan prefix `wpu0_` dan `wpu0_2_` (±300 MB, milik instalasi lain) membuat dump besar. Impor HANYA tabel `c71gfp8_*`; sisanya diabaikan dan tidak disentuh. Impor lokal lewat CLI, bukan phpMyAdmin. Arsip uploads ±1,4 GB di home server sudah terverifikasi utuh (gzip OK, 31.288 entri sama dengan isi folder, sha256 `56afe558ac3ec22f237a701f423177f9b0799ee6521b962620c7a4886580371b`); belum diunduh ke PC pemilik. Sebagian besar isinya kemungkinan thumbnail otomatis WordPress, jadi impor media hanya memakai file asli.
- PHP web diatur per situs di hPanel (pilihan 8.2 sampai 8.5). Jangan ubah situs yang sedang hidup. Laravel diuji dulu di subdomain staging dengan PHP 8.3; PHP CLI 8.2 harus diganti ke binary 8.3.

## Peta Repo Ringkas (isi seiring berjalan)

```
app/         -
database/    -
resources/   -
tests/       -
```

## Backlog (urut prioritas, jangan dikerjakan tanpa dijadwalkan)

1. Inisialisasi proyek
1b. Staging Hostinger (subdomain, PHP 8.3 web dan CLI, deploy awal; semua langkah butuh approval)
2. Skema DB (posts, categories, tags, media, projects)
3. Impor WordPress ke staging (`wp_legacy`)
4. Impor media + penulisan ulang URL gambar
5. Halaman blog (daftar, detail, kategori, tag, pencarian)
6. SEO: sitemap, RSS, redirect 301, meta, OG
7. Homepage portofolio (hero, sertifikasi drone, galeri aplikasi, blog terbaru, kontak)
8. Halaman Proyek/Aplikasi (awork.id, ODM Studio App)
9. Admin Filament: kelola post, media, proyek
10. Performa & deploy

## Masalah / Catatan Teknis Terbuka

- -

## Checklist Verifikasi Manual untuk Pemilik

(Diisi agent bila sesi menyisakan pengecekan manual. Hapus setelah dikonfirmasi.)

- [ ] -

## Perintah Penting

```
php artisan serve
php artisan test --filter=<Fitur>
./vendor/bin/pint
npx playwright test <spec>
```

## Catatan untuk Sesi Berikutnya (maks. 5 baris)

- -
