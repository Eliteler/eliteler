<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>{{ config('app.name') }} — {{ __('System Maintenance...') }}</title>
    <link
        href="https://fonts.googleapis.com/css2?family=Figtree:wght@300;400;500;600&family=JetBrains+Mono:wght@400;500&display=swap"
        rel="stylesheet" />
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --accent: {{ $template_config->template_color }};
            --bg: #ffffff;
            --panel: #ffffff;
            --panel-2: #f7f7f5;
            --line: #e7e6e1;
            --text: #16171a;
            --text-dim: #6b6d72;
            --text-faint: #9a9ca1;
            --accent-soft: color-mix(in srgb, var(--accent) 14%, transparent);
            --accent-dim: color-mix(in srgb, var(--accent) 55%, #6b6d72);
            --green: #1f9d63;
        }

        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--bg);
            color: var(--text);
            font-family: "Figtree", sans-serif;
            padding: 1.5rem;
        }

        .console {
            width: 100%;
            max-width: 460px;
            background: var(--panel);
            border: 1px solid var(--line);
            border-top: 2px solid var(--accent);
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 1px 2px rgba(16, 17, 20, 0.04), 0 8px 24px rgba(16, 17, 20, 0.05);
            animation: enter 0.7s cubic-bezier(0.16, 1, 0.3, 1) forwards;
            opacity: 0;
        }

        @keyframes enter {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Title bar */
        .titlebar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0.85rem 1.1rem;
            background: var(--panel-2);
            border-bottom: 1px solid var(--line);
        }

        .titlebar-left {
            display: flex;
            align-items: center;
            gap: 8px;
            line-height: 1;
        }

        .dot {
            flex: none;
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: var(--text-faint);
        }

        .titlebar-label {
            font-family: "JetBrains Mono", monospace;
            font-size: 0.72rem;
            color: var(--text-dim);
            letter-spacing: 0.02em;
            line-height: 1;
        }

        .titlebar-label b {
            font-weight: 500;
            color: var(--text);
        }

        .status-chip {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-family: "JetBrains Mono", monospace;
            font-size: 0.68rem;
            letter-spacing: 0.04em;
            color: var(--accent-dim);
            text-transform: uppercase;
            line-height: 1;
        }

        .status-chip .pulse {
            flex: none;
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: var(--accent);
            animation: pulse 1.6s ease-in-out infinite;
        }

        @keyframes pulse {

            0%,
            100% {
                opacity: 1;
                box-shadow: 0 0 0 0 var(--accent-soft);
            }

            50% {
                opacity: 0.5;
                box-shadow: 0 0 0 4px transparent;
            }
        }

        /* Body */
        .body-content {
            padding: 1.6rem 1.4rem 1.5rem;
        }

        h1 {
            font-size: 1.35rem;
            font-weight: 500;
            letter-spacing: -0.01em;
            margin-bottom: 0.5rem;
        }

        .desc {
            font-size: 0.88rem;
            font-weight: 300;
            color: var(--text-dim);
            line-height: 1.55;
            margin-bottom: 1.5rem;
        }

        /* Progress */
        .progress-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-family: "JetBrains Mono", monospace;
            font-size: 0.68rem;
            color: var(--text-faint);
            margin-bottom: 6px;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        .progress-row span:last-child {
            color: var(--text-dim);
        }

        .progress-track {
            width: 100%;
            height: 4px;
            background: var(--line);
            border-radius: 2px;
            overflow: hidden;
            margin-bottom: 1.4rem;
        }

        .progress-fill {
            height: 100%;
            width: 0%;
            background: var(--accent);
            border-radius: 2px;
            animation: fill 14s cubic-bezier(0.4, 0, 0.2, 1) infinite;
        }

        @keyframes fill {
            0% {
                width: 4%;
            }

            70% {
                width: 92%;
            }

            85% {
                width: 92%;
            }

            100% {
                width: 4%;
            }
        }

        /* Log */
        .log {
            background: #0b0c0e;
            border: 1px solid var(--line);
            border-radius: 6px;
            padding: 0.85rem 0.95rem;
            font-family: "JetBrains Mono", monospace;
            font-size: 0.73rem;
            line-height: 1.9;
        }

        .log-line {
            display: flex;
            align-items: center;
            gap: 0;
            opacity: 0;
            animation: logIn 0.4s ease forwards;
            white-space: nowrap;
        }

        .log-line .tick {
            flex: none;
            width: 16px;
            color: var(--green);
        }

        .log-line .txt {
            color: #9a9ca1;
        }

        .log-line:nth-child(1) {
            animation-delay: 0.3s;
        }

        .log-line:nth-child(2) {
            animation-delay: 1.1s;
        }

        .log-line:nth-child(3) {
            animation-delay: 1.9s;
        }

        .log-line:nth-child(4) {
            animation-delay: 2.7s;
        }

        .log-line.active .txt {
            color: #edece7;
        }

        .log-line.active .tick {
            color: var(--accent);
        }

        @keyframes logIn {
            from {
                opacity: 0;
                transform: translateY(4px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .cursor {
            display: inline-block;
            width: 6px;
            height: 12px;
            background: var(--accent);
            margin-left: 4px;
            animation: blink 1s step-end infinite;
            flex: none;
        }

        @keyframes blink {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0;
            }
        }

        @media (max-width: 480px) {
            .log-line {
                white-space: normal;
            }
        }
    </style>
</head>

<body>
    <div class="console">
        <div class="titlebar">
            <div class="titlebar-left">
                <span class="dot"></span>
                <span class="titlebar-label"><b>{{ config('app.name') }}</b> · {{ __('system.status') }}</span>
            </div>
            <span class="status-chip"><span class="pulse"></span>{{ __('maintenance') }}</span>
        </div>

        <div class="body-content">
            <h1>{{ __('Under maintenance') }}</h1>
            <p class="desc">
                {{ __('We are currently performing scheduled system updates to improve your experience. Please check back shortly.') }}
            </p>

            <div class="progress-row">
                <span>{{ __('progress') }}</span>
                <span>{{ __('running...') }}</span>
            </div>
            <div class="progress-track">
                <div class="progress-fill"></div>
            </div>

            <div class="log">
                <div class="log-line"><span class="tick">✓</span><span
                        class="txt">{{ __('backing up database') }}</span></div>
                <div class="log-line"><span class="tick">✓</span><span
                        class="txt">{{ __('applying migrations') }}</span></div>
                <div class="log-line"><span class="tick">✓</span><span
                        class="txt">{{ __('rebuilding cache') }}</span></div>
                <div class="log-line active"><span class="tick">›</span><span
                        class="txt">{{ __('finalizing deployment') }}</span><span class="cursor"></span></div>
            </div>
        </div>
    </div>
</body>

</html>
