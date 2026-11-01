<?php

declare(strict_types=1);

/**
 * Minimalist Institutional Footer Component.
 * Free of any backend/technical mentions.
 *
 * @var string $appName Application name
 * @var string $simulationEra Current roleplay season
 */
?>
<footer class="mt-auto bg-white border-t border-slate-200 py-10">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-6 text-xs text-slate-500">
        <div class="space-y-1">
            <p class="font-semibold text-slate-900 flex items-center gap-2">
                <img src="Assets/image/Logo_DIS.png" alt="Logo Discord Indonesia Simulator" class="w-5 h-5 object-contain">
                <span><?= htmlspecialchars((string) ($appName ?? 'Discord Indonesia Simulator'), ENT_QUOTES, 'UTF-8') ?></span>
            </p>
            <p>
                Simulasi komunitas ketatanegaraan dan pemerintahan Indonesia. Seluruh nama tokoh, partai, dan keputusan di dalam simulasi bersifat fiksi.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-5 font-mono text-slate-500">
            <span><?= htmlspecialchars((string) ($simulationEra ?? ''), ENT_QUOTES, 'UTF-8') ?></span>
            <span>&bull;</span>
            <span>Portal Resmi Discord Indonesia Simulator</span>
        </div>
    </div>
</footer>
