@props([
    'code' => '500',
    'title' => 'Something went wrong',
    'message' => 'We could not complete your request.',
    'verse' => null,
    'verseRef' => null,
    'primaryLabel' => 'Back to home',
    'primaryUrl' => null,
    'secondaryLabel' => null,
    'secondaryUrl' => null,
    'showAdminLink' => true,
])

@php
    use App\Support\AdminPanelConfig;

    $primaryUrl ??= url('/');
    $isAdmin = request()->is(AdminPanelConfig::pathPattern());
@endphp
<!DOCTYPE html>
<html lang="en-GB">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="robots" content="noindex, nofollow">
    <meta name="theme-color" content="#d4cabb">
    <title>{{ $code }} · {{ $title }}</title>
    <style>
        :root {
            --error-bg: #f5f0e8;
            --error-bg-2: #ebe4d8;
            --error-surface: rgba(255, 255, 255, 0.92);
            --error-ink: #1c1917;
            --error-muted: #6b5f52;
            --error-accent: #b8892a;
            --error-brand: #1a2332;
            --error-border: rgba(26, 35, 50, 0.1);
            --error-glow: rgba(251, 191, 36, 0.22);
        }
        @media (prefers-color-scheme: dark) {
            :root {
                --error-bg: #0f1115;
                --error-bg-2: #17141f;
                --error-surface: rgba(24, 24, 27, 0.92);
                --error-ink: #fafafa;
                --error-muted: #a8a29e;
                --error-accent: #dbb45a;
                --error-brand: #f5f0e8;
                --error-border: rgba(255, 255, 255, 0.08);
                --error-glow: rgba(251, 191, 36, 0.14);
            }
        }
        * { box-sizing: border-box; }
        html, body {
            margin: 0;
            min-height: 100%;
        }
        body {
            min-height: 100dvh;
            font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            color: var(--error-ink);
            background:
                radial-gradient(ellipse 80% 55% at 50% -10%, var(--error-glow), transparent 55%),
                linear-gradient(180deg, var(--error-bg), var(--error-bg-2));
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: max(1rem, env(safe-area-inset-top)) max(1rem, env(safe-area-inset-right)) max(1.5rem, env(safe-area-inset-bottom)) max(1rem, env(safe-area-inset-left));
        }
        .error-shell {
            width: min(100%, 28rem);
        }
        .error-card {
            position: relative;
            overflow: hidden;
            border-radius: 1.75rem;
            border: 1px solid var(--error-border);
            background: var(--error-surface);
            backdrop-filter: blur(16px);
            box-shadow:
                0 24px 60px rgba(26, 35, 50, 0.12),
                inset 0 1px 0 rgba(255, 255, 255, 0.35);
            padding: clamp(1.5rem, 4vw, 2rem);
            text-align: center;
        }
        .error-card::before {
            content: '';
            position: absolute;
            inset: 0 auto auto 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(90deg, transparent, var(--error-accent), transparent);
        }
        .error-mark {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 3.25rem;
            height: 3.25rem;
            margin-bottom: 1rem;
            border-radius: 1rem;
            background: linear-gradient(135deg, rgba(251, 191, 36, 0.22), rgba(184, 137, 42, 0.12));
            color: var(--error-accent);
            font-size: 1.25rem;
            font-weight: 700;
            box-shadow: inset 0 0 0 1px rgba(184, 137, 42, 0.18);
        }
        .error-code {
            display: inline-flex;
            margin-bottom: 0.75rem;
            padding: 0.35rem 0.8rem;
            border-radius: 9999px;
            font-size: 0.72rem;
            font-weight: 800;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            color: var(--error-accent);
            background: rgba(184, 137, 42, 0.12);
        }
        h1 {
            margin: 0 0 0.75rem;
            font-size: clamp(1.35rem, 4vw, 1.75rem);
            line-height: 1.15;
            letter-spacing: -0.03em;
        }
        .error-message {
            margin: 0;
            font-size: 0.98rem;
            line-height: 1.65;
            color: var(--error-muted);
        }
        .error-actions {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            margin-top: 1.5rem;
        }
        @media (min-width: 420px) {
            .error-actions--split {
                flex-direction: row;
                justify-content: center;
            }
            .error-actions--split .error-btn {
                flex: 1 1 0;
                min-width: 0;
            }
        }
        .error-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 2.85rem;
            padding: 0.75rem 1.15rem;
            border-radius: 9999px;
            font-size: 0.92rem;
            font-weight: 700;
            text-decoration: none;
            transition: transform 160ms ease, box-shadow 160ms ease;
        }
        .error-btn:active {
            transform: scale(0.98);
        }
        .error-btn-primary {
            color: #1c1917;
            background: linear-gradient(135deg, #dbb45a, #b8892a);
            box-shadow: 0 10px 24px rgba(184, 137, 42, 0.22);
        }
        .error-btn-secondary {
            color: var(--error-ink);
            background: rgba(184, 137, 42, 0.08);
            box-shadow: inset 0 0 0 1px var(--error-border);
        }
        .error-verse {
            margin-top: 1.25rem;
            padding-top: 1rem;
            border-top: 1px solid var(--error-border);
            font-size: 0.82rem;
            line-height: 1.55;
            color: var(--error-muted);
            font-style: italic;
        }
        .error-verse cite {
            display: block;
            margin-top: 0.35rem;
            font-style: normal;
            font-size: 0.72rem;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: var(--error-accent);
        }
        .error-meta {
            margin-top: 1rem;
            font-size: 0.78rem;
            color: var(--error-muted);
        }
    </style>
</head>
<body>
    <main class="error-shell">
        <article class="error-card">
            <div class="error-mark" aria-hidden="true">✝</div>
            <div class="error-code">{{ $code }}</div>
            <h1>{{ $title }}</h1>
            <p class="error-message">{{ $message }}</p>

            {{ $slot }}

            <div class="error-actions {{ ($secondaryLabel && $secondaryUrl) ? 'error-actions--split' : '' }}">
                <a href="{{ $primaryUrl }}" class="error-btn error-btn-primary">{{ $primaryLabel }}</a>
                @if ($secondaryLabel && $secondaryUrl)
                    <a href="{{ $secondaryUrl }}" class="error-btn error-btn-secondary">{{ $secondaryLabel }}</a>
                @endif
            </div>

            @if ($showAdminLink && ! $isAdmin)
                <p class="error-meta">
                    Parish team? <a href="{{ AdminPanelConfig::url('login') }}" style="color: var(--error-accent); font-weight: 700;">Sign in to admin</a>
                </p>
            @endif

            @if ($verse)
                <blockquote class="error-verse">
                    “{{ $verse }}”
                    @if ($verseRef)
                        <cite>{{ $verseRef }}</cite>
                    @endif
                </blockquote>
            @endif
        </article>
    </main>
</body>
</html>
