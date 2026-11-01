<?php

declare(strict_types=1);

/**
 * Exclusive Menu Presiden Republik Indonesia (`Views/components/president-menu.php`).
 * Rendered ONLY when the authenticated session belongs to `ROLE_PRESIDEN`.
 * Allows the President to issue Perpres, PP, Keppres, Perppu, and Nota Keuangan APBN.
 *
 * @var array<string, mixed>|null $authUser Authenticated user session
 * @var array<int, array<string, mixed>> $presidentPermissions Authorized permissions from MySQL
 */
if ($authUser === null || ($authUser['role_code'] ?? '') !== 'ROLE_PRESIDEN') {
    return;
}

$presName = htmlspecialchars((string) ($authUser['character_name'] ?? ''), ENT_QUOTES, 'UTF-8');
$presDecree = htmlspecialchars((string) ($authUser['inauguration_decree'] ?? ''), ENT_QUOTES, 'UTF-8');
?>
<section id="menu-presiden" class="py-14 bg-white border-b border-slate-200">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- Header Bar -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-8 border-b border-slate-200">
            <div>
                <div class="inline-flex items-center gap-2 text-xs font-mono uppercase tracking-wider text-crimson-700 font-semibold">
                    <span class="w-2 h-2 rounded-full bg-crimson-700" aria-hidden="true"></span>
                    <span>Menu Khusus Presiden Republik Indonesia</span>
                </div>
                <h2 class="mt-1.5 font-serif text-3xl font-medium text-slate-900">
                    Ruang Kerja Kepresidenan &mdash; Istana Merdeka
                </h2>
                <p class="mt-1 text-sm text-slate-600">
                    Pejabat Aktif: <strong class="text-slate-900 font-semibold"><?= $presName ?></strong> (<?= $presDecree ?>)
                </p>
            </div>

            <div class="flex items-center gap-2 text-xs font-mono text-slate-600 bg-slate-50 px-3.5 py-2 rounded-lg border border-slate-200">
                <span>Wewenang: Pasal 4 ayat (1), Pasal 5, Pasal 10–17 &amp; Pasal 22 UUD 1945</span>
            </div>
        </div>

        <!-- Presidential Task Cards & Regulation Issuance Form -->
        <div class="mt-8 grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

            <!-- Left Column: Presidential Constitutional Duties -->
            <div class="lg:col-span-5 space-y-4">
                <h3 class="text-xs font-mono uppercase tracking-wider text-slate-500 font-semibold">
                    Tugas &amp; Hak Prerogatif Presiden
                </h3>

                <div class="bg-paper border border-slate-200 rounded-xl divide-y divide-slate-200 text-sm">
                    <div class="p-4">
                        <span class="text-xs font-mono text-crimson-700 font-semibold">Pasal 4 ayat (1) &amp; Pasal 5 ayat (2) UUD 1945</span>
                        <h4 class="font-bold text-slate-900 mt-0.5">Penerbitan Perpres &amp; Peraturan Pemerintah</h4>
                        <p class="text-xs text-slate-600 mt-1 leading-relaxed">
                            Menetapkan Peraturan Presiden (Perpres) dan Peraturan Pemerintah (PP) untuk menjalankan Undang-Undang sebagaimana mestinya.
                        </p>
                    </div>

                    <div class="p-4">
                        <span class="text-xs font-mono text-crimson-700 font-semibold">Pasal 17 ayat (2) UUD 1945</span>
                        <h4 class="font-bold text-slate-900 mt-0.5">Keputusan Presiden (Keppres) &amp; Kabinet</h4>
                        <p class="text-xs text-slate-600 mt-1 leading-relaxed">
                            Hak prerogatif penuh untuk mengangkat, melantik, dan memberhentikan Menteri-Menteri Negara serta pejabat tinggi negara.
                        </p>
                    </div>

                    <div class="p-4">
                        <span class="text-xs font-mono text-crimson-700 font-semibold">Pasal 22 ayat (1) &amp; Pasal 23 UUD 1945</span>
                        <h4 class="font-bold text-slate-900 mt-0.5">Penetapan Perppu &amp; Nota Keuangan APBN</h4>
                        <p class="text-xs text-slate-600 mt-1 leading-relaxed">
                            Menetapkan Perppu dalam hal ihwal kegentingan yang memaksa serta mengajukan RUU APBN bersama DPR RI.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Right Column: Form Penerbitan Perpres / Keppres / PP / Perppu -->
            <div class="lg:col-span-7 bg-paper border border-slate-200 rounded-xl p-6">
                <h3 class="text-base font-bold text-slate-900">
                    Terbitkan Peraturan atau Keputusan Presiden Baru
                </h3>
                <p class="text-xs text-slate-500 mt-0.5 mb-5">
                    Naskah yang Anda sahkan akan langsung tercatat dan diumumkan pada Lembaran Berita Negara.
                </p>

                <form id="presidentialActForm" class="space-y-4" novalidate>
                    <div id="presActGlobalError" class="hidden p-3.5 rounded-lg bg-crimson-50 border border-crimson-100 text-xs text-crimson-700"></div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="pres_document_type" class="block text-xs font-semibold text-slate-700 mb-1.5">
                                Jenis Instrumen Hukum <span class="text-crimson-600">*</span>
                            </label>
                            <select
                                id="pres_document_type"
                                name="document_type"
                                class="w-full px-3.5 py-2 rounded-lg bg-white border border-slate-300 text-sm text-slate-900 focus:outline-none focus:border-slate-900"
                            >
                                <option value="">Pilih jenis regulasi...</option>
                                <option value="PERPRES">Peraturan Presiden (Perpres)</option>
                                <option value="KEPPRES">Keputusan Presiden (Keppres)</option>
                                <option value="PP">Peraturan Pemerintah (PP)</option>
                                <option value="PERPPU">Perppu (Kegentingan Memaksa)</option>
                                <option value="NOTA_KEUANGAN">Nota Keuangan &amp; RAPBN</option>
                            </select>
                            <p id="err-pres-document_type" class="hidden mt-1 text-xs text-crimson-600 font-medium"></p>
                        </div>

                        <div>
                            <label for="pres_document_number" class="block text-xs font-semibold text-slate-700 mb-1.5">
                                Nomor Registrasi Naskah <span class="text-crimson-600">*</span>
                            </label>
                            <input
                                type="text"
                                id="pres_document_number"
                                name="document_number"
                                placeholder="Contoh: PERPRES-18/2026"
                                class="w-full px-3.5 py-2 rounded-lg bg-white border border-slate-300 text-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:border-slate-900"
                            >
                            <p id="err-pres-document_number" class="hidden mt-1 text-xs text-crimson-600 font-medium"></p>
                        </div>
                    </div>

                    <div>
                        <label for="pres_permission_id" class="block text-xs font-semibold text-slate-700 mb-1.5">
                            Dasar Kewenangan Konstitusional (UUD 1945) <span class="text-crimson-600">*</span>
                        </label>
                        <select
                            id="pres_permission_id"
                            name="permission_id"
                            class="w-full px-3.5 py-2 rounded-lg bg-white border border-slate-300 text-sm text-slate-900 focus:outline-none focus:border-slate-900"
                        >
                            <option value="">Pilih pasal kewenangan Presiden...</option>
                            <?php foreach ($presidentPermissions as $perm): ?>
                                <option value="<?= (int) $perm['permission_id'] ?>">
                                    <?= htmlspecialchars((string) $perm['permission_name'], ENT_QUOTES, 'UTF-8') ?> — (<?= htmlspecialchars((string) $perm['legal_basis'], ENT_QUOTES, 'UTF-8') ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <p id="err-pres-permission_id" class="hidden mt-1 text-xs text-crimson-600 font-medium"></p>
                    </div>

                    <div>
                        <label for="pres_title" class="block text-xs font-semibold text-slate-700 mb-1.5">
                            Judul Peraturan / Keputusan Presiden <span class="text-crimson-600">*</span>
                        </label>
                        <input
                            type="text"
                            id="pres_title"
                            name="title"
                            placeholder="Contoh: Percepatan Pembangunan Infrastruktur Pangan Nasional"
                            class="w-full px-3.5 py-2 rounded-lg bg-white border border-slate-300 text-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:border-slate-900"
                        >
                        <p id="err-pres-title" class="hidden mt-1 text-xs text-crimson-600 font-medium"></p>
                    </div>

                    <div>
                        <label for="pres_summary_content" class="block text-xs font-semibold text-slate-700 mb-1.5">
                            Pokok Materi / Diktum Keputusan <span class="text-crimson-600">*</span>
                        </label>
                        <textarea
                            id="pres_summary_content"
                            name="summary_content"
                            rows="3"
                            placeholder="Uraikan isi pokok keputusan atau perintah kepada menteri terkait..."
                            class="w-full px-3.5 py-2 rounded-lg bg-white border border-slate-300 text-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:border-slate-900"
                        ></textarea>
                        <p id="err-pres-summary_content" class="hidden mt-1 text-xs text-crimson-600 font-medium"></p>
                    </div>

                    <div class="pt-2 flex justify-end">
                        <button
                            type="submit"
                            id="presActSubmitBtn"
                            class="inline-flex items-center gap-2 px-5 py-2.5 rounded-lg text-sm font-semibold text-white bg-crimson-700 hover:bg-crimson-800 transition-colors cursor-pointer"
                        >
                            <span>Sahkan &amp; Terbitkan Keputusan Presiden</span>
                        </button>
                    </div>
                </form>
            </div>

        </div>

    </div>
</section>

