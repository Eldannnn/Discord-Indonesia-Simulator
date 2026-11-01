<?php

declare(strict_types=1);

/**
 * State Gazette (Lembaran Berita Negara) Feed Component (`Views/components/gazette-timeline.php`).
 * Displays official Presidential and Vice-Presidential decrees directly from the MySQL database.
 *
 * @var array<int, array<string, string>> $gazetteFeed Recent state actions from `executive_decrees_log`
 */
?>
<section id="lembaran-negara" class="py-16 sm:py-20 border-b border-slate-200">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">

        <div class="flex flex-col sm:flex-row sm:items-end justify-between mb-10 gap-4">
            <div>
                <p class="text-xs font-mono uppercase tracking-wider text-crimson-700 font-semibold">
                    Arsip Keputusan Resmi
                </p>
                <h2 class="mt-2 font-serif text-3xl sm:text-4xl font-medium text-slate-900 tracking-tight">
                    Lembaran Berita Negara Terbaru
                </h2>
            </div>
        </div>

        <div class="bg-white border border-slate-200 rounded-xl divide-y divide-slate-200 overflow-hidden">
            <?php if (empty($gazetteFeed)): ?>
                <div class="p-8 text-center text-sm text-slate-500">
                    Belum ada keputusan atau peraturan negara yang diterbitkan.
                </div>
            <?php else: ?>
                <?php foreach ($gazetteFeed as $item): ?>
                    <?php
                    $code = htmlspecialchars((string) ($item['code'] ?? ''), ENT_QUOTES, 'UTF-8');
                    $docType = htmlspecialchars((string) ($item['document_type'] ?? ''), ENT_QUOTES, 'UTF-8');
                    $branch = htmlspecialchars((string) ($item['branch'] ?? ''), ENT_QUOTES, 'UTF-8');
                    $actor = htmlspecialchars((string) ($item['actor'] ?? ''), ENT_QUOTES, 'UTF-8');
                    $title = htmlspecialchars((string) ($item['title'] ?? ''), ENT_QUOTES, 'UTF-8');
                    $summary = htmlspecialchars((string) ($item['summary_content'] ?? ''), ENT_QUOTES, 'UTF-8');
                    $status = htmlspecialchars((string) ($item['status'] ?? ''), ENT_QUOTES, 'UTF-8');
                    $timestamp = htmlspecialchars((string) ($item['timestamp'] ?? ''), ENT_QUOTES, 'UTF-8');
                    ?>
                    <div class="p-5 sm:p-6 flex flex-col sm:flex-row sm:items-start justify-between gap-4 hover:bg-slate-50/70 transition-colors">
                        <div class="space-y-1.5 max-w-3xl">
                            <div class="flex flex-wrap items-center gap-2 text-xs font-mono text-slate-500">
                                <span class="px-2 py-0.5 rounded bg-crimson-50 text-crimson-700 border border-crimson-100 font-semibold"><?= $docType ?></span>
                                <span class="font-semibold text-slate-800"><?= $code ?></span>
                                <span>&bull;</span>
                                <span><?= $branch ?> (<?= $actor ?>)</span>
                            </div>
                            <h3 class="text-base font-bold text-slate-900 leading-snug">
                                <?= $title ?>
                            </h3>
                            <?php if ($summary !== ''): ?>
                                <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">
                                    <?= $summary ?>
                                </p>
                            <?php endif; ?>
                        </div>

                        <div class="flex sm:flex-col items-center sm:items-end justify-between gap-1.5 shrink-0">
                            <span class="px-2.5 py-0.5 rounded text-xs font-mono font-medium bg-emerald-50 text-emerald-800 border border-emerald-200">
                                <?= $status ?>
                            </span>
                            <span class="text-xs font-mono text-slate-400"><?= $timestamp ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

    </div>
</section>
