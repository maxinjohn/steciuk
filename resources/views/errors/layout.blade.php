<!DOCTYPE html>
<html lang="en-GB">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ $title ?? 'Something went wrong' }}</title>
    <style>
        :root { color-scheme: light dark; --bg:#faf9f7; --ink:#1c1917; --muted:#57534e; --brand:#1a2332; --accent:#b8892a; --surface:#fff; }
        @media (prefers-color-scheme: dark) {
            :root { --bg:#131316; --ink:#fafafa; --muted:#a1a1aa; --brand:#dbb45a; --accent:#dbb45a; --surface:#1c1c1f; }
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 1.5rem;
            font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: var(--bg);
            color: var(--ink);
        }
        .card {
            width: min(100%, 28rem);
            padding: 2rem;
            border-radius: 1.5rem;
            border: 1px solid color-mix(in srgb, var(--ink) 8%, transparent);
            background: var(--surface);
            text-align: center;
            box-shadow: 0 16px 40px color-mix(in srgb, var(--ink) 8%, transparent);
        }
        .code {
            display: inline-flex;
            margin-bottom: 1rem;
            padding: 0.35rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: var(--accent);
            background: color-mix(in srgb, var(--accent) 12%, transparent);
        }
        h1 {
            margin: 0 0 0.75rem;
            font-size: 1.5rem;
            line-height: 1.2;
            letter-spacing: -0.03em;
        }
        p {
            margin: 0;
            line-height: 1.6;
            color: var(--muted);
        }
        a {
            display: inline-flex;
            margin-top: 1.5rem;
            padding: 0.75rem 1.25rem;
            border-radius: 9999px;
            font-weight: 700;
            text-decoration: none;
            color: #1c1917;
            background: var(--accent);
        }
    </style>
</head>
<body>
    <main class="card">
        @yield('content')
    </main>
</body>
</html>
