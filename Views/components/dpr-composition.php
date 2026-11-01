<?php

declare(strict_types=1);

/**
 * DPR RI Parliament Composition Component (`Views/components/dpr-composition.php`).
 * Renders parliament seat distribution, Coalition vs Opposition balance, and faction breakdown from MySQL.
 *
 * @var array<int, array<string, mixed>> $politicalParties Party & faction records from MySQL
 * @var array<string, mixed> $dprSummary Computed DPR seat totals
 */
$totalSeats = (int) ($dprSummary['total_seats'] ?? 580);
$coalitionSeats = (int) ($dprSummary['coalition_seats'] ?? 0);
$oppositionSeats = (int) ($dprSummary['opposition_seats'] ?? 0);
$coalitionPercent = (float) ($dprSummary['coalition_percent'] ?? 0);
$oppositionPercent = (float) ($dprSummary['opposition_percent'] ?? 0);
$coalitionCount = (int) ($dprSummary['coalition_parties_count'] ?? 0);
$oppositionCount = (int) ($dprSummary['opposition_parties_count'] ?? 0);
?>
<section id="komposisi-dpr" class="py-16 sm:py-20 border-b border-slate-200">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">

        <div class="max-w-2xl mb-10">
            <p class="text-xs font-mono uppercase tracking-wider text-crimson-700 font-semibold">
                Lembaga Legislatif Nasional
            </p>
            <h2 class="mt-2 font-serif text-3xl sm:text-4xl font-medium text-slate-900 tracking-tight">
                Komposisi Kursi DPR RI
            </h2>
            <p class="mt-2 text-sm sm:text-base text-slate-600 leading-relaxed">
                Distribusi kekuatan politik di Dewan Perwakilan Rakyat Discord Indonesia Simulator berdasarkan perolehan kursi fraksi partai politik.
            </p>
        </div>

        <!-- Summary Cards: Total, Koalisi Pemerintah, Oposisi -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white border border-slate-200 rounded-xl p-6">
                <span class="text-xs font-mono uppercase tracking-wider text-slate-500">Total Kursi Parlemen</span>
                <div class="mt-2 flex items-baseline justify-between">
                    <span class="text-3xl font-bold font-mono text-slate-900 tabular-nums"><?= $totalSeats ?> Kursi</span>
                    <span class="text-xs font-mono text-slate-500"><?= count($politicalParties ?? []) ?> Fraksi Partai</span>
                </div>
                <p class="mt-2 text-xs text-slate-500">Ambang batas parlemen nasional: 4,0% suara sah.</p>
            </div>

            <div class="bg-white border border-slate-200 rounded-xl p-6">
                <span class="text-xs font-mono uppercase tracking-wider text-crimson-700 font-semibold">Koalisi Pemerintah</span>
                <div class="mt-2 flex items-baseline justify-between">
                    <span class="text-3xl font-bold font-mono text-slate-900 tabular-nums"><?= $coalitionSeats ?> Kursi</span>
                    <span class="px-2 py-0.5 rounded bg-crimson-50 text-crimson-700 border border-crimson-100 text-xs font-mono font-semibold">
                        <?= number_format($coalitionPercent, 1, ',', '.') ?>%
                    </span>
                </div>
                <p class="mt-2 text-xs text-slate-500">Didukung oleh <?= $coalitionCount ?> partai politik pendukung pemerintah.</p>
            </div>

            <div class="bg-white border border-slate-200 rounded-xl p-6">
                <span class="text-xs font-mono uppercase tracking-wider text-slate-700 font-semibold">Oposisi Parlemen</span>
                <div class="mt-2 flex items-baseline justify-between">
                    <span class="text-3xl font-bold font-mono text-slate-900 tabular-nums"><?= $oppositionSeats ?> Kursi</span>
                    <span class="px-2 py-0.5 rounded bg-slate-100 text-slate-800 border border-slate-200 text-xs font-mono font-semibold">
                        <?= number_format($oppositionPercent, 1, ',', '.') ?>%
                    </span>
                </div>
                <p class="mt-2 text-xs text-slate-500">Terdiri atas <?= $oppositionCount ?> fraksi penyeimbang di DPR RI.</p>
            </div>
        </div>

        <!-- Visual Proportional Bar & Faction Breakdown Table -->
        <div class="bg-white border border-slate-200 rounded-xl overflow-hidden">
            <div class="p-6 border-b border-slate-200 space-y-3">
                <div class="flex items-center justify-between text-xs font-mono text-slate-600">
                    <span>Proporsi Koalisi Pemerintahan (<?= number_format($coalitionPercent, 1, ',', '.') ?>%)</span>
                    <span>Oposisi Parlemen (<?= number_format($oppositionPercent, 1, ',', '.') ?>%)</span>
                </div>
                <div class="w-full h-3.5 rounded-full bg-slate-100 overflow-hidden flex">
                    <div
                        class="h-full bg-crimson-700"
                        style="width: <?= htmlspecialchars((string) $coalitionPercent, ENT_QUOTES, 'UTF-8') ?>%"
                        title="Koalisi Pemerintah: <?= $coalitionSeats ?> Kursi"
                    ></div>
                    <div
                        class="h-full bg-slate-700"
                        style="width: <?= htmlspecialchars((string) $oppositionPercent, ENT_QUOTES, 'UTF-8') ?>%"
                        title="Oposisi Parlemen: <?= $oppositionSeats ?> Kursi"
                    ></div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-sm">
                    <thead>
                        <tr class="border-b border-slate-200 bg-slate-50/80 text-[11px] font-mono uppercase tracking-wider text-slate-500">
                            <th class="py-3.5 px-5">Fraksi Partai Politik</th>
                            <th class="py-3.5 px-5">Ideologi Utama</th>
                            <th class="py-3.5 px-5">Posisi di DPR</th>
                            <th class="py-3.5 px-5 text-right">Perolehan Kursi</th>
                            <th class="py-3.5 px-5 text-right">Persentase</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        <?php foreach (($politicalParties ?? []) as $party): ?>
                            <?php
                            $acronym = htmlspecialchars((string) ($party['party_acronym'] ?? ''), ENT_QUOTES, 'UTF-8');
                            $name = htmlspecialchars((string) ($party['party_name'] ?? ''), ENT_QUOTES, 'UTF-8');
                            $ideology = htmlspecialchars((string) ($party['ideology'] ?? ''), ENT_QUOTES, 'UTF-8');
                            $isCoalition = ($party['coalition_status'] ?? '') === 'KOALISI_PEMERINTAH';
                            $coalitionLabel = htmlspecialchars((string) ($party['coalition_label'] ?? ''), ENT_QUOTES, 'UTF-8');
                            $seats = (int) ($party['dpr_seats'] ?? 0);
                            $percent = number_format((float) ($party['seat_percentage'] ?? 0), 1, ',', '.');
                            ?>
                            <tr class="hover:bg-slate-50/70 transition-colors">
                                <td class="py-4 px-5">
                                    <div class="flex items-center gap-2.5">
                                        <span class="px-2 py-0.5 rounded font-mono text-xs font-bold bg-slate-900 text-white">
                                            <?= $acronym ?>
                                        </span>
                                        <span class="font-bold text-slate-900"><?= $name ?></span>
                                    </div>
                                </td>
                                <td class="py-4 px-5 text-xs text-slate-600">
                                    <?= $ideology ?>
                                </td>
                                <td class="py-4 px-5">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-medium border <?= $isCoalition ? 'bg-crimson-50 text-crimson-700 border-crimson-100' : 'bg-slate-100 text-slate-700 border-slate-200' ?>">
                                        <?= $coalitionLabel ?>
                                    </span>
                                </td>
                                <td class="py-4 px-5 text-right font-mono font-bold text-slate-900 tabular-nums">
                                    <?= $seats ?> Kursi
                                </td>
                                <td class="py-4 px-5 text-right font-mono text-xs text-slate-600 tabular-nums">
                                    <?= $percent ?>%
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</section>

