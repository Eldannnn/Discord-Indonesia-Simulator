<?php

declare(strict_types=1);

/**
 * Dynamic State Officials & Constitutional Roles Grid (`Views/components/branches-grid.php`).
 * Renders roles and active seat counts dynamically from the MySQL database.
 *
 * @var array<int, array<string, mixed>> $branchesSummary Dynamic role summaries from MySQL
 */
?>
<section id="pilar-negara" class="py-16 sm:py-20 border-b border-slate-200">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">

        <div class="max-w-2xl mb-10">
            <p class="text-xs font-mono uppercase tracking-wider text-crimson-700 font-semibold">
                Jabatan Negara Terdaftar
            </p>
            <h2 class="mt-2 font-serif text-3xl sm:text-4xl font-medium text-slate-900 tracking-tight">
                Kedudukan Pejabat Negara Aktif
            </h2>
            <p class="mt-2.5 text-sm sm:text-base text-slate-600 leading-relaxed">
                Daftar jabatan konstitusional beserta kuota kursi yang tercatat secara langsung dalam sistem kenegaraan Discord Indonesia Simulator.
            </p>
        </div>

        <!-- Dynamic Grid from MySQL rbac_roles -->
        <div class="grid grid-cols-1 md:grid-cols-2 bg-white border border-slate-200 rounded-xl overflow-hidden divide-y md:divide-y-0 md:divide-x divide-slate-200">
            <?php foreach ($branchesSummary as $branch): ?>
                <?php
                $title = htmlspecialchars((string) ($branch['title'] ?? ''), ENT_QUOTES, 'UTF-8');
                $subtitle = htmlspecialchars((string) ($branch['subtitle'] ?? ''), ENT_QUOTES, 'UTF-8');
                $basis = htmlspecialchars((string) ($branch['constitutional_basis'] ?? ''), ENT_QUOTES, 'UTF-8');
                $desc = htmlspecialchars((string) ($branch['description'] ?? ''), ENT_QUOTES, 'UTF-8');
                $activeCount = (int) ($branch['active_count'] ?? 0);
                $quotaCount = (int) ($branch['quota_count'] ?? 1);
                ?>
                <article class="p-6 flex flex-col justify-between hover:bg-slate-50/60 transition-colors">
                    <div>
                        <div class="flex items-center justify-between text-xs font-mono text-slate-500 mb-4">
                            <span><?= $basis ?></span>
                            <span class="tabular-nums text-emerald-700 bg-emerald-50 border border-emerald-200 px-2 py-0.5 rounded font-medium">
                                <?= $activeCount ?> / <?= $quotaCount ?> Kursi Terisi
                            </span>
                        </div>

                        <h3 class="text-lg font-bold text-slate-900">
                            <?= $title ?>
                        </h3>
                        <p class="text-xs text-crimson-700 font-mono mt-0.5">
                            <?= $subtitle ?>
                        </p>

                        <p class="mt-3 text-sm text-slate-600 leading-relaxed">
                            <?= $desc ?>
                        </p>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>

    </div>
</section>
