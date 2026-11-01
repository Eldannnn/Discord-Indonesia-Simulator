<?php

declare(strict_types=1);

/**
 * Dedicated Vice-Presidential Workspace Page (`Views/vice-president/index.php`).
 * Separate page (`?page=wakil-presiden`) accessible ONLY when logged in as `ROLE_WAKIL_PRESIDEN`.
 * Allows the Vice President to issue Instructions, Cabinet Coordination Minutes, and Ministry Audits
 * without citing constitutional articles.
 *
 * @var string $appName
 * @var string $activePage
 * @var string $simulationEra
 * @var array<string, mixed> $authUser
 * @var array<string, mixed> $telemetry
 * @var array<int, array<string, mixed>> $vicePresidentPermissions
 * @var array<int, array<string, string>> $vicePresidentDecrees
 */
$componentsDir = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR;

$vpName = htmlspecialchars((string) ($authUser['character_name'] ?? ''), ENT_QUOTES, 'UTF-8');
$vpNik = htmlspecialchars((string) ($authUser['nik_rp'] ?? ''), ENT_QUOTES, 'UTF-8');
$vpParty = htmlspecialchars((string) ($authUser['faction_party'] ?? ''), ENT_QUOTES, 'UTF-8');

require $componentsDir . 'navbar.php';
?>

<main id="main-content" class="flex-1 py-12 sm:py-16">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">

        <!-- Page Header -->
        <div class="pb-8 border-b border-slate-200 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <div class="inline-flex items-center gap-2 text-xs font-mono uppercase tracking-wider text-slate-800 font-semibold">
                    <span class="w-2 h-2 rounded-full bg-slate-900" aria-hidden="true"></span>
                    <span>Halaman Khusus Wakil Presiden Republik Indonesia</span>
                </div>
                <h1 class="mt-2 font-serif text-3xl sm:text-4xl font-medium text-slate-900">
                    Ruang Koordinasi &amp; Pengawasan Wakil Presiden
                </h1>
                <p class="mt-1.5 text-sm text-slate-600">
                    Pejabat Aktif: <strong class="text-slate-900 font-semibold"><?= $vpName ?></strong> &bull; NIK: <span class="font-mono"><?= $vpNik ?></span> &bull; <?= $vpParty ?>
                </p>
            </div>

            <a
                href="?page=beranda"
                class="self-start md:self-auto inline-flex items-center gap-2 px-4 py-2 rounded-lg text-xs font-semibold text-slate-700 bg-white hover:bg-slate-100 border border-slate-300 transition-colors"
            >
                <span>Kembali ke Menu Utama</span>
            </a>
        </div>

        <!-- Main Workspace Grid: Duties (Left 5 Cols) & Instruction Issuance Form (Right 7 Cols) -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

            <!-- Left Column: Lingkup Tugas Wakil Presiden -->
            <div class="lg:col-span-5 space-y-4">
                <h2 class="text-xs font-mono uppercase tracking-wider text-slate-500 font-semibold">
                    Lingkup Tugas &amp; Koordinasi Wakil Presiden
                </h2>

                <div class="bg-white border border-slate-200 rounded-xl divide-y divide-slate-200 text-sm">
                    <div class="p-5">
                        <span class="text-xs font-mono text-slate-700 font-semibold">Koordinasi Lintas Kementerian</span>
                        <h3 class="font-bold text-slate-900 mt-0.5">Reformasi Birokrasi &amp; Layanan Publik</h3>
                        <p class="text-xs text-slate-600 mt-1 leading-relaxed">
                            Mengoordinasikan penyederhanaan prosedur birokrasi nasional, percepatan pengentasan kemiskinan, dan pembangunan wilayah prioritas.
                        </p>
                    </div>

                    <div class="p-5">
                        <span class="text-xs font-mono text-slate-700 font-semibold">Pengawasan Eksekutif</span>
                        <h3 class="font-bold text-slate-900 mt-0.5">Evaluasi &amp; Audit Kinerja Kabinet</h3>
                        <p class="text-xs text-slate-600 mt-1 leading-relaxed">
                            Memimpin evaluasi capaian serapan anggaran dan target program strategis kementerian secara berkala.
                        </p>
                    </div>

                    <div class="p-5">
                        <span class="text-xs font-mono text-slate-700 font-semibold">Kepemimpinan Kabinet</span>
                        <h3 class="font-bold text-slate-900 mt-0.5">Pimpinan Rapat Koordinasi Nasional</h3>
                        <p class="text-xs text-slate-600 mt-1 leading-relaxed">
                            Memimpin rapat koordinasi lintas menteri koordinator serta menetapkan risalah tindak lanjut kebijakan pemerintah.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Right Column: Form Penerbitan Instruksi Wakil Presiden / Risalah Rakor / Audit -->
            <div class="lg:col-span-7 bg-white border border-slate-200 rounded-xl p-6 sm:p-8 shadow-xs">
                <h2 class="text-lg font-bold text-slate-900">
                    Terbitkan Instruksi Wakil Presiden &amp; Risalah Koordinasi
                </h2>
                <p class="text-xs text-slate-500 mt-1 mb-6">
                    Instruksi dan risalah koordinasi yang Anda terbitkan akan langsung tercatat pada Lembaran Keputusan Negara serta Berita Nasional di Menu Utama.
                </p>

                <form id="vicePresidentialActForm" class="space-y-4" novalidate>
                    <div id="vpActGlobalError" class="hidden p-3.5 rounded-lg bg-crimson-50 border border-crimson-100 text-xs text-crimson-700"></div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="vp_document_type" class="block text-xs font-semibold text-slate-700 mb-1.5">
                                Jenis Instrumen Wakil Presiden <span class="text-crimson-600">*</span>
                            </label>
                            <select
                                id="vp_document_type"
                                name="document_type"
                                class="w-full px-3.5 py-2 rounded-lg bg-white border border-slate-300 text-sm text-slate-900 focus:outline-none focus:border-slate-900"
                            >
                                <option value="">Pilih jenis instrumen...</option>
                                <option value="INSTRUKSI_WAPRES">Instruksi Wakil Presiden</option>
                                <option value="RISALAH_RAKOR_WAPRES">Risalah Rapat Koordinasi Kabinet</option>
                                <option value="AUDIT_KINERJA_KABINET">Nota Evaluasi &amp; Pengawasan Kementerian</option>
                            </select>
                            <p id="err-vp-document_type" class="hidden mt-1 text-xs text-crimson-600 font-medium"></p>
                        </div>

                        <div>
                            <label for="vp_document_number" class="block text-xs font-semibold text-slate-700 mb-1.5">
                                Nomor Registrasi Instruksi <span class="text-crimson-600">*</span>
                            </label>
                            <input
                                type="text"
                                id="vp_document_number"
                                name="document_number"
                                placeholder="Contoh: INS-WAPRES/08/2026"
                                class="w-full px-3.5 py-2 rounded-lg bg-white border border-slate-300 text-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:border-slate-900"
                            >
                            <p id="err-vp-document_number" class="hidden mt-1 text-xs text-crimson-600 font-medium"></p>
                        </div>
                    </div>

                    <div>
                        <label for="vp_permission_id" class="block text-xs font-semibold text-slate-700 mb-1.5">
                            Bidang Koordinasi &amp; Pengawasan <span class="text-crimson-600">*</span>
                        </label>
                        <select
                            id="vp_permission_id"
                            name="permission_id"
                            class="w-full px-3.5 py-2 rounded-lg bg-white border border-slate-300 text-sm text-slate-900 focus:outline-none focus:border-slate-900"
                        >
                            <option value="">Pilih bidang koordinasi Wakil Presiden...</option>
                            <?php foreach ($vicePresidentPermissions as $perm): ?>
                                <option value="<?= (int) $perm['permission_id'] ?>">
                                    <?= htmlspecialchars((string) $perm['permission_name'], ENT_QUOTES, 'UTF-8') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <p id="err-vp-permission_id" class="hidden mt-1 text-xs text-crimson-600 font-medium"></p>
                    </div>

                    <div>
                        <label for="vp_title" class="block text-xs font-semibold text-slate-700 mb-1.5">
                            Judul Instruksi / Agenda Koordinasi Nasional <span class="text-crimson-600">*</span>
                        </label>
                        <input
                            type="text"
                            id="vp_title"
                            name="title"
                            placeholder="Contoh: Instruksi Percepatan Reformasi Birokrasi & Pelayanan Terpadu"
                            class="w-full px-3.5 py-2 rounded-lg bg-white border border-slate-300 text-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:border-slate-900"
                        >
                        <p id="err-vp-title" class="hidden mt-1 text-xs text-crimson-600 font-medium"></p>
                    </div>

                    <div>
                        <label for="vp_summary_content" class="block text-xs font-semibold text-slate-700 mb-1.5">
                            Pokok Arahan &amp; Sasaran Evaluasi Kementerian <span class="text-crimson-600">*</span>
                        </label>
                        <textarea
                            id="vp_summary_content"
                            name="summary_content"
                            rows="3"
                            placeholder="Uraikan pokok arahan koordinasi atau hasil pengawasan kinerja kementerian..."
                            class="w-full px-3.5 py-2 rounded-lg bg-white border border-slate-300 text-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:border-slate-900"
                        ></textarea>
                        <p id="err-vp-summary_content" class="hidden mt-1 text-xs text-crimson-600 font-medium"></p>
                    </div>

                    <div class="pt-3 flex justify-end">
                        <button
                            type="submit"
                            id="vpActSubmitBtn"
                            class="inline-flex items-center gap-2 px-5 py-2.5 rounded-lg text-sm font-semibold text-white bg-slate-900 hover:bg-slate-800 transition-colors cursor-pointer"
                        >
                            <span>Terbitkan Instruksi Wakil Presiden</span>
                        </button>
                    </div>
                </form>
            </div>

        </div>

        <!-- History of Instructions Issued by the Vice President -->
        <div class="pt-6 border-t border-slate-200 space-y-5">
            <div>
                <span class="text-xs font-mono uppercase tracking-wider text-slate-700 font-semibold">Arsip Sekretariat Wakil Presiden</span>
                <h2 class="mt-1 font-serif text-2xl font-medium text-slate-900">
                    Riwayat Instruksi &amp; Risalah Koordinasi Wakil Presiden
                </h2>
            </div>

            <div class="bg-white border border-slate-200 rounded-xl divide-y divide-slate-200 overflow-hidden">
                <?php if (empty($vicePresidentDecrees)): ?>
                    <div class="p-6 text-sm text-slate-500">Belum ada instruksi wakil presiden yang diterbitkan.</div>
                <?php else: ?>
                    <?php foreach ($vicePresidentDecrees as $decree): ?>
                        <div class="p-5 sm:p-6 flex flex-col sm:flex-row sm:items-start justify-between gap-4">
                            <div class="space-y-1.5 max-w-3xl">
                                <div class="flex flex-wrap items-center gap-2 text-xs font-mono">
                                    <span class="px-2 py-0.5 rounded bg-slate-100 text-slate-800 border border-slate-300 font-semibold">
                                        <?= htmlspecialchars($decree['document_type'], ENT_QUOTES, 'UTF-8') ?>
                                    </span>
                                    <span class="font-bold text-slate-900">
                                        <?= htmlspecialchars($decree['document_number'], ENT_QUOTES, 'UTF-8') ?>
                                    </span>
                                    <span class="text-slate-300">&bull;</span>
                                    <span class="text-slate-500">
                                        <?= htmlspecialchars($decree['permission_name'], ENT_QUOTES, 'UTF-8') ?>
                                    </span>
                                </div>
                                <h3 class="text-base font-bold text-slate-900">
                                    <?= htmlspecialchars($decree['title'], ENT_QUOTES, 'UTF-8') ?>
                                </h3>
                                <p class="text-sm text-slate-600 leading-relaxed">
                                    <?= htmlspecialchars($decree['summary_content'], ENT_QUOTES, 'UTF-8') ?>
                                </p>
                            </div>
                            <div class="flex sm:flex-col items-center sm:items-end justify-between gap-1 shrink-0">
                                <span class="px-2.5 py-0.5 rounded text-xs font-mono font-medium bg-emerald-50 text-emerald-800 border border-emerald-200">
                                    <?= htmlspecialchars($decree['status'], ENT_QUOTES, 'UTF-8') ?>
                                </span>
                                <span class="text-xs font-mono text-slate-400">
                                    <?= htmlspecialchars($decree['timestamp'], ENT_QUOTES, 'UTF-8') ?>
                                </span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

    </div>
</main>

<!-- Custom Confirmation Modal for Vice-Presidential Act -->
<div
    id="executiveActConfirmModal"
    class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs"
    role="dialog"
    aria-modal="true"
    aria-labelledby="execConfirmTitle"
>
    <div class="w-full max-w-md rounded-xl bg-white border border-slate-200 p-6 shadow-xl space-y-4">
        <div class="flex items-start gap-3">
            <div class="w-9 h-9 rounded-lg bg-slate-100 border border-slate-200 flex items-center justify-center text-slate-900 shrink-0">
                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                </svg>
            </div>
            <div>
                <h3 id="execConfirmTitle" class="text-base font-bold text-slate-900">
                    Konfirmasi Penerbitan Instruksi Wakil Presiden
                </h3>
                <p class="mt-1 text-xs text-slate-600 leading-relaxed">
                    Apakah Anda yakin hendak menerbitkan dan mengumumkan naskah <strong id="execConfirmDocPreview" class="text-slate-900 font-semibold"></strong> ke dalam Lembaran Negara dan Berita Nasional?
                </p>
            </div>
        </div>

        <div class="flex justify-end gap-2 pt-3 border-t border-slate-200">
            <button
                type="button"
                id="cancelExecActConfirmBtn"
                class="px-3.5 py-2 rounded-lg text-xs font-medium text-slate-600 hover:bg-slate-100 transition-colors cursor-pointer"
            >
                Periksa Kembali
            </button>
            <button
                type="button"
                id="proceedExecActConfirmBtn"
                class="px-4 py-2 rounded-lg text-xs font-semibold text-white bg-slate-900 hover:bg-slate-800 transition-colors cursor-pointer"
            >
                Ya, Terbitkan Sekarang
            </button>
        </div>
    </div>
</div>

<?php
require $componentsDir . 'footer.php';
?>

