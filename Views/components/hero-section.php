<?php

declare(strict_types=1);

/**
 * National Information Portal Header & News Section (`Views/components/hero-section.php`).
 * Purely informational overview featuring National News (`national_news`) and Active Executive Leadership.
 *
 * @var array<string, mixed> $telemetry Live national metrics from MySQL
 * @var array<int, array<string, string>> $newsFeed Latest national news from MySQL
 * @var array<int, array<string, mixed>> $executiveUsers Active President & Vice President from MySQL
 * @var array<string, mixed> $dprSummary Aggregated DPR seat metrics
 * @var string $simulationEra Current roleplay period
 * @var array<string, mixed>|null $authUser Currently authenticated user session
 */
$totalDprSeats = (int) ($dprSummary['total_seats'] ?? 0);
$activeLawsCount = (int) ($telemetry['active_laws_db'] ?? 0);
$partiesCount = (int) ($telemetry['parties_count_db'] ?? 0);
$registeredCitizens = (int) ($telemetry['registered_citizens_db'] ?? 0);
$parliamentSession = htmlspecialchars((string) ($telemetry['parliament_session_status'] ?? ''), ENT_QUOTES, 'UTF-8');
$authRoleCode = (string) ($authUser['role_code'] ?? '');
?>
<section id="berita-nasional" class="py-12 sm:py-16 border-b border-slate-200">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- Top Editorial Masthead -->
        <div class="pb-8 border-b border-slate-200 flex flex-col lg:flex-row lg:items-end justify-between gap-6">
            <div>
                <div class="inline-flex items-center gap-2 text-xs font-mono uppercase tracking-wider text-crimson-700 font-semibold">
                    <span>Portal Informasi Kenegaraan</span>
                    <span class="text-slate-300">&bull;</span>
                    <span class="text-slate-500 font-normal"><?= htmlspecialchars((string) ($simulationEra ?? ''), ENT_QUOTES, 'UTF-8') ?></span>
                </div>
                <h1 class="mt-2 font-serif text-3xl sm:text-4xl lg:text-[44px] font-medium text-slate-900 tracking-tight leading-tight">
                    Pusat Data &amp; Berita Nasional Discord Indonesia Simulator
                </h1>
                <p class="mt-2 text-sm sm:text-base text-slate-600 max-w-2xl">
                    Pantau perkembangan berita pemerintahan, indikator makroekonomi, komposisi kursi parlemen DPR RI, regulasi yang berlaku, serta profil partai politik nasional.
                </p>
            </div>

            <!-- Role Workspace Shortcut if logged in as Presiden / Wakil Presiden -->
            <?php if ($authRoleCode === 'ROLE_PRESIDEN'): ?>
                <div class="shrink-0">
                    <a
                        href="?page=presiden"
                        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-lg text-sm font-semibold text-white bg-crimson-700 hover:bg-crimson-800 transition-colors"
                    >
                        <span>Buka Halaman Menu Presiden RI</span>
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <line x1="5" y1="12" x2="19" y2="12"></line>
                            <polyline points="12 5 19 12 12 19"></polyline>
                        </svg>
                    </a>
                </div>
            <?php elseif ($authRoleCode === 'ROLE_WAKIL_PRESIDEN'): ?>
                <div class="shrink-0">
                    <a
                        href="?page=wakil-presiden"
                        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-lg text-sm font-semibold text-white bg-slate-900 hover:bg-slate-800 transition-colors"
                    >
                        <span>Buka Halaman Menu Wakil Presiden RI</span>
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <line x1="5" y1="12" x2="19" y2="12"></line>
                            <polyline points="12 5 19 12 12 19"></polyline>
                        </svg>
                    </a>
                </div>
            <?php endif; ?>
        </div>

        <!-- Summary Key Indicators Bar -->
        <dl class="py-6 border-b border-slate-200 grid grid-cols-2 sm:grid-cols-4 gap-6">
            <div>
                <dt class="text-xs text-slate-500">Total Kursi DPR RI</dt>
                <dd class="mt-1 text-2xl font-bold font-mono text-slate-900 tabular-nums"><?= $totalDprSeats ?> Kursi</dd>
            </div>
            <div>
                <dt class="text-xs text-slate-500">Partai Politik Parlemen</dt>
                <dd class="mt-1 text-2xl font-bold font-mono text-slate-900 tabular-nums"><?= $partiesCount ?> Partai</dd>
            </div>
            <div>
                <dt class="text-xs text-slate-500">UU &amp; Regulasi Aktif</dt>
                <dd class="mt-1 text-2xl font-bold font-mono text-slate-900 tabular-nums"><?= $activeLawsCount ?> Naskah</dd>
            </div>
            <div>
                <dt class="text-xs text-slate-500">Warga &amp; Pejabat Terdaftar</dt>
                <dd class="mt-1 text-2xl font-bold font-mono text-slate-900 tabular-nums"><?= $registeredCitizens ?> Akun</dd>
            </div>
        </dl>

        <!-- Main Grid: National News (Left 7 Cols) & Active Executive Leadership (Right 5 Cols) -->
        <div class="mt-10 grid grid-cols-1 lg:grid-cols-12 gap-10 items-start">

            <!-- Left Column: Berita Nasional Terkini -->
            <div class="lg:col-span-7 space-y-5">
                <div class="flex items-center justify-between">
                    <h2 class="font-serif text-2xl font-medium text-slate-900">
                        Berita &amp; Siaran Pers Nasional
                    </h2>
                    <span class="text-xs font-mono text-slate-500">Pembaruan Langsung</span>
                </div>

                <div class="bg-white border border-slate-200 rounded-xl divide-y divide-slate-200 overflow-hidden">
                    <?php if (empty($newsFeed)): ?>
                        <div class="p-6 text-sm text-slate-500">Belum ada berita nasional yang diterbitkan.</div>
                    <?php else: ?>
                        <?php foreach ($newsFeed as $news): ?>
                            <?php
                            $category = htmlspecialchars((string) ($news['category'] ?? ''), ENT_QUOTES, 'UTF-8');
                            $headline = htmlspecialchars((string) ($news['headline'] ?? ''), ENT_QUOTES, 'UTF-8');
                            $summary = htmlspecialchars((string) ($news['summary'] ?? ''), ENT_QUOTES, 'UTF-8');
                            $author = htmlspecialchars((string) ($news['author_name'] ?? ''), ENT_QUOTES, 'UTF-8');
                            $pubDate = htmlspecialchars((string) ($news['published_label'] ?? ''), ENT_QUOTES, 'UTF-8');
                            ?>
                            <article class="p-5 sm:p-6 hover:bg-slate-50/70 transition-colors space-y-2">
                                <div class="flex flex-wrap items-center justify-between gap-2 text-xs font-mono">
                                    <span class="px-2 py-0.5 rounded bg-crimson-50 text-crimson-700 border border-crimson-100 font-semibold">
                                        <?= $category ?>
                                    </span>
                                    <span class="text-slate-400"><?= $pubDate ?></span>
                                </div>
                                <h3 class="text-base sm:text-lg font-bold text-slate-900 leading-snug">
                                    <?= $headline ?>
                                </h3>
                                <p class="text-sm text-slate-600 leading-relaxed">
                                    <?= $summary ?>
                                </p>
                                <div class="pt-1 text-xs text-slate-500 font-medium">
                                    Sumber: <?= $author ?>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Right Column: Pimpinan Pemerintahan & Status Sidang -->
            <div class="lg:col-span-5 space-y-6">
                <div class="bg-white border border-slate-200 rounded-xl shadow-xs overflow-hidden">
                    <div class="px-5 py-4 border-b border-slate-200 flex items-center justify-between bg-slate-50/70">
                        <span class="text-xs font-mono uppercase tracking-wider font-semibold text-slate-700">
                            Pimpinan Pemerintahan Negara
                        </span>
                        <span class="inline-flex items-center gap-1.5 text-xs font-mono text-emerald-700 bg-emerald-50 border border-emerald-200 px-2 py-0.5 rounded">
                            Masa Bakti 2026–2031
                        </span>
                    </div>

                    <div class="p-5 divide-y divide-slate-200 text-sm">
                        <?php foreach (($executiveUsers ?? []) as $execUser): ?>
                            <?php
                            $uRoleTitle = htmlspecialchars((string) ($execUser['role_title'] ?? ''), ENT_QUOTES, 'UTF-8');
                            $uName = htmlspecialchars((string) ($execUser['character_name'] ?? ''), ENT_QUOTES, 'UTF-8');
                            $uParty = htmlspecialchars((string) ($execUser['faction_party'] ?? ''), ENT_QUOTES, 'UTF-8');
                            $uDomicile = htmlspecialchars((string) ($execUser['domicile_province'] ?? ''), ENT_QUOTES, 'UTF-8');
                            ?>
                            <div class="py-3.5 first:pt-0 last:pb-0">
                                <span class="text-xs font-mono font-semibold text-crimson-700 block"><?= $uRoleTitle ?></span>
                                <p class="font-bold text-slate-900 mt-1 text-base"><?= $uName ?></p>
                                <p class="text-xs text-slate-600 mt-0.5">Asal Fraksi: <?= $uParty ?></p>
                                <div class="mt-2 flex items-center justify-between text-xs text-slate-500">
                                    <span>Kedudukan: <?= $uDomicile ?></span>
                                    <span class="font-mono text-emerald-700 font-medium">Menjabat Aktif</span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Parliament Session Status Box -->
                <div class="bg-white border border-slate-200 rounded-xl p-5 space-y-2">
                    <span class="text-xs font-mono uppercase tracking-wider text-slate-500 font-semibold block">
                        Agenda Parlemen Nasional
                    </span>
                    <p class="text-sm font-bold text-slate-900">
                        <?= $parliamentSession ?>
                    </p>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        Seluruh fraksi partai politik di DPR RI sedang melaksanakan rapat kerja pengawasan anggaran dan pembahasan rancangan undang-undang prioritas.
                    </p>
                </div>
            </div>

        </div>

    </div>
</section>
