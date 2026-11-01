<?php

declare(strict_types=1);

/**
 * Exclusive Menu Wakil Presiden Republik Indonesia (`Views/components/vice-president-menu.php`).
 * Rendered ONLY when the authenticated session belongs to `ROLE_WAKIL_PRESIDEN`.
 * Distinct from the President's menu: focuses on Vice-Presidential Instructions,
 * Cross-Ministry Coordination, Bureaucratic Reform, Otsus, and Cabinet Supervision (Pasal 4 ayat 2 UUD 1945).
 *
 * @var array<string, mixed>|null $authUser Authenticated user session
 * @var array<int, array<string, mixed>> $vicePresidentPermissions Authorized permissions from MySQL
 */
if ($authUser === null || ($authUser['role_code'] ?? '') !== 'ROLE_WAKIL_PRESIDEN') {
    return;
}

$vpName = htmlspecialchars((string) ($authUser['character_name'] ?? ''), ENT_QUOTES, 'UTF-8');
$vpDecree = htmlspecialchars((string) ($authUser['inauguration_decree'] ?? ''), ENT_QUOTES, 'UTF-8');
?>
<section id="menu-wakil-presiden" class="py-14 bg-white border-b border-slate-200">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- Header Bar -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-8 border-b border-slate-200">
            <div>
                <div class="inline-flex items-center gap-2 text-xs font-mono uppercase tracking-wider text-slate-800 font-semibold">
                    <span class="w-2 h-2 rounded-full bg-slate-900" aria-hidden="true"></span>
                    <span>Menu Khusus Wakil Presiden Republik Indonesia</span>
                </div>
                <h2 class="mt-1.5 font-serif text-3xl font-medium text-slate-900">
                    Ruang Koordinasi &amp; Pengawasan Wakil Presiden
                </h2>
                <p class="mt-1 text-sm text-slate-600">
                    Pejabat Aktif: <strong class="text-slate-900 font-semibold"><?= $vpName ?></strong> (<?= $vpDecree ?>)
                </p>
            </div>

            <div class="flex items-center gap-2 text-xs font-mono text-slate-600 bg-slate-50 px-3.5 py-2 rounded-lg border border-slate-200">
                <span>Wewenang: Pasal 4 ayat (2) &amp; Pasal 8 ayat (1) UUD 1945</span>
            </div>
        </div>

        <!-- Vice-Presidential Task Cards & Instruction Issuance Form -->
        <div class="mt-8 grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

            <!-- Left Column: Vice-Presidential Constitutional Duties -->
            <div class="lg:col-span-5 space-y-4">
                <h3 class="text-xs font-mono uppercase tracking-wider text-slate-500 font-semibold">
                    Lingkup Tugas Konstitusional Wakil Presiden
                </h3>

                <div class="bg-paper border border-slate-200 rounded-xl divide-y divide-slate-200 text-sm">
                    <div class="p-4">
                        <span class="text-xs font-mono text-slate-700 font-semibold">Pasal 4 ayat (2) UUD 1945</span>
                        <h4 class="font-bold text-slate-900 mt-0.5">Koordinasi Lintas Kementerian &amp; Reformasi Birokrasi</h4>
                        <p class="text-xs text-slate-600 mt-1 leading-relaxed">
                            Mengoordinasikan reformasi birokrasi nasional, percepatan pengentasan kemiskinan, serta percepatan pembangunan daerah otonomi khusus.
                        </p>
                    </div>

                    <div class="p-4">
                        <span class="text-xs font-mono text-slate-700 font-semibold">Mandat Pengawasan Eksekutif</span>
                        <h4 class="font-bold text-slate-900 mt-0.5">Evaluasi &amp; Audit Kinerja Kabinet</h4>
                        <p class="text-xs text-slate-600 mt-1 leading-relaxed">
                            Memimpin evaluasi capaian program strategis nasional dan menerbitkan arahan tindak lanjut pengawasan lintas kementerian.
                        </p>
                    </div>

                    <div class="p-4">
                        <span class="text-xs font-mono text-slate-700 font-semibold">Pasal 8 ayat (1) UUD 1945</span>
                        <h4 class="font-bold text-slate-900 mt-0.5">Pimpinan Sidang Kabinet &amp; Suksesi Kepresidenan</h4>
                        <p class="text-xs text-slate-600 mt-1 leading-relaxed">
                            Memimpin sidang kabinet atas penugasan Presiden serta menjalankan tugas kepresidenan apabila Presiden berhalangan.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Right Column: Form Penerbitan Instruksi Wakil Presiden / Risalah Rakor / Audit -->
            <div class="lg:col-span-7 bg-paper border border-slate-200 rounded-xl p-6">
                <h3 class="text-base font-bold text-slate-900">
                    Terbitkan Instruksi Wakil Presiden &amp; Risalah Koordinasi
                </h3>
                <p class="text-xs text-slate-500 mt-0.5 mb-5">
                    Instruksi dan risalah koordinasi Wakil Presiden akan langsung dicatat dalam Lembaran Berita Negara.
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
                            Dasar Kewenangan Konstitusional Wakil Presiden <span class="text-crimson-600">*</span>
                        </label>
                        <select
                            id="vp_permission_id"
                            name="permission_id"
                            class="w-full px-3.5 py-2 rounded-lg bg-white border border-slate-300 text-sm text-slate-900 focus:outline-none focus:border-slate-900"
                        >
                            <option value="">Pilih dasar kewenangan Wakil Presiden...</option>
                            <?php foreach ($vicePresidentPermissions as $perm): ?>
                                <option value="<?= (int) $perm['permission_id'] ?>">
                                    <?= htmlspecialchars((string) $perm['permission_name'], ENT_QUOTES, 'UTF-8') ?> — (<?= htmlspecialchars((string) $perm['legal_basis'], ENT_QUOTES, 'UTF-8') ?>)
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

                    <div class="pt-2 flex justify-end">
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

    </div>
</section>

