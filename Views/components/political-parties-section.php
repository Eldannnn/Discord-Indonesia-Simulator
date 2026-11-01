<?php

declare(strict_types=1);

/**
 * Political Parties, Ideologies & Descriptions Component (`Views/components/political-parties-section.php`).
 * Renders comprehensive profiles of all political parties in Republik Nusantara from `political_parties` in MySQL.
 *
 * @var array<int, array<string, mixed>> $politicalParties Political party records from MySQL
 */
?>
<section id="partai-politik" class="py-16 sm:py-20 border-b border-slate-200">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">

        <div class="max-w-2xl mb-10">
            <p class="text-xs font-mono uppercase tracking-wider text-crimson-700 font-semibold">
                Direktori Kepartaian Nasional
            </p>
            <h2 class="mt-2 font-serif text-3xl sm:text-4xl font-medium text-slate-900 tracking-tight">
                Partai Politik, Ideologi &amp; Arah Perjuangan
            </h2>
            <p class="mt-2 text-sm sm:text-base text-slate-600 leading-relaxed">
                Profil lengkap partai politik peserta pemilu beserta haluan ideologi, posisi koalisi pemerintahan, dan fokus kebijakan masing-masing partai.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <?php foreach (($politicalParties ?? []) as $party): ?>
                <?php
                $acronym = htmlspecialchars((string) ($party['party_acronym'] ?? ''), ENT_QUOTES, 'UTF-8');
                $name = htmlspecialchars((string) ($party['party_name'] ?? ''), ENT_QUOTES, 'UTF-8');
                $ideology = htmlspecialchars((string) ($party['ideology'] ?? ''), ENT_QUOTES, 'UTF-8');
                $chairperson = htmlspecialchars((string) ($party['chairperson_name'] ?? ''), ENT_QUOTES, 'UTF-8');
                $desc = htmlspecialchars((string) ($party['description'] ?? ''), ENT_QUOTES, 'UTF-8');
                $seats = (int) ($party['dpr_seats'] ?? 0);
                $percent = number_format((float) ($party['seat_percentage'] ?? 0), 1, ',', '.');
                $isCoalition = ($party['coalition_status'] ?? '') === 'KOALISI_PEMERINTAH';
                $coalitionLabel = htmlspecialchars((string) ($party['coalition_label'] ?? ''), ENT_QUOTES, 'UTF-8');
                ?>
                <article class="bg-white border border-slate-200 rounded-xl p-6 flex flex-col justify-between hover:border-slate-300 transition-colors">
                    <div class="space-y-4">
                        <!-- Top Row: Acronym Badge & Coalition Status -->
                        <div class="flex items-center justify-between gap-2">
                            <span class="px-2.5 py-1 rounded-md font-mono text-xs font-bold bg-slate-900 text-white">
                                <?= $acronym ?>
                            </span>
                            <span class="px-2.5 py-0.5 rounded text-xs font-mono font-medium border <?= $isCoalition ? 'bg-crimson-50 text-crimson-700 border-crimson-100' : 'bg-slate-100 text-slate-700 border-slate-200' ?>">
                                <?= $coalitionLabel ?>
                            </span>
                        </div>

                        <!-- Party Name & Ideology -->
                        <div>
                            <h3 class="text-lg font-bold text-slate-900 leading-snug">
                                <?= $name ?>
                            </h3>
                            <div class="mt-1.5 inline-flex items-center gap-1.5 text-xs font-mono text-crimson-700 bg-crimson-50/70 px-2.5 py-1 rounded border border-crimson-100">
                                <span class="font-semibold">Ideologi:</span>
                                <span><?= $ideology ?></span>
                            </div>
                        </div>

                        <!-- Party Description -->
                        <p class="text-sm text-slate-600 leading-relaxed">
                            <?= $desc ?>
                        </p>
                    </div>

                    <!-- Footer Metadata: Chairperson & DPR Seats -->
                    <div class="mt-6 pt-4 border-t border-slate-200 flex flex-wrap items-center justify-between gap-2 text-xs text-slate-500">
                        <div>
                            <span>Ketua Umum:</span>
                            <strong class="text-slate-800 font-semibold ml-1"><?= $chairperson ?></strong>
                        </div>
                        <div class="font-mono font-semibold text-slate-900">
                            <?= $seats ?> Kursi DPR (<?= $percent ?>%)
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>

    </div>
</section>

