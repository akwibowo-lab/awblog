<?php

return [
    'seo' => [
        'title' => 'Akut Wibowo | Certified Drone Pilot & AI-Assisted Builder',
        'description' => 'Official portfolio of Akut Wibowo: certified drone operations, AI-assisted web application development, and field notes on mapping and technology.',
    ],
    'hero' => [
        'name' => 'Akut Wibowo',
        'role' => 'Certified Professional Drone Pilot & AI-Assisted Builder',
        'summary' => 'Combining precision aerial operations for mapping and inspection with lean, measurable, field-ready web application development.',
        'cta_projects' => 'View App Gallery',
        'cta_contact' => 'Get in Touch',
        'whatsapp_url' => 'https://wa.me/6289623084000',
        'orbit_alt' => 'AW Orbit visual identity of Akut Wibowo',
    ],
    'certification' => [
        'heading' => 'Drone Certification & Capabilities',
        'intro' => 'Flight operations follow strict airspace safety procedures, measurable mission planning, and verifiable output documentation.',
        'download_pdf' => 'Download Portfolio PDF',
        'items' => [
            [
                'code' => '01 / RPC',
                'title' => 'Commercial Drone Pilot Certification',
                'detail' => 'Qualified unmanned aircraft operations for commercial missions, area surveys, and airspace compliance.',
            ],
            [
                'code' => '02 / MAP',
                'title' => 'Aerial Mapping & Photogrammetry',
                'detail' => 'Orthophoto acquisition, elevation modeling, and field imagery processing for technical documentation.',
            ],
            [
                'code' => '03 / OPS',
                'title' => 'Visual Inspection & Mission Safety',
                'detail' => 'Pre-flight checks, site risk management, and high-resolution visual reporting.',
            ],
        ],
    ],
    'projects' => [
        'heading' => 'App & Project Gallery',
        'intro' => 'Software and workflow systems built to support field operations, automation, and daily team execution.',
        'view_all' => 'View all projects',
        'types' => [
            'app' => 'Web App',
            'drone' => 'Drone Operations',
            'case_study' => 'Case Study',
        ],
        'live_link' => 'App',
        'repo_link' => 'GitHub',
        'fallback_items' => [
            [
                'slug' => 'awork-id',
                'type' => 'Web App',
                'title' => 'awork.id',
                'summary' => 'Work management and project workflow platform for operational teams.',
                'stack' => ['Laravel', 'MySQL', 'Tailwind CSS'],
                'url' => 'https://awork.id',
                'url_label' => 'Website',
                'note' => null,
            ],
            [
                'slug' => 'odm-studio-app',
                'type' => 'Drone Operations',
                'title' => 'ODM Studio App',
                'summary' => 'Management interface for aerial imagery processing and OpenDroneMap mapping outputs.',
                'stack' => ['Web App', 'OpenDroneMap', 'GIS'],
                'url' => null,
                'url_label' => null,
                'note' => 'Project details are being prepared for the Projects page.',
            ],
        ],
    ],
    'blog' => [
        'heading' => 'Latest Articles',
        'intro' => 'Field notes, hardware tests, and technical write-ups from the blog archive (in Indonesian).',
        'view_all' => 'View all articles',
        'empty' => 'No published articles yet.',
    ],
    'contact' => [
        'heading' => 'Project Discussion or Collaboration',
        'body' => 'Open for aerial mapping and documentation missions, drone workflow consulting, and focused web application builds.',
        'whatsapp_label' => 'Message on WhatsApp',
        'whatsapp_url' => 'https://wa.me/6289623084000',
        'email_label' => 'Send Email',
        'email_address' => 'akwibowo@gmail.com',
        'blog_label' => 'Read Blog Notes',
        'rss_label' => 'Subscribe RSS',
    ],
    'back_to_top' => 'Back to top',
];
