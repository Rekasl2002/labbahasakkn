<?php
$code = http_response_code();
if (! is_int($code) || $code < 100) {
    $code = 500;
}
$title = lang('Errors.whoops');
$displayMessage = lang('Errors.weHitASnag');
$meta = 'Time: ' . date('Y-m-d H:i:s');
$copyText = "Status: {$code}\nTitle: {$title}\nMessage: {$displayMessage}\n{$meta}";
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="robots" content="noindex">
    <title><?= esc($title) ?></title>
    <style>
        :root {
            --bg-1: #f6f0ff;
            --bg-2: #fef2e4;
            --ink: #12131a;
            --muted: #555866;
            --card: rgba(255, 255, 255, 0.9);
            --border: rgba(18, 19, 26, 0.12);
            --accent: #e15b2f;
            --accent-2: #2f6ee5;
            --shadow: 0 20px 60px rgba(17, 17, 24, 0.12);
        }
        * {
            box-sizing: border-box;
        }
        body {
            margin: 0;
            min-height: 100vh;
            font-family: "IBM Plex Sans", "Ubuntu", "Trebuchet MS", sans-serif;
            color: var(--ink);
            background:
                radial-gradient(1200px 600px at 10% 0%, var(--bg-1), transparent),
                radial-gradient(1000px 600px at 100% 10%, var(--bg-2), transparent),
                #f7f6fb;
        }
        .page {
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 3rem 1.5rem;
            position: relative;
            overflow: hidden;
        }
        .page::before {
            content: "";
            position: absolute;
            inset: -20%;
            background: repeating-linear-gradient(
                45deg,
                rgba(19, 19, 27, 0.03),
                rgba(19, 19, 27, 0.03) 8px,
                transparent 8px,
                transparent 16px
            );
            opacity: 0.25;
            pointer-events: none;
        }
        .card {
            width: min(860px, 100%);
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 28px;
            padding: clamp(1.75rem, 3vw, 3rem);
            box-shadow: var(--shadow);
            position: relative;
            backdrop-filter: blur(12px);
            animation: rise 0.6s ease-out;
        }
        .badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.2em;
            color: #fff;
            background: linear-gradient(120deg, var(--accent), #ff9b57);
            padding: 0.35rem 0.85rem;
            border-radius: 999px;
            font-weight: 600;
        }
        .status {
            margin-top: 1.5rem;
            display: flex;
            align-items: baseline;
            gap: 1rem;
            flex-wrap: wrap;
        }
        .status-code {
            font-size: clamp(3rem, 10vw, 7rem);
            font-weight: 700;
            letter-spacing: 0.08em;
        }
        .status-title {
            font-size: clamp(1.3rem, 3vw, 2.4rem);
            color: var(--muted);
        }
        .message {
            margin-top: 1rem;
            font-size: 1.05rem;
            line-height: 1.6;
        }
        .meta {
            margin-top: 1rem;
            font-size: 0.9rem;
            color: var(--muted);
        }
        .detail {
            margin-top: 1.8rem;
            background: #101318;
            color: #f1f1f6;
            border-radius: 18px;
            padding: 1.2rem;
            font-family: "JetBrains Mono", "Fira Code", "SFMono-Regular", Consolas, monospace;
        }
        .detail-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            font-size: 0.9rem;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: #c9c9d7;
        }
        .detail pre {
            margin: 0.8rem 0 0;
            white-space: pre-wrap;
            word-break: break-word;
        }
        .copy-btn {
            appearance: none;
            border: 1px solid rgba(255, 255, 255, 0.2);
            background: rgba(255, 255, 255, 0.08);
            color: #fff;
            padding: 0.35rem 0.75rem;
            border-radius: 999px;
            font-size: 0.75rem;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            cursor: pointer;
        }
        .copy-btn:hover {
            background: rgba(255, 255, 255, 0.18);
        }
        @media (max-width: 640px) {
            .card {
                border-radius: 20px;
            }
            .detail-head {
                flex-direction: column;
                align-items: flex-start;
            }
        }
        @keyframes rise {
            from {
                transform: translateY(16px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }
        @media (prefers-reduced-motion: reduce) {
            .card {
                animation: none;
            }
        }
    </style>
</head>
<body>
<main class="page">
    <section class="card">
        <span class="badge">Unexpected Error</span>
        <div class="status">
            <span class="status-code"><?= esc($code) ?></span>
            <span class="status-title"><?= esc($title) ?></span>
        </div>
        <p class="message"><?= esc($displayMessage) ?></p>
        <div class="meta"><?= esc($meta) ?></div>
        <div class="detail">
            <div class="detail-head">
                <span>Detail yang bisa disalin</span>
                <button type="button" class="copy-btn" data-copy-target="error-details" data-copy-label="Salin" data-copy-done="Tersalin">
                    Salin
                </button>
            </div>
            <pre id="error-details"><?= esc($copyText) ?></pre>
        </div>
    </section>
</main>
<script>
    (function () {
        function copyText(text, button) {
            var original = button.getAttribute('data-copy-label') || button.textContent;
            var done = button.getAttribute('data-copy-done') || 'Copied';
            function setState(label) {
                button.textContent = label;
                setTimeout(function () {
                    button.textContent = original;
                }, 1600);
            }
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(text).then(function () {
                    setState(done);
                }, function () {
                    fallbackCopy(text, setState, done);
                });
            } else {
                fallbackCopy(text, setState, done);
            }
        }
        function fallbackCopy(text, setState, done) {
            var textarea = document.createElement('textarea');
            textarea.value = text;
            textarea.setAttribute('readonly', 'readonly');
            textarea.style.position = 'absolute';
            textarea.style.left = '-9999px';
            document.body.appendChild(textarea);
            textarea.select();
            try {
                document.execCommand('copy');
                setState(done);
            } catch (err) {
                setState('Manual');
            }
            document.body.removeChild(textarea);
        }
        var button = document.querySelector('[data-copy-target="error-details"]');
        if (!button) {
            return;
        }
        button.addEventListener('click', function () {
            var target = document.getElementById(this.getAttribute('data-copy-target'));
            if (!target) {
                return;
            }
            copyText(target.textContent || target.innerText || '', this);
        });
    })();
</script>
</body>
</html>
