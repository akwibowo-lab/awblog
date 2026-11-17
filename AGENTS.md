# AGENTS.md — Aturan Kerja Agent

Proyek: **Situs pribadi Laravel** (migrasi blog WordPress + homepage portofolio).
Pemilik: pilot drone profesional bersertifikat & AI-assisted builder. Bahasa komunikasi: **Indonesia**. Kode, nama variabel, commit: **Inggris**.

> Baca file ini penuh di awal sesi. Jangan dibaca ulang di tengah sesi.

---

## 1. Urutan Wajib Awal Sesi (maks. 3 langkah)

1. Baca `handoff.md` (sumber kebenaran status saat ini).
2. Baca bagian terakhir `log-kegiatan.md` (5 entri terakhir saja).
3. Konfirmasi ke pemilik dalam **maks. 5 baris**: fitur sesi ini, rencana langkah, file yang akan disentuh, bagian yang butuh approval.

Jangan memindai seluruh repo. Gunakan `grep`/pencarian terarah dan baca hanya file yang relevan.

## 2. Aturan Emas

1. **1 sesi = 1 fitur.** Jika muncul ide/bug di luar fitur, catat di `handoff.md` bagian *Backlog*, jangan dikerjakan.
2. **Rencana dulu, kode kemudian.** Untuk fitur >3 file, tulis rencana singkat dan tunggu "lanjut" dari pemilik.
3. **Perubahan kecil dan terarah.** Diff minimal; jangan refactor di luar lingkup; jangan ubah gaya kode yang sudah ada.
4. **Jangan mengarang.** Jika ragu skema/versi/API, cek di kode atau dokumentasi resmi, atau tanyakan.
5. **Selesai = terverifikasi.** Fitur belum selesai sebelum tes lolos (lihat bagian 5).

## 3. Hemat Token

- Jawaban singkat, tanpa pengantar/penutup panjang, tanpa mengulang isi file yang sudah ditampilkan.
- Baca file dengan rentang baris, bukan seluruhnya, kecuali file kecil.
- Jangan menempelkan log/output panjang: ringkas ke ≤10 baris + lokasi file.
- Jangan membuat ulang file utuh untuk perubahan kecil; gunakan edit parsial.
- Jangan menjelaskan konsep dasar kecuali diminta.
- Di akhir sesi, tulis ringkasan ke `handoff.md` agar sesi berikutnya tidak perlu menelusuri ulang.
- Jika konteks sesi sudah panjang/berat: berhenti, tulis `handoff.md`, sarankan sesi baru.

## 4. Stack & Konvensi

| Area | Pilihan |
|---|---|
| Backend | Laravel 13, PHP 8.3 (disamakan dengan awork) |
| Database | MySQL/MariaDB |
| Frontend | Blade + Tailwind CSS + Alpine.js (tanpa SPA kecuali disetujui) |
| Admin | Filament 5 (CRUD post, kategori, tag, media, proyek, unggah PDF portofolio) |
| Infra | Hostinger Premium (shared, SSH). Tanpa Docker/Redis/worker permanen: cache file/database, queue via scheduler + cron. Detail di `handoff.md` |
| Tes | Pest (unit/feature) + Playwright (E2E) |
| Gaya kode | Laravel Pint (`./vendor/bin/pint`) sebelum selesai |

Konvensi:
- Logika bisnis di `app/Actions` atau `app/Services`, controller tipis.
- Validasi di Form Request. Query berat di scope/model, hindari N+1 (`with()`).
- Semua teks tampilan publik dalam Bahasa Indonesia, disiapkan lewat `lang/` bila mungkin.
- SEO: setiap halaman publik punya `<title>`, meta description, canonical, Open Graph.
- Aksesibilitas dasar: `alt` pada gambar, kontras cukup, navigasi keyboard.
- Gambar: simpan di storage, turunan ukuran (thumbnail/medium/large) dan format WebP; wajib `loading="lazy"` kecuali hero.
- Slug dan URL artikel lama **harus dipertahankan** (SEO). Perubahan URL wajib punya redirect 301.

## 5. Verifikasi (Definition of Done)

Setiap fitur wajib:
1. `./vendor/bin/pint` bersih.
2. Tes Pest untuk logika baru lolos (`php artisan test --filter=<Fitur>`), jangan jalankan seluruh suite jika tidak perlu; jalankan penuh hanya di akhir fitur.
3. Minimal **1 skenario E2E Playwright** untuk alur utama fitur.
4. Cek manual dari pemilik **bila ditandai** di `handoff.md` (visual, responsif, alur yang sulit diotomasi). Agent menyiapkan **checklist manual** (langkah klik + hasil yang diharapkan), lalu berhenti dan menunggu hasil dari pemilik.
5. Update `handoff.md` dan tambahkan 1 baris ke `log-kegiatan.md`.
6. Commit dan push ke remote GitHub (bagian 8). Sesi belum selesai sebelum push berhasil.
7. Berikan **prompt siap salin** untuk sesi berikutnya berdasarkan `handoff.md`.

Jangan menandai selesai jika tes gagal. Jika tes dilewati, tulis alasannya di handoff.

## 6. Proses Kritis — WAJIB APPROVAL PEMILIK

Berhenti dan minta persetujuan eksplisit ("approve") **sebelum**:

- Menjalankan migrasi/impor terhadap database **non-lokal** atau data asli WordPress.
- `migrate:fresh`, `db:wipe`, `truncate`, `DROP`, atau menghapus data/kolom.
- Menghapus atau menimpa file/folder (termasuk `storage`, `uploads`, media).
- Mengubah `.env`, kredensial, kunci, konfigurasi server, DNS, SSL.
- Deploy, rilis, atau perubahan di server produksi.
- Menambah/mengubah dependency mayor (`composer require`, `npm install` paket baru) — sebutkan nama, alasan, dan ukuran/risikonya.
- Mengubah struktur URL/redirect, `robots.txt`, sitemap, atau kanonikal.
- `git push --force`, rewrite history, hapus branch.
- Perintah yang memakai jaringan eksternal atau mengirim data ke layanan pihak ketiga.
- **Apa pun yang menyentuh VPS**: VPS ini dipakai bersama aplikasi lain (awork di `/opt/awork`). Dilarang membaca/mengubah/menghentikan `/opt/awork`, layanan, database, port, atau konfigurasi web server milik awork. Proyek ini hidup di direktori, pengguna sistem, pool PHP-FPM, user database, dan server block terpisah. Setiap perubahan nginx/Apache, firewall, service, atau restart wajib approval dan harus menyertakan cara rollback.
- **Apa pun di akun Hostinger**: akun ini juga menjalankan situs lain (mis. `galangbahari.com`). Dilarang membaca/mengubah/menghapus folder, database, cron, atau pengaturan PHP milik situs lain. Proyek ini memakai folder, database, dan pengaturan sendiri. Mengubah versi PHP, cron, domain/DNS, atau pengaturan hPanel wajib approval dengan cara rollback.

Format permintaan approval (singkat):
```
APPROVAL DIPERLUKAN
Aksi    : <perintah/aksi tepat>
Alasan  : <1 kalimat>
Risiko  : <apa yang bisa salah>
Rollback: <cara mengembalikan>
```
Tanpa balasan "approve" yang jelas, jangan lanjut.

## 7. Keamanan & Data

- Jangan pernah menaruh rahasia (password, API key, token) di kode, log, atau commit.
- Data WordPress asli: hanya baca. Impor selalu ke database staging dahulu (`wp_legacy`), lalu dipetakan ke skema Laravel.
- Selalu backup (dump DB + salinan `uploads`) sebelum langkah destruktif yang sudah di-approve.
- Sanitasi HTML konten hasil impor (whitelist tag) sebelum ditampilkan.
- Dump WordPress memuat tabel instalasi lain (prefix `wpu0_`, `wpu0_2_`). Hanya tabel berprefix `c71gfp8_` yang boleh dibaca dan diimpor; tabel lain tidak dibaca, tidak diubah, tidak dikutip di log.
- Dump database dan arsip data tidak boleh ditaruh di dalam folder yang bisa diakses web (`public_html`, `public/`, `wp-content`). Taruh di luar document root.

## 8. Git

- Satu branch per fitur: `feat/<nama-fitur>`. Perbaikan: `fix/<nama>`.
- Commit kecil, pesan format: `feat: ...`, `fix: ...`, `chore: ...`, `docs: ...`.
- Jangan commit ke `main` langsung. Merge hanya setelah pemilik setuju.
- **Cadangan wajib**: remote GitHub (private) harus ada. Di akhir setiap sesi: commit lalu push branch fitur. Sebelum pemilik bepergian atau mengakhiri hari, pastikan semuanya sudah ter-push. Push pertama dan perubahan remote butuh approval. Jangan biarkan satu-satunya salinan kerja berada di satu disk.

## 9. Saat Terblokir

Jika terblokir >2 percobaan pada masalah yang sama: berhenti, jelaskan gejala + yang sudah dicoba (maks. 8 baris), tawarkan 2 opsi. Jangan mencoba-coba tanpa arah.

## 10. Rujukan

- `handoff.md` → status, keputusan, backlog, langkah berikut.
- `log-kegiatan.md` → riwayat ringkas per sesi.
- `skills.md` → prosedur baku (playbook) untuk tugas berulang.

<!-- antislop:start -->
## antislop
For UI, copy, people, mobile layout, or code comments work, read these installed skill files directly (use these paths even if a same-named global skill exists):
- Core filter, always on: `antislop`: `.agents/skills/antislop/SKILL.md`
- UI / visual: `antislop-ui`: `.agents/skills/antislop-ui/SKILL.md`
- Copy & text: `antislop-copywriting`: `.agents/skills/antislop-copywriting/SKILL.md`
- Mobile / responsive: `antislop-layoutmobile`: `.agents/skills/antislop-layoutmobile/SKILL.md`
- Code comments: `antislop-code`: `.agents/skills/antislop-code/SKILL.md`
Before starting, follow the core's "Two Usage Modes" section in strict order: explicit session instruction first, then global preference, then ask. A session instruction always wins. For a resolved mode, say `antislop active: <mode> (session override).` or `antislop active: <mode> (global preference).` once before presenting findings or making edits, using the actual mode and source. Acknowledging the user's request without naming the source does not replace this notice.
Only an explicit choice of antislop during or after selects a session mode. A request to review, audit, or avoid file edits does not select a mode; read the global preference in that case. Another skill's mode does not select antislop's mode.
If the mode is unresolved, ask during/after and end the response; wait for the answer before any UI review, planning, or concept. For read-only tasks, put the active-mode notice only at the start of the final answer, never in progress messages. For editing tasks, announce before the first edit and omit it from the final answer.
To update antislop later: `npx antislop-ai --update`, or run `npx antislop-ai` and pick Overwrite them.
<!-- antislop:end -->
