<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="robots" content="noindex, nofollow">
        <title>{{ $title }} — Akut Wibowo</title>
        <link rel="icon" type="image/svg+xml" href="{{ asset('branding/favicon.svg') }}">
        <style>
            :root {
                color-scheme: dark;
            }
            * {
                box-sizing: border-box;
            }
            body {
                margin: 0;
                min-height: 100vh;
                display: grid;
                place-items: center;
                padding: 1.5rem;
                background-color: #0a0c10;
                color: #f3f4f6;
                font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            }
            main {
                width: 100%;
                max-width: 34rem;
                border: 1px solid #1f2937;
                border-radius: 1rem;
                background: #11141b;
                padding: 2.25rem;
                text-align: center;
            }
            .badge {
                display: inline-block;
                margin-bottom: 1.25rem;
                padding: 0.25rem 0.75rem;
                border-radius: 9999px;
                background: rgba(245, 158, 11, 0.12);
                color: #f59e0b;
                font-size: 0.75rem;
                font-weight: 600;
                letter-spacing: 0.06em;
                text-transform: uppercase;
            }
            h1 {
                margin: 0 0 0.75rem;
                font-size: 1.5rem;
                line-height: 1.3;
                font-weight: 700;
                color: #ffffff;
            }
            p {
                margin: 0;
                font-size: 0.975rem;
                line-height: 1.65;
                color: #9ca3af;
            }
            .logo {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                margin-bottom: 1.25rem;
            }
        </style>
    </head>
    <body>
        <main>
            <div class="logo" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="-60 -60 120 120" width="44" height="44">
                    <path d="M47.2 -26.2 A54 54 0 1 1 26.2 -47.2" fill="none" stroke="#f3f4f6" stroke-width="3" stroke-linecap="round"/>
                    <circle cx="38.2" cy="-38.2" r="7" fill="#f59e0b"/>
                    <path d="M-35.5 18 L-20.5 -18 L-5.5 18 M-30.5 6 L-10.5 6" fill="none" stroke="#f3f4f6" stroke-width="4" stroke-linejoin="miter"/>
                    <path d="M-0.5 -18 L8.5 18 L17.5 -6.4 L26.5 18 L35.5 -18" fill="none" stroke="#f3f4f6" stroke-width="4" stroke-linejoin="miter"/>
                </svg>
            </div>
            <div class="badge">{{ $isBackend ? 'Admin Maintenance' : 'Maintenance Mode' }}</div>
            <h1>{{ $title }}</h1>
            <p>{{ $message }}</p>
        </main>
    </body>
</html>
