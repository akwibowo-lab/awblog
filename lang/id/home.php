<?php

return [
    'seo' => [
        'title' => 'Akut Wibowo | Pilot Drone Bersertifikat & AI-Assisted Builder',
        'description' => 'Portofolio resmi Akut Wibowo: operasi drone bersertifikat, pengembangan aplikasi web dengan AI, serta catatan lapangan pemetaan dan teknologi.',
    ],
    'hero' => [
        'name' => 'Akut Wibowo',
        'role' => 'Pilot Drone Profesional Bersertifikat & AI-Assisted Builder',
        'summary' => 'Menggabungkan ketelitian operasi udara untuk pemetaan dan inspeksi dengan pengembangan aplikasi web yang ringkas, terukur, dan siap dipakai di lapangan.',
        'cta_projects' => 'Lihat Galeri Aplikasi',
        'cta_contact' => 'Hubungi untuk Kolaborasi',
        'whatsapp_url' => 'https://wa.me/6289623084000',
        'orbit_alt' => 'Identitas visual AW Orbit milik Akut Wibowo',
    ],
    'certification' => [
        'heading' => 'Sertifikasi & Kapabilitas Drone',
        'intro' => 'Operasi terbang dilakukan mengikuti prosedur keselamatan udara, perencanaan misi terukur, dan dokumentasi hasil yang dapat diverifikasi.',
        'download_pdf' => 'Unduh Portofolio PDF',
        'items' => [
            [
                'code' => '01 / RPC',
                'title' => 'Sertifikasi Pilot Drone Komersial',
                'detail' => 'Kualifikasi operasi pesawat udara tanpa awak untuk kebutuhan komersial, survei area, dan kepatuhan ruang udara.',
            ],
            [
                'code' => '02 / MAP',
                'title' => 'Pemetaan & Fotogrametri Udara',
                'detail' => 'Akuisisi data ortofoto, model elevasi, dan pengolahan citra lapangan untuk kebutuhan dokumentasi teknis.',
            ],
            [
                'code' => '03 / OPS',
                'title' => 'Inspeksi Visual & Keselamatan Misi',
                'detail' => 'Pemeriksaan pra-terbang, manajemen risiko lokasi, dan pelaporan visual beresolusi tinggi.',
            ],
        ],
    ],
    'projects' => [
        'heading' => 'Galeri Aplikasi & Proyek',
        'intro' => 'Perangkat lunak dan sistem kerja yang dibangun untuk mendukung operasi lapangan, otomasi, dan alur kerja harian.',
        'types' => [
            'app' => 'Aplikasi Web',
            'drone' => 'Operasi Drone',
            'case_study' => 'Studi Kasus',
        ],
        'live_link' => 'Buka Aplikasi',
        'repo_link' => 'Repositori',
        'fallback_items' => [
            [
                'type' => 'Aplikasi Web',
                'title' => 'awork.id',
                'summary' => 'Platform manajemen kerja dan alur proyek untuk tim operasional.',
                'stack' => ['Laravel', 'MySQL', 'Tailwind CSS'],
                'url' => 'https://awork.id',
                'url_label' => 'Buka awork.id',
                'note' => null,
            ],
            [
                'type' => 'Operasi Drone',
                'title' => 'ODM Studio App',
                'summary' => 'Antarmuka pengelolaan pemrosesan citra udara dan keluaran pemetaan OpenDroneMap.',
                'stack' => ['Web App', 'OpenDroneMap', 'GIS'],
                'url' => null,
                'url_label' => null,
                'note' => 'Detail proyek sedang disiapkan untuk halaman Proyek.',
            ],
        ],
    ],
    'blog' => [
        'heading' => 'Tulisan Terbaru',
        'intro' => 'Catatan lapangan, uji perangkat, dan pembahasan teknis dari arsip blog.',
        'view_all' => 'Lihat semua tulisan',
        'empty' => 'Belum ada tulisan yang terbit saat ini.',
    ],
    'contact' => [
        'heading' => 'Diskusi Proyek atau Kolaborasi',
        'body' => 'Terbuka untuk misi pemetaan dan dokumentasi udara, konsultasi alur kerja drone, maupun pembangunan aplikasi web terfokus.',
        'whatsapp_label' => 'Hubungi via WhatsApp',
        'whatsapp_url' => 'https://wa.me/6289623084000',
        'email_label' => 'Kirim Email',
        'email_address' => 'akwibowo@gmail.com',
        'blog_label' => 'Baca Catatan di Blog',
        'rss_label' => 'Langganan RSS',
    ],
    'back_to_top' => 'Kembali ke atas',
];
