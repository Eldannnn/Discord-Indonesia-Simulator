<?php

declare(strict_types=1);

/**
 * Active Laws & Regulations Component (`Views/components/active-laws-section.php`).
 * Renders all active Undang-Undang (UU), Peraturan Presiden (Perpres), PP, and Perppu from `active_laws`.
 *
 * @var array<int, array<string, string>> $activeLaws Active laws and regulations from MySQL
 */
?>
<section id="uu-aktif" class="py-16 sm:py-20 border-b border-slate-200">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">

        <div class="max-w-2xl mb-10">
            <p class="text-xs font-mono uppercase tracking-wider text-crimson-700 font-semibold">
                Produk Hukum Nasional
            </p>
            <h2 class="mt-2 font-serif text-3xl sm:text-4xl font-medium text-slate-900 tracking-tight">
                Undang-Undang &amp; Regulasi Aktif
            </h2>
            <p class="mt-2 text-sm sm:text-base text-slate-600 leading-relaxed">
                Daftar Undang-Undang dan Peraturan Presiden yang sedang berlaku serta mengikat seluruh warga negara dan penyelenggara pemerintahan.
            </p>
        </div>

        <div class="bg-white border border-slate-200 rounded-xl divide-y divide-slate-200 overflow-hidden">
            <?php if (empty($activeLaws)): ?>
                <div class="p-8 text-center text-sm text-slate-500">
                    Belum ada undang-undang aktif yang tercatat.
                </div>
            <?php else: ?>
                <?php foreach ($activeLaws as $law): ?>
                    <?php
                    $lawNumber = htmlspecialchars((string) ($law['law_number'] ?? ''), ENT_QUOTES, 'UTF-8');
                    $lawType = htmlspecialchars((string) ($law['law_type'] ?? ''), ENT_QUOTES, 'UTF-8');
                    $title = htmlspecialchars((string) ($law['title'] ?? ''), ENT_QUOTES, 'UTF-8');
                    $category = htmlspecialchars((string) ($law['category'] ?? ''), ENT_QUOTES, 'UTF-8');
                    $summary = htmlspecialchars((string) ($law['summary'] ?? ''), ENT_QUOTES, 'UTF-8');
                    $enactedBy = htmlspecialchars((string) ($law['enacted_by'] ?? ''), ENT_QUOTES, 'UTF-8');
                    $enactedDate = htmlspecialchars((string) ($law['enacted_date_label'] ?? ''), ENT_QUOTES, 'UTF-8');
                    $statusLabel = htmlspecialchars((string) ($law['status_label'] ?? ''), ENT_QUOTES, 'UTF-8');
                    ?>
                    <article class="p-6 flex flex-col lg:flex-row lg:items-start justify-between gap-4 hover:bg-slate-50/70 transition-colors">
                        <div class="space-y-2 max-w-3xl">
                            <div class="flex flex-wrap items-center gap-2 text-xs font-mono">
                                <span class="px-2 py-0.5 rounded bg-slate-900 text-white font-semibold">
                                    <?= $lawType ?>
                                </span>
                                <span class="font-bold text-crimson-700"><?= $lawNumber ?></span>
                                <span class="text-slate-300">&bull;</span>
                                <span class="text-slate-600"><?= $category ?></span>
                            </div>

                            <h3 class="text-base sm:text-lg font-bold text-slate-900 leading-snug">
                                <?= $title ?>
                            </h3>

                            <p class="text-sm text-slate-600 leading-relaxed">
                                <?= $summary ?>
                            </p>

                            <p class="text-xs text-slate-500 pt-1">
                                Disahkan oleh: <strong class="font-medium text-slate-700"><?= $enactedBy ?></strong>
                            </p>
                        </div>

                        <div class="flex lg:flex-col items-center lg:items-end justify-between gap-1.5 shrink-0">
                            <span class="px-2.5 py-0.5 rounded text-xs font-mono font-medium bg-emerald-50 text-emerald-800 border border-emerald-200">
                                <?= $statusLabel ?>
                            </span>
                            <span class="text-xs font-mono text-slate-400">Mulai Berlaku: <?= $enactedDate ?></span>
                        </div>
                    </article>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

    </div>
</section>

