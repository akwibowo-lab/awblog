# log-kegiatan.md — Riwayat Sesi

> **Append-only.** Tambahkan 1 entri di bagian paling bawah di akhir setiap sesi. Jangan edit entri lama. Agent hanya membaca 5 entri terakhir.

## Format Entri

```
### S<nomor> | <YYYY-MM-DD> | <fitur>
- Hasil   : selesai / sebagian / diblokir
- Dikerjakan: <1–3 poin singkat>
- File utama: <maks. 5 path>
- Tes     : pint ✔/✘ | pest ✔/✘ | e2e ✔/✘ | manual ✔/✘/n.a.
- Approval: <aksi kritis yang di-approve, atau ->
- Lanjut  : <1 baris>
```

---

### S0 | <tanggal> | Perencanaan
- Hasil   : selesai
- Dikerjakan: blueprint, struktur file agent (AGENTS, handoff, log, skills)
- File utama: AGENTS.md, handoff.md, log-kegiatan.md, skills.md
- Tes     : n.a.
- Approval: -
- Lanjut  : diskusi blueprint → mockup & branding → S1 inisialisasi proyek

### S1 | 2026-11-16 | Inisialisasi proyek (percobaan pertama)
- Hasil   : hilang
- Dikerjakan: sesi 1 selesai di lokal, lalu drive D laptop terhapus sebelum sempat di-push/sinkron; recovery oleh pemilik gagal
- File utama: -
- Tes     : n.a.
- Approval: -
- Lanjut  : bangun ulang sesi 1 dari awal (repo GitHub private dulu, push di akhir sesi)


### S1 | 2026-11-17 | Inisialisasi proyek Laravel + Filament + Tailwind
- Hasil   : selesai
- Dikerjakan: setup Laravel 13, Filament 5 (/admin), Pest 4, Playwright, Tailwind v4 token brand & placeholder `/`
- File utama: composer.json, .env.example, resources/css/app.css, resources/views/welcome.blade.php, tests/Feature/HomeTest.php
- Tes     : pint ✔ | pest ✔ | e2e ✔ | manual ✔
- Approval: bersihkan folder sisa parsial; repo GitHub origin; dependensi sesi (laravel 13, filament 5, pest, playwright)
- Lanjut  : staging Hostinger (1b) atau skema DB blog (2)


### S2 | 2026-11-22 | Skema DB (`posts`, `categories`, `tags`, `media`, `projects`) + Model & Relasi
- Hasil   : selesai
- Dikerjakan: 7 migrasi skema konten & pivot WP-ready, 2 enum (`ContentStatus`, `ProjectType`), 5 model + relasi `User::posts()`, 5 factory, dan tes Pest `DatabaseSchemaTest`
- File utama: database/migrations/2026_11_22_000001..000007_*.php, app/Models/Post.php, app/Models/Project.php, app/Enums/ContentStatus.php, tests/Feature/DatabaseSchemaTest.php
- Tes     : pint ✔ | pest ✔ (9 passed) | e2e ✔ (1 passed) | manual n.a.
- Approval: -
- Lanjut  : Impor WordPress (`c71gfp8_*`) ke staging `wp_legacy` (atau Staging Hostinger 1b)