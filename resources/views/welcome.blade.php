<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Akut Wibowo</title>
        <meta name="description" content="Portofolio dan catatan Akut Wibowo — pilot drone profesional bersertifikat & AI-assisted builder.">
        <link rel="canonical" href="{{ url('/') }}">
        <meta property="og:title" content="Akut Wibowo">
        <meta property="og:description" content="Pilot drone profesional bersertifikat & AI-assisted builder.">
        <meta property="og:type" content="website">
        <meta property="og:url" content="{{ url('/') }}">
        <link rel="icon" type="image/svg+xml" href="{{ asset('branding/favicon.svg') }}">

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500&family=JetBrains+Mono:wght@400&family=Sora:wght@500;600&display=swap" rel="stylesheet">

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-brand-bg text-brand-text font-sans antialiased flex items-center justify-center px-6">
        <main class="w-full max-w-xl rounded-2xl border border-brand-border bg-brand-surface p-8 sm:p-10 text-center shadow-2xl">
            <div class="mx-auto mb-6 flex h-20 w-20 items-center justify-center">
                <img
                    src="{{ asset('branding/aw-orbit-dark.svg') }}"
                    alt="Logo Akut Wibowo"
                    class="h-16 w-16"
                >
            </div>

            <h1 class="font-display text-3xl sm:text-4xl font-semibold tracking-tight text-brand-text">
                Akut Wibowo
            </h1>

            <p class="mt-3 text-base sm:text-lg text-brand-muted">
                Pilot Drone Bersertifikat &amp; AI-Assisted Builder
            </p>

            <div class="mt-6 inline-flex items-center gap-2 rounded-full border border-brand-border bg-brand-bg/60 px-4 py-1.5 text-xs font-mono text-brand-accent">
                <span class="h-2 w-2 rounded-full bg-brand-accent" aria-hidden="true"></span>
                <span>Situs sedang disiapkan</span>
            </div>
        </main>
    </body>
</html>
