<?php

declare(strict_types=1);

/**
 * Constitutional Authority Explorer Component (`Views/components/rbac-explorer.php`).
 * Displays the constitutional capabilities of the President and Vice President of Indonesia
 * in clean, in-universe legal language with zero technical/backend terminology.
 *
 * @var array<int, array<string, mixed>> $roles Enriched role definitions
 */
$initialRole = $roles[0] ?? [];
$initialHolder = $initialRole['holder_user'] ?? [];
?>
<section id="wewenang-jabatan" class="py-16 sm:py-24 border-b border-slate-200">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">

        <div class="max-w-3xl mb-10">
            <p class="text-xs font-mono uppercase tracking-wider text-crimson-700 font-semibold">
                Kewenangan Konstitusional Lembaga Kepresidenan
            </p>
            <h2 class="mt-2 font-serif text-3xl sm:text-4xl font-medium text-slate-900 tracking-tight">
                Hak, Kewajiban, dan Batasan Jabatan Presiden &amp; Wakil Presiden
            </h2>
            <p class="mt-2.5 text-sm sm:text-base text-slate-600 leading-relaxed">
                Pilih jabatan <strong class="text-slate-900 font-semibold">Presiden Republik Indonesia</strong> atau <strong class="text-slate-900 font-semibold">Wakil Presiden Republik Indonesia</strong> di bawah ini untuk melihat pejabat yang sedang menjabat, dasar pasal UUD 1945, serta mekanisme pengawasan bersama DPR RI dan Mahkamah Agung.
            </p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

            <!-- Left Column: Role Selector & Constitutional Note -->
            <div class="lg:col-span-4 space-y-4">
                <div class="bg-white border border-slate-200 rounded-xl p-2 space-y-1.5" role="tablist" aria-label="Pilih Jabatan Kepresidenan">
                    <?php foreach ($roles as $index => $role): ?>
                        <?php
                        $isActive = $index === 0;
                        $roleCode = htmlspecialchars((string) $role['role_code'], ENT_QUOTES, 'UTF-8');
                        $roleTitle = htmlspecialchars((string) $role['role_title'], ENT_QUOTES, 'UTF-8');
                        $branchName = htmlspecialchars((string) $role['branch_name'], ENT_QUOTES, 'UTF-8');
                        $holderName = htmlspecialchars((string) ($role['holder_user']['character_name'] ?? '-'), ENT_QUOTES, 'UTF-8');
                        ?>
                        <button
                            type="button"
                            role="tab"
                            aria-selected="<?= $isActive ? 'true' : 'false' ?>"
                            data-rbac-role-btn="<?= $roleCode ?>"
                            class="w-full text-left px-4 py-3.5 rounded-lg transition-colors cursor-pointer flex items-start justify-between gap-3 <?= $isActive ? 'bg-slate-900 text-white' : 'text-slate-700 hover:bg-slate-100' ?>"
                        >
                            <div class="min-w-0 space-y-0.5">
                                <p class="text-sm font-bold truncate"><?= $roleTitle ?></p>
                                <p class="text-xs truncate <?= $isActive ? 'text-slate-300' : 'text-slate-500' ?>" data-role-subtext>
                                    <?= $branchName ?>
                                </p>
                                <p class="text-[11px] font-mono pt-1 truncate <?= $isActive ? 'text-amber-300' : 'text-crimson-700' ?>" data-role-holder>
                                    Pejabat: <?= $holderName ?>
                                </p>
                            </div>
                            <span
                                data-role-pill
                                class="text-[11px] font-mono px-2 py-0.5 rounded shrink-0 <?= $isActive ? 'bg-slate-800 text-slate-200' : 'bg-slate-100 text-slate-600' ?>"
                            >
                                Menjabat
                            </span>
                        </button>
                    <?php endforeach; ?>
                </div>

                <!-- Constitutional Principle Card -->
                <div class="bg-white border border-slate-200 rounded-xl p-5 space-y-2.5 text-xs text-slate-600 leading-relaxed">
                    <p class="font-mono font-semibold uppercase tracking-wider text-slate-800">
                        Prinsip Konstitusional
                    </p>
                    <p>
                        Menurut <strong class="text-slate-900">Pasal 4 ayat (1) dan (2) UUD 1945</strong>, Presiden memegang kekuasaan pemerintahan tertinggi dan dalam melakukan kewajibannya dibantu oleh satu orang Wakil Presiden.
                    </p>
                    <p>
                        Kewenangan Kepala Negara seperti menyatakan perang, memberi amnesti, atau mengangkat duta besar tidak dapat dijalankan sepihak, melainkan wajib melalui persetujuan atau pertimbangan <strong class="text-slate-900">DPR RI</strong> maupun <strong class="text-slate-900">Mahkamah Agung</strong>.
                    </p>
                </div>
            </div>

            <!-- Right Column: Detailed Constitutional Specification Sheet -->
            <div class="lg:col-span-8 bg-white border border-slate-200 rounded-xl overflow-hidden shadow-sm">

                <!-- Role Header & Active Official Profile -->
                <div class="p-6 sm:p-8 border-b border-slate-200">
                    <div class="flex flex-wrap items-center gap-2 text-xs font-mono text-slate-500 mb-2">
                        <span id="inspectorClearanceBadge" class="text-crimson-700 font-semibold">
                            <?= htmlspecialchars((string) ($initialRole['clearance_level'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                        </span>
                        <span>&bull;</span>
                        <span id="inspectorRecruitmentBadge">
                            <?= htmlspecialchars((string) ($initialRole['recruitment_type'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                        </span>
                    </div>

                    <h3 id="inspectorRoleTitle" class="text-2xl font-bold text-slate-900">
                        <?= htmlspecialchars((string) ($initialRole['role_title'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                    </h3>

                    <p id="inspectorRoleSummary" class="mt-2 text-sm text-slate-600 leading-relaxed">
                        <?= htmlspecialchars((string) ($initialRole['summary_text'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                    </p>

                    <!-- Active Official Record Box -->
                    <div class="mt-5 p-4 rounded-xl bg-slate-50 border border-slate-200/90 grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs">
                        <div>
                            <span class="text-slate-500 block">Pejabat Menjabat Saat Ini</span>
                            <strong id="inspectorHolderName" class="text-sm font-bold text-slate-900 mt-0.5 block">
                                <?= htmlspecialchars((string) ($initialHolder['character_name'] ?? '-'), ENT_QUOTES, 'UTF-8') ?>
                            </strong>
                            <span id="inspectorHolderUsername" class="font-mono text-slate-500">
                                <?= htmlspecialchars((string) ($initialHolder['faction_party'] ?? 'Koalisi Nusantara Bersatu'), ENT_QUOTES, 'UTF-8') ?>
                            </span>
                        </div>

                        <div>
                            <span class="text-slate-500 block">Nomor Induk &amp; Ketetapan MPR</span>
                            <span id="inspectorHolderNik" class="font-mono font-semibold text-slate-800 mt-0.5 block">
                                <?= htmlspecialchars((string) ($initialHolder['nik_rp'] ?? '-'), ENT_QUOTES, 'UTF-8') ?>
                            </span>
                            <span id="inspectorHolderDecree" class="text-slate-500">
                                <?= htmlspecialchars((string) ($initialHolder['inauguration_decree'] ?? '-'), ENT_QUOTES, 'UTF-8') ?>
                            </span>
                        </div>

                        <div>
                            <span class="text-slate-500 block">Dasar Hukum &amp; Hak Keuangan</span>
                            <strong id="inspectorConstitutionalBasis" class="font-mono text-crimson-700 font-semibold mt-0.5 block">
                                <?= htmlspecialchars((string) ($initialRole['constitutional_basis'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                            </strong>
                            <span id="inspectorSalary" class="font-mono text-slate-600">
                                <?= htmlspecialchars((string) ($initialRole['salary_formatted'] ?? ''), ENT_QUOTES, 'UTF-8') ?> / siklus
                            </span>
                        </div>
                    </div>
                </div>

                <!-- UUD 1945 Permissions Table -->
                <div class="p-6 sm:p-8 space-y-6">
                    <div>
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-3">
                            <h4 class="text-xs font-mono uppercase tracking-wider text-slate-500 font-semibold">
                                Rincian Kewenangan Menurut UUD 1945 &amp; Undang-Undang
                            </h4>
                            <span class="text-xs text-slate-500">Klik "Tinjau" untuk membaca penjelasan pasal</span>
                        </div>

                        <div class="overflow-x-auto border border-slate-200 rounded-lg">
                            <table class="w-full text-left border-collapse text-sm">
                                <thead>
                                    <tr class="border-b border-slate-200 bg-slate-50 text-xs font-medium text-slate-500">
                                        <th class="py-2.5 px-4">Tindakan &amp; Kewenangan Kenegaraan</th>
                                        <th class="py-2.5 px-4">Dasar Hukum &amp; Syarat Konstitusi</th>
                                        <th class="py-2.5 px-4">Kedudukan Wewenang</th>
                                        <th class="py-2.5 px-4 text-right">Penjelasan</th>
                                    </tr>
                                </thead>
                                <tbody id="inspectorPermissionsBody" class="divide-y divide-slate-200">
                                    <?php foreach (($initialRole['permissions'] ?? []) as $perm): ?>
                                        <?php
                                        $scope = htmlspecialchars((string) $perm['scope'], ENT_QUOTES, 'UTF-8');
                                        $label = htmlspecialchars((string) $perm['label'], ENT_QUOTES, 'UTF-8');
                                        $legalBasis = htmlspecialchars((string) ($perm['legal_basis'] ?? 'UUD 1945'), ENT_QUOTES, 'UTF-8');
                                        $organ = htmlspecialchars((string) ($perm['check_and_balance_organ'] ?? ''), ENT_QUOTES, 'UTF-8');
                                        $access = htmlspecialchars((string) $perm['access'], ENT_QUOTES, 'UTF-8');
                                        $authStatus = (string) ($perm['authorization_status'] ?? '');
                                        $isDenied = stripos($access, 'Ditolak') !== false
                                            || stripos($access, 'Tidak Berwenang') !== false
                                            || stripos($access, 'Dilarang') !== false
                                            || stripos($authStatus, 'DENIED') !== false;
                                        $isConditional = stripos($authStatus, 'CONDITIONAL') !== false;
                                        ?>
                                        <tr class="hover:bg-slate-50/80 transition-colors">
                                            <td class="py-3.5 px-4">
                                                <p class="text-slate-900 font-semibold leading-snug"><?= $label ?></p>
                                            </td>
                                            <td class="py-3.5 px-4">
                                                <span class="text-xs font-medium text-slate-800 block"><?= $legalBasis ?></span>
                                                <span class="text-[11px] font-mono text-slate-500 block mt-0.5"><?= $organ ?></span>
                                            </td>
                                            <td class="py-3.5 px-4">
                                                <?php if ($isDenied): ?>
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-crimson-50 text-crimson-700 border border-crimson-100">
                                                        <?= $access ?>
                                                    </span>
                                                <?php elseif ($isConditional): ?>
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-amber-50 text-amber-800 border border-amber-200">
                                                        <?= $access ?>
                                                    </span>
                                                <?php else: ?>
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-emerald-50 text-emerald-700 border border-emerald-200/70">
                                                        <?= $access ?>
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="py-3.5 px-4 text-right">
                                                <button
                                                    type="button"
                                                    data-test-rbac-scope="<?= $scope ?>"
                                                    data-test-rbac-role="<?= htmlspecialchars((string) ($initialRole['role_code'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                                                    class="px-2.5 py-1 rounded text-xs font-medium text-slate-700 bg-white hover:bg-slate-100 border border-slate-300 transition-colors cursor-pointer"
                                                >
                                                    Tinjau
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Clean Constitutional Explanation Box -->
                        <div class="mt-3 px-4 py-3.5 rounded-lg bg-slate-50 border border-slate-200 flex items-start justify-between gap-4 text-xs">
                            <p id="rbacConsoleOutput" class="text-slate-700 leading-relaxed">
                                Pilih tombol "Tinjau" pada salah satu baris di atas untuk membaca penjelasan konstitusional mengenai kewenangan tersebut.
                            </p>
                            <span id="rbacConsoleTimestamp" class="font-mono text-slate-400 shrink-0">UUD 1945</span>
                        </div>
                    </div>

                    <!-- Unlocked Responsibilities -->
                    <div class="pt-4 border-t border-slate-200">
                        <h4 class="text-xs font-mono uppercase tracking-wider text-slate-500 font-semibold mb-3">
                            Lingkup Tugas &amp; Ruang Kerja Kenegaraan
                        </h4>
                        <ul id="inspectorModulesList" class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                            <?php foreach (($initialRole['command_modules'] ?? []) as $moduleName): ?>
                                <li class="px-3.5 py-2.5 rounded-lg bg-slate-50 border border-slate-200/80 flex items-center gap-2.5 text-sm text-slate-700">
                                    <svg class="w-4 h-4 text-slate-500 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                        <polyline points="20 6 9 17 4 12"></polyline>
                                    </svg>
                                    <span><?= htmlspecialchars((string) $moduleName, ENT_QUOTES, 'UTF-8') ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>

                </div>

            </div>

        </div>

    </div>
</section>
