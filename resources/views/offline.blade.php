<!DOCTYPE html>
<html lang="en-GB">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#d4cabb">
    <title>Offline — STECI UK Parish</title>
    <style>
        :root {
            --offline-bg: #f5f0e8;
            --offline-bg-2: #ebe4d8;
            --offline-surface: rgba(255, 255, 255, 0.92);
            --offline-ink: #1c1917;
            --offline-muted: #6b5f52;
            --offline-accent: #b8892a;
            --offline-brand: #1a2332;
            --offline-border: rgba(26, 35, 50, 0.1);
        }
        @media (prefers-color-scheme: dark) {
            :root {
                --offline-bg: #0f1115;
                --offline-bg-2: #17141f;
                --offline-surface: rgba(24, 24, 27, 0.92);
                --offline-ink: #fafafa;
                --offline-muted: #a8a29e;
                --offline-accent: #dbb45a;
                --offline-brand: #f5f0e8;
                --offline-border: rgba(255, 255, 255, 0.08);
            }
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            min-height: 100dvh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            color: var(--offline-ink);
            background:
                radial-gradient(ellipse 80% 55% at 50% -10%, rgba(251, 191, 36, 0.18), transparent 55%),
                linear-gradient(180deg, var(--offline-bg), var(--offline-bg-2));
            padding: max(1.5rem, env(safe-area-inset-top)) max(1.5rem, env(safe-area-inset-right)) max(1.5rem, env(safe-area-inset-bottom)) max(1.5rem, env(safe-area-inset-left));
            text-align: center;
        }
        .card {
            position: relative;
            overflow: hidden;
            max-width: 24rem;
            width: 100%;
            padding: 2rem 1.5rem;
            background: var(--offline-surface);
            backdrop-filter: blur(16px);
            border-radius: 1.75rem;
            border: 1px solid var(--offline-border);
            box-shadow: 0 24px 60px rgba(26, 35, 50, 0.12);
        }
        .card::before {
            content: '';
            position: absolute;
            inset: 0 auto auto 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(90deg, transparent, var(--offline-accent), transparent);
        }
        .mark {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 3rem;
            height: 3rem;
            margin-bottom: 1rem;
            border-radius: 1rem;
            background: linear-gradient(135deg, rgba(251, 191, 36, 0.22), rgba(184, 137, 42, 0.12));
            color: var(--offline-accent);
            font-size: 1.125rem;
            font-weight: 700;
        }
        h1 { font-size: clamp(1.35rem, 4vw, 1.65rem); font-weight: 800; letter-spacing: -0.03em; margin-bottom: 0.75rem; }
        p { color: var(--offline-muted); line-height: 1.65; margin-bottom: 1.5rem; font-size: 0.98rem; }
        a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 2.85rem;
            padding: 0.75rem 1.5rem;
            background: linear-gradient(135deg, #dbb45a, #b8892a);
            color: #1c1917;
            text-decoration: none;
            border-radius: 9999px;
            font-weight: 700;
            box-shadow: 0 10px 24px rgba(184, 137, 42, 0.22);
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="mark" aria-hidden="true">✝</div>
        <h1>You're offline</h1>
        <p>Please check your connection. Previously visited pages may still be available when you return.</p>
        <a href="/">Try again</a>
    </div>
</body>
</html>
