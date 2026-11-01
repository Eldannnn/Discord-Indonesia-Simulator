<?php

declare(strict_types=1);

/**
 * Presidential Cabinet & Ministerial Administration Tab (`Views/president/cabinet-tab.php`).
 * Dedicated workspace interface for managing Cabinet identity, inspecting Golongan I ministers (Triumvirat Konstitusional),
 * appointing eligible citizens to vacant posts, relieving incumbents, and viewing upcoming ministerial groups.
 *
 * @var array<string, mixed> $cabinetData
 * @var array<string, mixed> $authUser
 * @var string $presName
 * @var string $presNik
 */

$settings = (array) ($cabinetData['settings'] ?? []);
$cabinetName = htmlspecialchars((string) ($settings['cabinet_name'] ?? 'Kabinet Indonesia Emas'), ENT_QUOTES, 'UTF-8');
$cabinetVision = htmlspecialchars((string) ($settings['cabinet_vision'] ?? 'Akselerasi Pembangunan Terpadu, Kedaulatan Nasional, dan Transformasi Digital 2026–2031'), ENT_QUOTES, 'UTF-8');
$formationDate = htmlspecialchars((string) ($settings['formation_date'] ?? '2026-10-21'), ENT_QUOTES, 'UTF-8');
$presidentName = htmlspecialchars((string) ($settings['president_name'] ?? $presName), ENT_QUOTES, 'UTF-8');

$golonganIMinistries = (array) ($cabinetData['golongan_i'] ?? []);
$eligibleCandidates = (array) ($cabinetData['eligible_candidates'] ?? []);
$comingSoonGroups = (array) ($cabinetData['coming_soon_groups'] ?? []);
$occupiedCount = (int) ($cabinetData['occupied_count'] ?? 0);
$vacantCount = (int) ($cabinetData['vacant_count'] ?? 0);
$totalCount = (int) ($cabinetData['total_golongan_i'] ?? 3);
?>

<div class="space-y-8">

    <!-- 1. Executive Cabinet Identity Card -->
    <div class="bg-white border border-slate-200 rounded-2xl p-6 sm:p-8 shadow-xs relative overflow-hidden">
        <div class="absolute -right-16 -top-16 w-56 h-56 rounded-full bg-crimson-50/50 pointer-events-none" aria-hidden="true"></div>

        <div class="relative flex flex-col lg:flex-row lg:items-center justify-between gap-6">
            <div class="space-y-3 max-w-3xl">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-crimson-50 text-crimson-700 border border-crimson-100 text-xs font-mono font-semibold">
                        <span class="w-1.5 h-1.5 rounded-full bg-crimson-700"></span>
                        <span>Hak Prerogatif Presiden RI &bull; Pasal 17 UUD 1945</span>
                    </span>
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-slate-100 text-slate-600 text-xs font-mono">
                        <span>Periode 2026–2031</span>
                    </span>
                </div>

                <div>
                    <h2 id="displayCabinetName" class="font-serif text-2xl sm:text-3xl font-bold text-slate-900 tracking-tight">
                        <?= $cabinetName ?>
                    </h2>
                    <p id="displayCabinetVision" class="mt-1 text-sm sm:text-base text-slate-600 leading-relaxed font-sans">
                        <?= $cabinetVision ?>
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-x-6 gap-y-2 text-xs text-slate-500 pt-1 font-mono">
                    <div class="flex items-center gap-1.5">
                        <span class="text-slate-400">Pimpinan:</span>
                        <strong class="text-slate-800 font-semibold"><?= $presidentName ?></strong>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span class="text-slate-400">Tanggal Pembentukan:</span>
                        <span class="text-slate-700"><?= date('d F Y', strtotime($formationDate)) ?></span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span class="text-slate-400">Status Operasional:</span>
                        <span class="text-emerald-700 font-semibold">Aktif Melaksanakan Tugas</span>
                    </div>
                </div>
            </div>

            <div class="shrink-0 flex sm:flex-col items-start sm:items-end justify-between gap-3 border-t sm:border-t-0 sm:border-l border-slate-200 pt-4 sm:pt-0 sm:pl-6">
                <button
                    type="button"
                    id="openEditCabinetNameBtn"
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-semibold text-white bg-slate-900 hover:bg-slate-800 transition-colors shadow-xs cursor-pointer"
                >
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path d="M12 20h9"></path>
                        <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path>
                    </svg>
                    <span>Ubah Nama &amp; Visi Kabinet</span>
                </button>
            </div>
        </div>
    </div>

    <!-- 2. Metric Overview Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="p-5 rounded-xl bg-white border border-slate-200 shadow-2xs space-y-1">
            <span class="text-[11px] font-mono uppercase tracking-wider text-slate-500 block">Kementerian Golongan I</span>
            <div class="flex items-baseline gap-2">
                <span class="font-serif text-3xl font-bold text-slate-900"><?= $totalCount ?></span>
                <span class="text-xs text-slate-500 font-sans">Kementerian Triumvirat</span>
            </div>
            <p class="text-[11px] text-slate-500">Menteri Luar Negeri, Dalam Negeri, dan Pertahanan.</p>
        </div>

        <div class="p-5 rounded-xl bg-white border border-slate-200 shadow-2xs space-y-1">
            <span class="text-[11px] font-mono uppercase tracking-wider text-emerald-700 font-semibold block">Pos Menteri Terisi</span>
            <div class="flex items-baseline gap-2">
                <span class="font-serif text-3xl font-bold text-emerald-800"><?= $occupiedCount ?></span>
                <span class="text-xs text-slate-500 font-sans">dari <?= $totalCount ?> menteri definitif</span>
            </div>
            <p class="text-[11px] text-slate-500">Menjabat aktif dengan hak prerogatif Presiden.</p>
        </div>

        <div class="p-5 rounded-xl bg-white border border-slate-200 shadow-2xs space-y-1">
            <span class="text-[11px] font-mono uppercase tracking-wider text-crimson-700 font-semibold block">Warga Memenuhi Syarat</span>
            <div class="flex items-baseline gap-2">
                <span class="font-serif text-3xl font-bold text-slate-900"><?= count($eligibleCandidates) ?></span>
                <span class="text-xs text-slate-500 font-sans">Kandidat Warga Aktif</span>
            </div>
            <p class="text-[11px] text-slate-500">Warga tanpa jabatan kenegaraan lain yang siap diangkat.</p>
        </div>
    </div>

    <!-- 3. Section: Menteri Golongan I (Triumvirat Konstitusional) -->
    <div class="space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-2 border-b border-slate-200">
            <div>
                <div class="inline-flex items-center gap-1.5 text-xs font-mono uppercase tracking-wider text-crimson-700 font-semibold">
                    <span class="w-1.5 h-1.5 rounded-full bg-crimson-700"></span>
                    <span>Kelompok I &bull; Triumvirat Konstitusional Negara</span>
                </div>
                <h3 class="font-serif text-xl sm:text-2xl font-bold text-slate-900 mt-0.5">
                    Menteri Negara Golongan I
                </h3>
            </div>
            <p class="text-xs text-slate-500 max-w-xl text-left sm:text-right">
                Nomenklatur kementerian diamanatkan langsung oleh UUD NRI 1945 Pasal 8 ayat (3) &amp; Pasal 17 ayat (3). Tidak dapat diubah atau dibubarkan secara sepihak.
            </p>
        </div>

        <!-- 3 Ministerial Cards -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <?php foreach ($golonganIMinistries as $min): ?>
                <?php
                $minId = (int) $min['id'];
                $minCode = htmlspecialchars((string) $min['ministry_code'], ENT_QUOTES, 'UTF-8');
                $minName = htmlspecialchars((string) $min['ministry_name'], ENT_QUOTES, 'UTF-8');
                $minTitle = htmlspecialchars((string) $min['minister_title'], ENT_QUOTES, 'UTF-8');
                $constBasis = htmlspecialchars((string) $min['constitutional_basis'], ENT_QUOTES, 'UTF-8');
                $scopeDesc = htmlspecialchars((string) $min['scope_description'], ENT_QUOTES, 'UTF-8');
                $isOccupied = (bool) ($min['is_occupied'] ?? false);

                $incumbentName = htmlspecialchars((string) ($min['character_name'] ?? ''), ENT_QUOTES, 'UTF-8');
                $incumbentNik = htmlspecialchars((string) ($min['nik_rp'] ?? ''), ENT_QUOTES, 'UTF-8');
                $incumbentParty = htmlspecialchars((string) ($min['faction_party'] ?? 'Independen'), ENT_QUOTES, 'UTF-8');
                $incumbentDomicile = htmlspecialchars((string) ($min['domicile_province'] ?? 'DKI Jakarta'), ENT_QUOTES, 'UTF-8');
                $appointedLabel = htmlspecialchars((string) ($min['appointed_label'] ?? ''), ENT_QUOTES, 'UTF-8');
                $decreeRef = htmlspecialchars((string) ($min['decree_reference'] ?? ''), ENT_QUOTES, 'UTF-8');
                ?>

                <div class="bg-white border <?= $isOccupied ? 'border-slate-200' : 'border-amber-200 bg-amber-50/10' ?> rounded-2xl p-6 shadow-xs flex flex-col justify-between space-y-6">
                    <div class="space-y-4">
                        <!-- Card Header -->
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <span class="px-2 py-0.5 rounded text-[11px] font-mono font-bold <?= $isOccupied ? 'bg-slate-100 text-slate-700' : 'bg-amber-100 text-amber-800' ?>">
                                    <?= $minCode ?>
                                </span>
                                <h4 class="text-base font-bold text-slate-900 mt-1.5">
                                    <?= $minName ?>
                                </h4>
                                <span class="text-xs text-slate-500 font-medium block">
                                    <?= $minTitle ?>
                                </span>
                            </div>

                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-mono font-medium <?= $isOccupied ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-amber-50 text-amber-800 border border-amber-200' ?>">
                                <span class="w-1.5 h-1.5 rounded-full <?= $isOccupied ? 'bg-emerald-600' : 'bg-amber-600' ?>"></span>
                                <span><?= $isOccupied ? 'Menjabat' : 'Kosong' ?></span>
                            </span>
                        </div>

                        <p class="text-xs text-slate-600 leading-relaxed">
                            <?= $scopeDesc ?>
                        </p>

                        <div class="pt-1">
                            <span class="text-[10px] font-mono uppercase tracking-wider text-slate-400 block font-semibold">Dasar Konstitusional:</span>
                            <span class="text-xs font-mono text-slate-700"><?= $constBasis ?></span>
                        </div>

                        <!-- Incumbent Details vs Vacant State -->
                        <div class="pt-3 border-t border-slate-100">
                            <?php if ($isOccupied): ?>
                                <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200 space-y-2">
                                    <div class="flex items-center justify-between">
                                        <span class="text-[10px] uppercase font-mono tracking-wider text-slate-400 font-semibold">Pejabat Menjabat</span>
                                        <span class="text-[10px] font-mono text-slate-500"><?= $decreeRef ?></span>
                                    </div>
                                    <div>
                                        <h5 class="font-serif font-bold text-slate-900 text-sm">
                                            <?= $incumbentName ?>
                                        </h5>
                                        <p class="text-xs text-slate-500 font-mono mt-0.5">
                                            NIK: <?= $incumbentNik ?> &bull; <?= $incumbentParty ?>
                                        </p>
                                    </div>
                                    <div class="text-[11px] text-slate-500 pt-1 border-t border-slate-200/60 flex items-center justify-between">
                                        <span>Dilantik:</span>
                                        <span class="font-medium text-slate-700"><?= $appointedLabel ?></span>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="p-3.5 rounded-xl bg-amber-50 border border-amber-200/80 space-y-2">
                                    <div class="flex items-center gap-1.5 text-xs font-semibold text-amber-900">
                                        <svg class="w-4 h-4 text-amber-700 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                            <circle cx="12" cy="12" r="10"></circle>
                                            <line x1="12" y1="8" x2="12" y2="12"></line>
                                            <line x1="12" y1="16" x2="12.01" y2="16"></line>
                                        </svg>
                                        <span>Jabatan Sedang Kosong</span>
                                    </div>
                                    <p class="text-[11px] text-amber-800 leading-relaxed">
                                        Pos menteri ini belum memiliki pejabat definitif. Silakan pilih warga negara yang memenuhi syarat di bawah untuk diangkat menjadi menteri.
                                    </p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Actions Area -->
                    <div class="pt-2">
                        <?php if ($isOccupied): ?>
                            <button
                                type="button"
                                class="openDismissMinisterBtn w-full inline-flex items-center justify-center gap-2 px-3.5 py-2.5 rounded-lg text-xs font-semibold text-crimson-700 hover:text-white bg-crimson-50 hover:bg-crimson-700 border border-crimson-200 hover:border-crimson-700 transition-colors cursor-pointer"
                                data-ministry-id="<?= $minId ?>"
                                data-ministry-name="<?= $minName ?>"
                                data-minister-title="<?= $minTitle ?>"
                                data-incumbent-name="<?= $incumbentName ?>"
                            >
                                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                    <circle cx="8.5" cy="7" r="4"></circle>
                                    <line x1="23" y1="11" x2="17" y2="11"></line>
                                </svg>
                                <span>Berhentikan dari Jabatan</span>
                            </button>
                        <?php else: ?>
                            <form class="appointMinisterForm space-y-2.5" novalidate data-ministry-id="<?= $minId ?>" data-ministry-name="<?= $minName ?>" data-minister-title="<?= $minTitle ?>">
                                <div>
                                    <label class="block text-[11px] font-semibold text-slate-700 mb-1">
                                        Pilih Calon Menteri (Warga Aktif Tanpa Jabatan):
                                    </label>
                                    <select
                                        name="candidate_user_id"
                                        class="w-full px-3 py-2 rounded-lg bg-white border border-slate-300 text-xs text-slate-900 focus:outline-none focus:border-slate-900"
                                    >
                                        <option value="">-- Pilih Warga Negara Memenuhi Syarat --</option>
                                        <?php if (empty($eligibleCandidates)): ?>
                                            <option value="" disabled>Tidak ada calon warga aktif tersedia</option>
                                        <?php else: ?>
                                            <?php foreach ($eligibleCandidates as $cand): ?>
                                                <option value="<?= (int) $cand['id'] ?>">
                                                    <?= htmlspecialchars((string) $cand['character_name'], ENT_QUOTES, 'UTF-8') ?> &bull; <?= htmlspecialchars((string) $cand['faction_party'], ENT_QUOTES, 'UTF-8') ?> (<?= htmlspecialchars((string) $cand['domicile_province'], ENT_QUOTES, 'UTF-8') ?>)
                                                </option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                </div>

                                <button
                                    type="submit"
                                    class="w-full inline-flex items-center justify-center gap-2 px-3.5 py-2.5 rounded-lg text-xs font-semibold text-white bg-crimson-700 hover:bg-crimson-800 transition-colors cursor-pointer shadow-xs"
                                >
                                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                        <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                        <circle cx="8.5" cy="7" r="4"></circle>
                                        <line x1="20" y1="8" x2="20" y2="14"></line>
                                        <line x1="23" y1="11" x2="17" y2="11"></line>
                                    </svg>
                                    <span>Tetapkan sebagai Menteri</span>
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- 4. Section: Menteri Golongan Lainnya & Pejabat Setingkat Menteri (Coming Soon) -->
    <div class="space-y-4 pt-4 border-t border-slate-200">
        <div>
            <div class="inline-flex items-center gap-1.5 text-xs font-mono uppercase tracking-wider text-slate-500 font-semibold">
                <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                <span>Kementerian Golongan II, Golongan III &amp; Lembaga Terkait</span>
            </div>
            <h3 class="font-serif text-xl sm:text-2xl font-bold text-slate-900 mt-0.5">
                Kementerian Negara Lainnya (Coming Soon)
            </h3>
            <p class="text-xs text-slate-500 mt-0.5">
                Struktur kementerian berikut sedang dalam tahap finalisasi draf regulasi penataan kelembagaan kabinet lanjutan.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <?php foreach ($comingSoonGroups as $group): ?>
                <div class="p-6 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-4 flex flex-col justify-between opacity-90">
                    <div class="space-y-2.5">
                        <div class="flex items-center justify-between gap-2">
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-mono font-bold bg-slate-200/80 text-slate-700">
                                <?= htmlspecialchars((string) $group['badge'], ENT_QUOTES, 'UTF-8') ?>
                            </span>
                        </div>

                        <h4 class="text-base font-bold text-slate-800">
                            <?= htmlspecialchars((string) $group['group_title'], ENT_QUOTES, 'UTF-8') ?>
                        </h4>

                        <p class="text-xs text-slate-500 leading-relaxed">
                            <?= htmlspecialchars((string) $group['description'], ENT_QUOTES, 'UTF-8') ?>
                        </p>

                        <div class="pt-2">
                            <span class="text-[10px] font-mono uppercase tracking-wider text-slate-400 font-semibold block mb-1.5">Contoh Nomenklatur Pos:</span>
                            <ul class="space-y-1 text-xs text-slate-600">
                                <?php foreach ((array) $group['examples'] as $ex): ?>
                                    <li class="flex items-center gap-1.5">
                                        <span class="w-1 h-1 rounded-full bg-slate-400"></span>
                                        <span><?= htmlspecialchars((string) $ex, ENT_QUOTES, 'UTF-8') ?></span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>

                    <div class="pt-3 border-t border-slate-200 text-center">
                        <span class="text-xs font-mono text-slate-400 italic">
                            Modul ini akan dibuka pada pembaruan mendatang
                        </span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

</div>

<!-- ======================================================== -->
<!-- MODAL: UBAH NAMA & VISI KABINET                          -->
<!-- ======================================================== -->
<div
    id="updateCabinetModal"
    class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs print-hidden"
    role="dialog"
    aria-modal="true"
    aria-labelledby="editCabinetTitle"
>
    <div class="w-full max-w-lg rounded-2xl bg-white border border-slate-200 p-6 sm:p-7 shadow-xl space-y-5">
        <div class="flex items-start justify-between pb-3 border-b border-slate-200">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-crimson-50 text-crimson-700 border border-crimson-100 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 20h9"></path>
                        <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path>
                    </svg>
                </div>
                <div>
                    <h3 id="editCabinetTitle" class="text-base font-bold text-slate-900">
                        Ubah Identitas Nama &amp; Visi Kabinet
                    </h3>
                    <p class="text-xs text-slate-500">Ditetapkan oleh Presiden Republik Indonesia</p>
                </div>
            </div>

            <button
                type="button"
                id="closeEditCabinetModalBtn"
                class="p-1 rounded text-slate-400 hover:text-slate-600 transition-colors cursor-pointer"
            >
                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>

        <form id="editCabinetForm" class="space-y-4" novalidate>
            <div id="cabinetFormError" class="hidden p-3 rounded-lg bg-crimson-50 border border-crimson-100 text-xs text-crimson-700"></div>

            <div>
                <label for="cabinet_name_input" class="block text-xs font-semibold text-slate-700 mb-1">
                    Nama Resmi Kabinet Pemerintahan <span class="text-crimson-600">*</span>
                </label>
                <input
                    type="text"
                    id="cabinet_name_input"
                    name="cabinet_name"
                    value="<?= $cabinetName ?>"
                    placeholder="Contoh: Kabinet Indonesia Emas"
                    class="w-full px-3.5 py-2.5 rounded-lg bg-white border border-slate-300 text-sm text-slate-900 focus:outline-none focus:border-slate-900"
                >
                <p id="err-cabinet_name" class="hidden mt-1 text-xs text-crimson-600 font-medium"></p>
            </div>

            <div>
                <label for="cabinet_vision_input" class="block text-xs font-semibold text-slate-700 mb-1">
                    Visi &amp; Sasaran Strategis Kabinet <span class="text-crimson-600">*</span>
                </label>
                <textarea
                    id="cabinet_vision_input"
                    name="cabinet_vision"
                    rows="3"
                    placeholder="Uraikan visi dan sasaran strategis kabinet..."
                    class="w-full px-3.5 py-2 rounded-lg bg-white border border-slate-300 text-sm text-slate-900 focus:outline-none focus:border-slate-900"
                ><?= $cabinetVision ?></textarea>
                <p id="err-cabinet_vision" class="hidden mt-1 text-xs text-crimson-600 font-medium"></p>
            </div>

            <div class="flex justify-end gap-2.5 pt-3 border-t border-slate-200">
                <button
                    type="button"
                    id="cancelEditCabinetBtn"
                    class="px-4 py-2 rounded-lg text-xs font-medium text-slate-600 hover:bg-slate-100 transition-colors cursor-pointer"
                >
                    Batal
                </button>
                <button
                    type="submit"
                    id="saveCabinetBtn"
                    class="px-5 py-2 rounded-lg text-xs font-semibold text-white bg-crimson-700 hover:bg-crimson-800 transition-colors cursor-pointer shadow-xs"
                >
                    Simpan Perubahan Kabinet
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ======================================================== -->
<!-- MODAL: KONFIRMASI PENGANGKATAN MENTERI                   -->
<!-- ======================================================== -->
<div
    id="appointMinisterConfirmModal"
    class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs print-hidden"
    role="dialog"
    aria-modal="true"
    aria-labelledby="appointConfirmTitle"
>
    <div class="w-full max-w-md rounded-2xl bg-white border border-slate-200 p-6 shadow-xl space-y-4">
        <div class="flex items-start gap-3">
            <div class="w-9 h-9 rounded-lg bg-emerald-50 border border-emerald-100 flex items-center justify-center text-emerald-700 shrink-0">
                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                    <circle cx="8.5" cy="7" r="4"></circle>
                    <line x1="20" y1="8" x2="20" y2="14"></line>
                    <line x1="23" y1="11" x2="17" y2="11"></line>
                </svg>
            </div>
            <div>
                <h3 id="appointConfirmTitle" class="text-base font-bold text-slate-900">
                    Konfirmasi Penetapan Menteri Negara
                </h3>
                <p class="mt-1 text-xs text-slate-600 leading-relaxed">
                    Apakah Anda yakin hendak menetapkan <strong id="appointCandidateNameDisplay" class="text-slate-900 font-semibold"></strong> memimpin <strong id="appointMinistryNameDisplay" class="text-slate-900 font-semibold"></strong> melalui Keputusan Presiden RI?
                </p>
            </div>
        </div>

        <div class="p-3 rounded-lg bg-slate-50 border border-slate-200 text-xs text-slate-500 font-mono">
            Sistem telah memverifikasi calon tidak memegang jabatan kenegaraan lain.
        </div>

        <div class="flex justify-end gap-2 pt-3 border-t border-slate-200">
            <button
                type="button"
                id="cancelAppointBtn"
                class="px-3.5 py-2 rounded-lg text-xs font-medium text-slate-600 hover:bg-slate-100 transition-colors cursor-pointer"
            >
                Batal
            </button>
            <button
                type="button"
                id="proceedAppointBtn"
                class="px-4 py-2 rounded-lg text-xs font-semibold text-white bg-crimson-700 hover:bg-crimson-800 transition-colors cursor-pointer"
            >
                Ya, Tetapkan &amp; Lantik
            </button>
        </div>
    </div>
</div>

<!-- ======================================================== -->
<!-- MODAL: KONFIRMASI PEMBERHENTIAN MENTERI                  -->
<!-- ======================================================== -->
<div
    id="dismissMinisterConfirmModal"
    class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs print-hidden"
    role="dialog"
    aria-modal="true"
    aria-labelledby="dismissConfirmTitle"
>
    <div class="w-full max-w-md rounded-2xl bg-white border border-slate-200 p-6 shadow-xl space-y-4">
        <div class="flex items-start gap-3">
            <div class="w-9 h-9 rounded-lg bg-crimson-50 border border-crimson-100 flex items-center justify-center text-crimson-700 shrink-0">
                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                    <circle cx="8.5" cy="7" r="4"></circle>
                    <line x1="23" y1="11" x2="17" y2="11"></line>
                </svg>
            </div>
            <div>
                <h3 id="dismissConfirmTitle" class="text-base font-bold text-slate-900">
                    Konfirmasi Pemberhentian Dengan Hormat
                </h3>
                <p class="mt-1 text-xs text-slate-600 leading-relaxed">
                    Apakah Anda yakin hendak memberhentikan dengan hormat <strong id="dismissIncumbentNameDisplay" class="text-slate-900 font-semibold"></strong> dari jabatan <strong id="dismissMinistryTitleDisplay" class="text-slate-900 font-semibold"></strong>?
                </p>
            </div>
        </div>

        <div>
            <label for="dismiss_reason_input" class="block text-xs font-semibold text-slate-700 mb-1">
                Catatan / Alasan Evaluasi Kabinet (Opsional):
            </label>
            <input
                type="text"
                id="dismiss_reason_input"
                placeholder="Contoh: Penataan susunan kabinet dan evaluasi kinerja..."
                class="w-full px-3 py-2 rounded-lg bg-white border border-slate-300 text-xs text-slate-900 focus:outline-none focus:border-slate-900"
            >
        </div>

        <div class="flex justify-end gap-2 pt-3 border-t border-slate-200">
            <button
                type="button"
                id="cancelDismissBtn"
                class="px-3.5 py-2 rounded-lg text-xs font-medium text-slate-600 hover:bg-slate-100 transition-colors cursor-pointer"
            >
                Batal
            </button>
            <button
                type="button"
                id="proceedDismissBtn"
                class="px-4 py-2 rounded-lg text-xs font-semibold text-white bg-crimson-700 hover:bg-crimson-800 transition-colors cursor-pointer"
            >
                Ya, Berhentikan dengan Hormat
            </button>
        </div>
    </div>
</div>
