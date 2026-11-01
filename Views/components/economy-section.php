<?php

declare(strict_types=1);

/**
 * National Macroeconomic Indicators Component (`Views/components/economy-section.php`).
 * Renders live macroeconomic indicators from `national_economy_metrics` in MySQL.
 *
 * @var array<int, array<string, mixed>> $economyMetrics Macroeconomic indicators from MySQL
 */
?>
<section id="indikator-ekonomi" class="py-16 sm:py-20 border-b border-slate-200">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">

        <div class="max-w-2xl mb-10">
            <p class="text-xs font-mono uppercase tracking-wider text-crimson-700 font-semibold">
                Perekonomian &amp; Keuangan Negara
            </p>
            <h2 class="mt-2 font-serif text-3xl sm:text-4xl font-medium text-slate-900 tracking-tight">
                Indikator Ekonomi Nasional
            </h2>
            <p class="mt-2 text-sm sm:text-base text-slate-600 leading-relaxed">
                Laporan kondisi makroekonomi, postur anggaran pendapatan negara, nilai tukar, serta kebijakan moneter Discord Indonesia Simulator.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach (($economyMetrics ?? []) as $metric): ?>
                <?php
                $label = htmlspecialchars((string) ($metric['metric_label'] ?? ''), ENT_QUOTES, 'UTF-8');
                $value = htmlspecialchars((string) ($metric['metric_value'] ?? ''), ENT_QUOTES, 'UTF-8');
                $unit = htmlspecialchars((string) ($metric['metric_unit'] ?? ''), ENT_QUOTES, 'UTF-8');
                $direction = (string) ($metric['trend_direction'] ?? 'STABLE');
                $change = htmlspecialchars((string) ($metric['trend_change'] ?? ''), ENT_QUOTES, 'UTF-8');
                $period = htmlspecialchars((string) ($metric['period_label'] ?? ''), ENT_QUOTES, 'UTF-8');
                $desc = htmlspecialchars((string) ($metric['description'] ?? ''), ENT_QUOTES, 'UTF-8');

                $badgeClass = 'bg-slate-100 text-slate-700 border-slate-200';
                if ($direction === 'UP') {
                    $badgeClass = 'bg-emerald-50 text-emerald-700 border-emerald-200';
                } elseif ($direction === 'DOWN') {
                    $badgeClass = 'bg-amber-50 text-amber-800 border-amber-200';
                }
                ?>
                <article class="bg-white border border-slate-200 rounded-xl p-6 flex flex-col justify-between hover:border-slate-300 transition-colors">
                    <div>
                        <div class="flex items-center justify-between gap-2 text-xs font-mono text-slate-500">
                            <span><?= $period ?></span>
                            <span class="px-2 py-0.5 rounded border font-medium <?= $badgeClass ?>">
                                <?= $change ?>
                            </span>
                        </div>

                        <h3 class="mt-3 text-sm font-semibold text-slate-600">
                            <?= $label ?>
                        </h3>

                        <div class="mt-1.5 flex items-baseline gap-1.5">
                            <span class="text-3xl font-bold font-mono text-slate-900 tabular-nums"><?= $value ?></span>
                            <?php if ($unit !== ''): ?>
                                <span class="text-sm font-mono font-semibold text-slate-500"><?= $unit ?></span>
                            <?php endif; ?>
                        </div>

                        <p class="mt-3 text-xs sm:text-sm text-slate-600 leading-relaxed">
                            <?= $desc ?>
                        </p>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>

    </div>
</section>

