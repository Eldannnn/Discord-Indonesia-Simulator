<?php

declare(strict_types=1);

/**
 * Master HTML Layout Wrapper for Republik Nusantara Government Roleplay.
 * Built with Swiss Minimalist & Editorial Grid aesthetics (Newsreader + Plus Jakarta Sans + JetBrains Mono).
 *
 * @var string $appName Application title injected by controller
 * @var string $simulationEra Current roleplay season identifier
 * @var string $pageContent Rendered inner view HTML content
 * @var array<int, array<string, mixed>> $roles Enriched RBAC roles for client-side interactive hydration
 */
$safeAppName = htmlspecialchars((string) ($appName ?? 'Discord Indonesia Simulator'), ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Discord Indonesia Simulator — Simulasi Ketatanegaraan dan Pemerintahan Indonesia.">
    <link rel="icon" type="image/png" href="Assets/image/Logo_DIS.png">
    <title><?= $safeAppName ?></title>

    <!-- Google Fonts: Newsreader (Editorial Serif), Plus Jakarta Sans (Clean UI), JetBrains Mono (Code/Metrics), Alex Brush & Great Vibes (Executive Signatures) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Alex+Brush&family=Great+Vibes&family=JetBrains+Mono:wght@400;500;600&family=Newsreader:ital,opsz,wght@0,6..72,400;0,6..72,500;0,6..72,600;1,6..72,400&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'system-ui', 'sans-serif'],
                        serif: ['"Newsreader"', 'Georgia', 'serif'],
                        mono: ['"JetBrains Mono"', 'monospace'],
                        signature: ['"Alex Brush"', '"Great Vibes"', 'cursive'],
                    },
                    colors: {
                        paper: '#FAF9F5',
                        ink: '#0F172A',
                        crimson: {
                            50: '#FEF2F2',
                            100: '#FEE2E2',
                            600: '#DC2626',
                            700: '#B91C1C',
                            800: '#991B1B',
                        }
                    }
                }
            }
        };
    </script>

    <link rel="stylesheet" href="public/assets/css/landing.css">
</head>
<body class="bg-paper text-ink font-sans antialiased selection:bg-crimson-700 selection:text-white">

    <!-- Skip Link for Accessibility -->
    <a href="#main-content" class="sr-only focus:not-sr-only focus:fixed focus:top-4 focus:left-4 focus:z-50 focus:px-4 focus:py-2 focus:bg-slate-900 focus:text-white focus:rounded-lg focus:text-sm">
        Langsung ke konten utama
    </a>

    <!-- Custom Toast Notification Container -->
    <div id="toastContainer" class="fixed bottom-6 right-6 z-50 flex flex-col gap-2.5 max-w-sm w-full pointer-events-none" aria-live="polite"></div>

    <!-- Main Page Wrapper -->
    <div class="min-h-screen flex flex-col">
        <?= $pageContent ?>
    </div>

    <!-- Main Interactive Application Script -->
    <script src="public/assets/js/landing.js" defer></script>
</body>
</html>
