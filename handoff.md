# handoff.md — Serah Terima Antar Sesi

> Ditimpa (bukan ditambah) di akhir setiap sesi. Maks. ±60 baris. Riwayat lama pindah ke `log-kegiatan.md`.

## Status Saat Ini

- **Fase**: Produksi aktif di `https://akutwibowo.com` (DB produksi terisolasi & `main` tersinkron)
- **Branch aktif**: `main` (hasil merge `feat/prod-cutover`)
- **Fitur sesi terakhir**: Isolasi DB produksi (`u735114786_AWblog`, impor dari backup `~/backups/awblog-newAW-20261123.sql.gz` 242 post), update tampilan seluruh foto/thumbnail/hero ke rasio `16:9` (`aspect-video`) tanpa cropping di Blade (`home`, `projects/index`, `blog/index`, `post-card`), build aset Vite, deploy `main` ke `~/apps/awblog-prod`, & merge `feat/prod-cutover` ke `main`.
- **Status**: selesai (menunggu pemilik melengkapi konten portofolio via `https://akutwibowo.com/admin` & menambah cron produksi di hPanel)
- **Terakhir diperbarui**: 2026-11-23

## Fitur Sesi Berikutnya (hanya 1)

- **Nama**: Tambahkan 2FA Google Authenticator (TOTP) untuk login `/admin`.
- **Tujuan**: Mengamankan akses panel admin produksi dengan autentikasi dua faktor berbasis TOTP (tanpa form reset password web).

## Keputusan yang Sudah Final

| Tanggal | Keputusan | Alasan |
|---|---|---|
| - | Stack: Laravel 13 + Blade + Tailwind v4 + Alpine + Filament 5 + MariaDB | SEO baik, sederhana, selaras Hostinger & awork |
| 2026-11-12 | WP lama: `/%postname%/`, 235 artikel, URL artikel/kategori/tag dipertahankan persis; selebihnya 301 | Jaga SEO |
| 2026-11-10 | Domain `akutwibowo.com`; hosting Hostinger Premium; homepage bilingual ID/EN, blog ID | Target klien lokal + internasional |
| 2026-11-23 | Host kanonik produksi `https://akutwibowo.com` (non-www); `www.akutwibowo.com` redirect 301 ke non-www | Keputusan pemilik saat cutover |
| 2026-11-22 | Dev 1 SOT aktif di Proxmox LXC `CT 103 (laravel-dev)` IP `192.168.0.105` | Hemat RAM host 8 GB, multi-project Laravel via Remote SSH |
| 2026-11-23 | Produksi di `~/apps/awblog-prod` (DB `u735114786_AWblog`), staging di `~/apps/awblog-staging` (DB `u735114786_newAW`) | Isolasi penuh app & DB produksi dari staging |
| 2026-11-23 | Rasio tampilan gambar baru (hero, thumbnail proyek & blog) standar `16:9` (`aspect-video`) | Menghindari cropping pada aset 16:9 dari pemilik |

## Infrastruktur & Data Lama

- **Workspace SOT aktif (Remote SSH)**: `ssh://akut@laravel-dev/home/akut/projects/awblog` (`192.168.0.105`).
- **Produksi Hostinger**: `https://akutwibowo.com` (`~/apps/awblog-prod`, DB `u735114786_AWblog`, symlink selektif di `~/domains/akutwibowo.com/public_html`), PHP 8.3.37.
- **Backup pra-isolasi**: `~/backups/wp-prod-20261123/` & `~/backups/awblog-newAW-20261123.sql.gz` di Hostinger.
- **Staging Hostinger**: `https://new.akutwibowo.com` (`~/apps/awblog-staging`, DB `u735114786_newAW`, `public_html/new`).
- **Repo GitHub**: `git@github.com:akwibowo-lab/awblog.git` (private).

## Backlog (urut prioritas, jangan dikerjakan tanpa dijadwalkan)

- Tambahkan 2FA Google Authenticator (TOTP) untuk login `/admin` (tanpa form reset password web)
- Verifikasi & rapikan tampilan portofolio setelah pemilik mengisi data via `https://akutwibowo.com/admin`
- Pastikan cron `schedule:run` untuk `~/apps/awblog-prod` aktif di hPanel

## Catatan untuk Sesi Berikutnya

- Pemilik mengisi `site_settings`, foto hero 16:9, proyek, dan PDF portofolio langsung melalui `https://akutwibowo.com/admin`.
- Di Playwright pada `laravel-dev`, jalankan `npx playwright test --workers=1`.
