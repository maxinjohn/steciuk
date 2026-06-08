<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>Offline — STECI UK Parish</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Plus Jakarta Sans', system-ui, sans-serif;
            background: linear-gradient(135deg, #4f46e5, #7c3aed, #2563eb);
            color: #fafafa;
            padding: max(2rem, env(safe-area-inset-top)) max(2rem, env(safe-area-inset-right)) max(2rem, env(safe-area-inset-bottom)) max(2rem, env(safe-area-inset-left));
            text-align: center;
        }
        .card {
            max-width: 24rem;
            width: 100%;
            padding: 2.5rem 1.75rem;
            background: rgba(255,255,255,0.12);
            backdrop-filter: blur(20px);
            border-radius: 1.5rem;
            border: 1px solid rgba(255,255,255,0.2);
        }
        h1 { font-size: 1.75rem; font-weight: 800; letter-spacing: -0.03em; margin-bottom: 0.75rem; }
        p { opacity: 0.9; line-height: 1.6; margin-bottom: 1.5rem; font-weight: 500; }
        a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 48px;
            padding: 0.75rem 1.75rem;
            background: #ffffff;
            color: #4f46e5;
            text-decoration: none;
            border-radius: 14px;
            font-weight: 700;
        }
    </style>
</head>
<body>
    <div class="card">
        <h1>You're offline</h1>
        <p>Please check your connection. Previously visited pages may still be available.</p>
        <a href="/">Try again</a>
    </div>
</body>
</html>
