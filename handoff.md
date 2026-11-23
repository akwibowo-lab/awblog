# handoff.md — Serah Terima Antar Sesi

> Ditimpa (bukan ditambah) di akhir setiap sesi. Maks. ±60 baris. Riwayat lama pindah ke `log-kegiatan.md`.

## Status Saat Ini

- **Fase**: Pengembangan fitur di `laravel-dev` (siap uji manual & menunggu persetujuan pemilik untuk deploy ke produksi `https://akutwibowo.com`)
- **Branch aktif**: `feat/admin-2fa-maintenance` (belum di-merge ke `main` / belum di-deploy ke Hostinger)
- **Fitur sesi terakhir**: 2FA Google Authenticator (TOTP) untuk login `/admin` (tanpa form reset password web), menu **Pengaturan** (`Pengaturan Umum`) dengan toggle Maintenance Mode frontend & backend (`503` + token bypass + `php artisan maintenance:off`), serta penghapusan jejak `FilamentInfoWidget` di dashboard admin.
- **Status**: menunggu cek manual pemilik di `laravel-dev` & persetujuan deploy ke produksi (`https://akutwibowo.com`)
- **Terakhir diperbarui**: 2026-11-23

## Fitur Sesi Berikutnya (hanya 1)

- **Nama**: Deploy `feat/admin-2fa-maintenance` ke produksi (bila belum di-approve sesi ini) atau verifikasi & rapikan tampilan portofolio setelah pemilik mengisi data via `https://akutwibowo.com/admin`.
- **Tujuan**: Menjalankan 2FA TOTP + menu Pengaturan/Maintenance Mode di produksi atau memoles tampilan portofolio pasca-pengisian konten.

## Keputusan yang Sudah Final

| Tanggal | Keputusan | Alasan |
|---|---|---|
| - | Stack: Laravel 13 + Blade + Tailwind v4 + Alpine + Filament 5 + MariaDB | SEO baik, sederhana, selaras Hostinger & awork |
| 2026-11-12 | WP lama: `/%postname%/`, 235 artikel, URL artikel/kategori/tag dipertahankan persis; selebihnya 301 | Jaga SEO |
| 2026-11-10 | Domain `akutwibowo.com`; hosting Hostinger Premium; homepage bilingual ID/EN, blog ID | Target klien lokal + internasional |
| 2026-11-23 | Host kanonik produksi `https://akutwibowo.com` (non-www); `www.akutwibowo.com` redirect 301 ke non-www | Keputusan pemilik saat cutover |
| 2026-11-22 | Dev 1 SOT aktif di Proxmox LXC `CT 103 (laravel-dev)` IP `192.168.0.105` | Hemat RAM host 8 GB, multi-project Laravel via Remote SSH |
| 2026-11-23 | Produksi di `~/apps/awblog-prod` (DB `u735114786_AWblog`); staging `new.akutwibowo.com` dibersihkan | Uji coba dilakukan di `laravel-dev`, hemat resource Hostinger |
| 2026-11-23 | Rasio tampilan gambar baru (hero, thumbnail proyek & blog) standar `16:9` (`aspect-video`) | Menghindari cropping pada aset 16:9 dari pemilik |
| 2026-11-23 | 2FA `/admin` memakai `AppAuthentication` bawaan Filament 5 (TOTP + recovery codes) dengan `profile(isSimple: false)` tanpa reset password web | Tanpa tambah dependency baru, aman & terintegrasi |

## Infrastruktur & Data Lama

- **Workspace SOT aktif (Remote SSH)**: `ssh://akut@laravel-dev/home/akut/projects/awblog` (`192.168.0.105`).
- **Produksi Hostinger**: `https://akutwibowo.com` (`~/apps/awblog-prod`, DB `u735114786_AWblog`, symlink selektif di `~/domains/akutwibowo.com/public_html`), PHP 8.3.37.
- **Backup pra-isolasi**: `~/backups/wp-prod-20261123/` & `~/backups/awblog-newAW-20261123.sql.gz` di Hostinger.
- **Repo GitHub**: `git@github.com:akwibowo-lab/awblog.git` (private).

## Backlog (urut prioritas, jangan dikerjakan tanpa dijadwalkan)

- Verifikasi & rapikan tampilan portofolio setelah pemilik mengisi data via `https://akutwibowo.com/admin`
- Pastikan cron `schedule:run` untuk `~/apps/awblog-prod` aktif di hPanel

## Catatan untuk Sesi Berikutnya

- Jika deploy ke produksi di-approve: merge `feat/admin-2fa-maintenance` ke `main`, push `main`, jalankan `scripts/deploy-prod.sh` di Hostinger (termasuk `php artisan migrate --force` untuk kolom 2FA pada `users`).
- Di Playwright pada `laravel-dev`, jalankan `npx playwright test --workers=1`.
