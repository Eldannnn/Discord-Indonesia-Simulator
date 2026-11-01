<?php

declare(strict_types=1);

/**
 * Dedicated Presidential Workspace Page (`Views/president/index.php`).
 * Separate page (`?page=presiden`) accessible ONLY when logged in as `ROLE_PRESIDEN`.
 * Features an authentic official state document live preview on the left (matching authentic Indonesian presidential decrees)
 * and the regulation issuance form on the right with dynamic consideration items, interactive signature pad, and PDF export.
 *
 * @var string $appName
 * @var string $activePage
 * @var string $simulationEra
 * @var array<string, mixed> $authUser
 * @var array<string, mixed> $telemetry
 * @var array<int, array<string, mixed>> $presidentPermissions
 * @var array<int, array<string, string>> $presidentDecrees
 */
$componentsDir = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR;

$rawCharName = (string) ($authUser['character_name'] ?? 'Ir. H. Pratama Wijaya, M.P.A.');
$presName = htmlspecialchars($rawCharName, ENT_QUOTES, 'UTF-8');
$presNik = htmlspecialchars((string) ($authUser['nik_rp'] ?? '317101-PRES-0001'), ENT_QUOTES, 'UTF-8');
$presParty = htmlspecialchars((string) ($authUser['faction_party'] ?? 'Koalisi Nusantara Bersatu'), ENT_QUOTES, 'UTF-8');

// Extract base name without academic/honorific titles for fluid signature cursive
$cleanSigName = preg_replace('/^(Ir\.|Drs\.|Dr\.|H\.|Hj\.)\s+/i', '', $rawCharName);
$cleanSigName = preg_replace('/,.*$/', '', (string) $cleanSigName);
$cleanSigName = trim((string) $cleanSigName);
if ($cleanSigName === '') {
    $cleanSigName = 'Pratama Wijaya';
}
$signatureName = htmlspecialchars($cleanSigName, ENT_QUOTES, 'UTF-8');

$indonesianMonths = [
    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
    5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
    9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
];
$todayDateFormatted = date('j') . ' ' . ($indonesianMonths[(int) date('n')] ?? date('F')) . ' ' . date('Y');
$activeTab = (string) ($activeTab ?? ($_GET['tab'] ?? 'regulasi'));

require $componentsDir . 'navbar.php';
?>

<!-- HTML2PDF CDN for Direct Vector PDF Export -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js" integrity="sha512-GsLlZN/3F2ErC5ifS5QtgpiJtWd43JWSuIgh7mbzZ8zBps+dvLusV+eNQATqgA/HdeKFVgA5v3S/cIrLF7QnIg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

<main id="main-content" class="flex-1 py-10 sm:py-14">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">

        <!-- Page Header -->
        <div class="pb-6 border-b border-slate-200 flex flex-col md:flex-row md:items-center justify-between gap-4 print-hidden">
            <div>
                <div class="inline-flex items-center gap-2 text-xs font-mono uppercase tracking-wider text-crimson-700 font-semibold">
                    <span class="w-2 h-2 rounded-full bg-crimson-700" aria-hidden="true"></span>
                    <span>Ruang Kerja Kepresidenan &bull; Discord Indonesia Simulator</span>
                </div>
                <h1 class="mt-1.5 font-serif text-3xl sm:text-4xl font-medium text-slate-900">
                    <?= $activeTab === 'kabinet' ? 'Tata Kelola Kabinet &amp; Susunan Menteri RI' : 'Penerbitan Regulasi &amp; Keputusan Presiden RI' ?>
                </h1>
                <p class="mt-1 text-sm text-slate-600">
                    Pejabat Menjabat: <strong class="text-slate-900 font-semibold"><?= $presName ?></strong> &bull; NIK: <span class="font-mono"><?= $presNik ?></span> &bull; <?= $presParty ?>
                </p>
            </div>

            <a
                href="?page=beranda"
                class="self-start md:self-auto inline-flex items-center gap-2 px-4 py-2 rounded-lg text-xs font-semibold text-slate-700 bg-white hover:bg-slate-100 border border-slate-300 transition-colors"
            >
                <span>Kembali ke Beranda</span>
            </a>
        </div>

        <!-- Presidential Workspace Sub-Navigation Tabs: Regulasi vs Kabinet -->
        <div class="flex items-center gap-2.5 border-b border-slate-200 pb-4 print-hidden overflow-x-auto">
            <a
                href="?page=presiden&tab=regulasi"
                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold transition-all <?= $activeTab === 'regulasi' ? 'bg-crimson-700 text-white shadow-xs' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100 bg-white border border-slate-200' ?>"
            >
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                    <polyline points="14 2 14 8 20 8"></polyline>
                    <line x1="16" y1="13" x2="8" y2="13"></line>
                    <line x1="16" y1="17" x2="8" y2="17"></line>
                </svg>
                <span>Penerbitan Regulasi &amp; Naskah Presiden</span>
            </a>

            <a
                href="?page=presiden&tab=kabinet"
                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold transition-all <?= $activeTab === 'kabinet' ? 'bg-crimson-700 text-white shadow-xs' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100 bg-white border border-slate-200' ?>"
            >
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                    <circle cx="9" cy="7" r="4"></circle>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                </svg>
                <span>Susunan Kabinet &amp; Menteri</span>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-mono <?= $activeTab === 'kabinet' ? 'bg-white/20 text-white' : 'bg-crimson-50 text-crimson-700 border border-crimson-100' ?>">
                    Golongan I
                </span>
            </a>
        </div>

        <?php if ($activeTab === 'kabinet'): ?>
            <!-- Cabinet Management Tab Component -->
            <?php require __DIR__ . DIRECTORY_SEPARATOR . 'cabinet-tab.php'; ?>
        <?php else: ?>
        <!-- Main Workspace: Sequential Top-to-Bottom Flow -->
        <div class="space-y-10">

            <!-- 1. Top Section: Form Penerbitan Perpres / Keppres / PP / Perppu -->
            <div class="bg-white border border-slate-200 rounded-2xl p-6 sm:p-8 shadow-xs print-hidden">
                <div class="border-b border-slate-200 pb-4 mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                    <div>
                        <span class="text-[11px] font-mono uppercase tracking-wider text-crimson-700 font-semibold block">
                            Formulir Pengesahan Regulasi Eksekutif
                        </span>
                        <h2 class="text-xl font-bold text-slate-900 mt-1">
                            Terbitkan Peraturan atau Keputusan Presiden Baru
                        </h2>
                        <p class="text-xs text-slate-500 mt-0.5">
                            Sesuaikan pertimbangan konsideran, materi pasal, dan tanda tangan resmi. Hasil naskah langsung ditampilkan pada lembaran cetak A4 di bawah formulir ini.
                        </p>
                    </div>

                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-slate-100 border border-slate-200 text-xs font-mono text-slate-600 self-start sm:self-auto">
                        <span class="w-2 h-2 rounded-full bg-emerald-600"></span>
                        <span>Mode Pengesahan Langsung</span>
                    </span>
                </div>

                <form id="presidentialActForm" class="space-y-6" novalidate>
                    <div id="presActGlobalError" class="hidden p-3.5 rounded-lg bg-crimson-50 border border-crimson-100 text-xs text-crimson-700"></div>

                    <!-- Row 1: Instrument Type, Number, Permission Scope -->
                    <div class="grid grid-cols-1 sm:grid-cols-12 gap-4">
                        <div class="sm:col-span-4">
                            <label for="pres_document_type" class="block text-xs font-semibold text-slate-700 mb-1.5">
                                Jenis Instrumen Keputusan <span class="text-crimson-600">*</span>
                            </label>
                            <select
                                id="pres_document_type"
                                name="document_type"
                                class="w-full px-3.5 py-2 rounded-lg bg-white border border-slate-300 text-sm text-slate-900 focus:outline-none focus:border-slate-900"
                            >
                                <option value="PERPRES">Peraturan Presiden (Perpres)</option>
                                <option value="KEPPRES">Keputusan Presiden (Keppres)</option>
                                <option value="PP">Peraturan Pemerintah (PP)</option>
                                <option value="PERPPU">Perppu (Keadaan Mendesak)</option>
                                <option value="NOTA_KEUANGAN">Nota Keuangan &amp; RAPBN</option>
                            </select>
                            <p id="err-pres-document_type" class="hidden mt-1 text-xs text-crimson-600 font-medium"></p>
                        </div>

                        <div class="sm:col-span-4">
                            <label for="pres_document_number" class="block text-xs font-semibold text-slate-700 mb-1.5">
                                Nomor Registrasi Naskah <span class="text-crimson-600">*</span>
                            </label>
                            <input
                                type="text"
                                id="pres_document_number"
                                name="document_number"
                                value="PERPRES-18/2026"
                                placeholder="Contoh: PERPRES-18/2026"
                                class="w-full px-3.5 py-2 rounded-lg bg-white border border-slate-300 text-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:border-slate-900"
                            >
                            <p id="err-pres-document_number" class="hidden mt-1 text-xs text-crimson-600 font-medium"></p>
                        </div>

                        <div class="sm:col-span-4">
                            <label for="pres_permission_id" class="block text-xs font-semibold text-slate-700 mb-1.5">
                                Bidang Urusan Pemerintahan <span class="text-crimson-600">*</span>
                            </label>
                            <select
                                id="pres_permission_id"
                                name="permission_id"
                                class="w-full px-3.5 py-2 rounded-lg bg-white border border-slate-300 text-sm text-slate-900 focus:outline-none focus:border-slate-900"
                            >
                                <?php foreach ($presidentPermissions as $index => $perm): ?>
                                    <option value="<?= (int) $perm['permission_id'] ?>" <?= $index === 1 ? 'selected' : '' ?>>
                                        <?= htmlspecialchars((string) $perm['permission_name'], ENT_QUOTES, 'UTF-8') ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <p id="err-pres-permission_id" class="hidden mt-1 text-xs text-crimson-600 font-medium"></p>
                        </div>
                    </div>

                    <!-- Row 2: Subject Title -->
                    <div>
                        <label for="pres_title" class="block text-xs font-semibold text-slate-700 mb-1.5">
                            Judul Peraturan / Keputusan Presiden <span class="text-crimson-600">*</span>
                        </label>
                        <input
                            type="text"
                            id="pres_title"
                            name="title"
                            value="Percepatan Pembangunan Kawasan Industri Hilirisasi Nasional"
                            placeholder="Contoh: Percepatan Pembangunan Kawasan Industri Hilirisasi Nasional"
                            class="w-full px-3.5 py-2.5 rounded-lg bg-white border border-slate-300 text-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:border-slate-900"
                        >
                        <p id="err-pres-title" class="hidden mt-1 text-xs text-crimson-600 font-medium"></p>
                    </div>

                    <!-- Row 3: Section Konsideran Menimbang (Interactive Dynamic Item List a, b, c...) -->
                    <div class="p-4 sm:p-5 rounded-xl bg-slate-50 border border-slate-200 space-y-3.5">
                        <div class="flex items-center justify-between">
                            <div>
                                <label class="block text-xs font-bold text-slate-900">
                                    Konsideran Menimbang (Poin a, b, c...) <span class="text-crimson-600">*</span>
                                </label>
                                <span class="text-[11px] text-slate-500">Tambahkan atau sesuaikan butir-butir pertimbangan hukum dan fakta kebijakan.</span>
                            </div>
                            <button
                                type="button"
                                id="addMenimbangItemBtn"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-white hover:bg-slate-100 border border-slate-300 text-xs font-semibold text-slate-700 transition-colors cursor-pointer shadow-2xs"
                            >
                                <svg class="w-3.5 h-3.5 text-crimson-700" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                    <line x1="12" y1="5" x2="12" y2="19"></line>
                                    <line x1="5" y1="12" x2="19" y2="12"></line>
                                </svg>
                                <span>Tambah Poin Konsideran</span>
                            </button>
                        </div>

                        <!-- Dynamic Menimbang Items Container -->
                        <div id="menimbangInputsContainer" class="space-y-2.5">
                            <!-- Injected by JavaScript dynamically -->
                        </div>
                    </div>

                    <!-- Row 3b: Section Dasar Hukum Mengingat (Interactive Dynamic Item List 1, 2, 3...) -->
                    <div class="p-4 sm:p-5 rounded-xl bg-slate-50 border border-slate-200 space-y-3.5">
                        <div class="flex items-center justify-between">
                            <div>
                                <label class="block text-xs font-bold text-slate-900">
                                    Dasar Hukum Mengingat (Poin 1, 2, 3...) <span class="text-crimson-600">*</span>
                                </label>
                                <span class="text-[11px] text-slate-500">Tambahkan atau sesuaikan rujukan konstitusi, undang-undang, atau regulasi yang menjadi landasan wewenang.</span>
                            </div>
                            <button
                                type="button"
                                id="addMengingatItemBtn"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-white hover:bg-slate-100 border border-slate-300 text-xs font-semibold text-slate-700 transition-colors cursor-pointer shadow-2xs"
                            >
                                <svg class="w-3.5 h-3.5 text-crimson-700" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                    <line x1="12" y1="5" x2="12" y2="19"></line>
                                    <line x1="5" y1="12" x2="19" y2="12"></line>
                                </svg>
                                <span>Tambah Dasar Hukum</span>
                            </button>
                        </div>

                        <!-- Dynamic Mengingat Items Container -->
                        <div id="mengingatInputsContainer" class="space-y-2.5">
                            <!-- Injected by JavaScript dynamically -->
                        </div>
                    </div>

                    <!-- Row 4: Section Diktum Materi Muatan Peraturan (Pasal-Pasal Dinamis) -->
                    <div class="p-4 sm:p-5 rounded-xl bg-slate-50 border border-slate-200 space-y-3.5">
                        <div class="flex items-center justify-between">
                            <div>
                                <label class="block text-xs font-bold text-slate-900">
                                    Materi Muatan Peraturan (Pasal-Pasal) <span class="text-crimson-600">*</span>
                                </label>
                                <span class="text-[11px] text-slate-500">Dapat ditambah sebanyak yang diperlukan. Pasal 1, Pasal 2, Pasal 3, dst. akan tersusun rapi otomatis.</span>
                            </div>
                            <button
                                type="button"
                                id="addPasalBtn"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-white hover:bg-slate-100 border border-slate-300 text-xs font-semibold text-slate-700 transition-colors cursor-pointer shadow-2xs"
                            >
                                <svg class="w-3.5 h-3.5 text-crimson-700" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                    <line x1="12" y1="5" x2="12" y2="19"></line>
                                    <line x1="5" y1="12" x2="19" y2="12"></line>
                                </svg>
                                <span>Tambah Pasal Baru</span>
                            </button>
                        </div>

                        <!-- Hidden input to store compiled summary content for backend form validation and persistence -->
                        <input type="hidden" id="pres_summary_content" name="summary_content" value="">
                        <p id="err-pres_summary_content" class="hidden mt-1 text-xs text-crimson-600 font-medium"></p>

                        <!-- Dynamic Pasal Items Container -->
                        <div id="pasalInputsContainer" class="space-y-3">
                            <!-- Injected by JavaScript dynamically -->
                        </div>
                    </div>

                    <!-- Row 5: Section Pengesahan & Tanda Tangan Naskah (Font Goresan TTD) -->
                    <div class="p-4 sm:p-5 rounded-xl bg-slate-50 border border-slate-200 space-y-3.5">
                        <div class="flex items-center justify-between">
                            <div>
                                <label for="pres_signature_text" class="block text-xs font-bold text-slate-900">
                                    Tanda Tangan Digital Presiden (Font Goresan TTD) <span class="text-crimson-600">*</span>
                                </label>
                                <span class="text-[11px] text-slate-500">Ketik nama penandatangan untuk ditampilkan sebagai goresan tanda tangan kaligrafi pena resmi.</span>
                            </div>
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-white border border-slate-200 text-[11px] font-mono font-semibold text-slate-700 shadow-2xs">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                                <span>Font: Alex Brush / Great Vibes</span>
                            </span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-center">
                            <div class="sm:col-span-7">
                                <input
                                    type="text"
                                    id="pres_signature_text"
                                    name="signature_text"
                                    value="<?= $signatureName ?>"
                                    placeholder="Contoh: Pratama Wijaya"
                                    class="w-full px-3.5 py-2.5 rounded-lg bg-white border border-slate-300 text-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:border-slate-900"
                                >
                            </div>
                            <div class="sm:col-span-5 p-2.5 rounded-lg bg-white border border-slate-200 flex items-center justify-between px-3.5 shadow-2xs">
                                <span class="text-[10px] uppercase font-mono text-slate-400 font-semibold">Pratinjau Goresan TTD:</span>
                                <span id="formSigTextPreview" class="font-signature text-2xl text-slate-900 tracking-wide select-none block transform -rotate-2">
                                    <?= $signatureName ?>
                                </span>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs pt-1">
                            <div class="p-3 rounded-lg bg-white border border-slate-200 space-y-0.5">
                                <span class="text-[10px] uppercase font-mono text-slate-400 font-semibold block">Pejabat Pengesah</span>
                                <span class="font-bold text-slate-900 block"><?= $presName ?></span>
                                <span class="text-slate-500 text-[11px]">Presiden Republik Indonesia</span>
                            </div>
                            <div class="p-3 rounded-lg bg-white border border-slate-200 space-y-0.5">
                                <span class="text-[10px] uppercase font-mono text-slate-400 font-semibold block">Kota &amp; Tanggal Penetapan</span>
                                <span class="font-bold text-slate-900 block"><?= $todayDateFormatted ?></span>
                                <span class="text-slate-500 text-[11px]">Ditetapkan di Jakarta</span>
                            </div>
                        </div>
                    </div>

                    <!-- Row 6: Submit Button -->
                    <div class="pt-2 flex justify-end">
                        <button
                            type="submit"
                            id="presActSubmitBtn"
                            class="inline-flex items-center gap-2 px-6 py-2.5 rounded-lg text-sm font-semibold text-white bg-crimson-700 hover:bg-crimson-800 transition-colors cursor-pointer shadow-xs"
                        >
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                            </svg>
                            <span>Sahkan &amp; Terbitkan Keputusan Presiden</span>
                        </button>
                    </div>
                </form>
            </div>

            <!-- 2. Bottom Section: Pratinjau Lembaran Naskah Resmi (Tatanan Cetak Kertas A4 Luas & Proporsional) -->
            <div class="space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 px-1 print-hidden">
                    <div>
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-crimson-700" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                <polyline points="14 2 14 8 20 8"></polyline>
                                <line x1="16" y1="13" x2="8" y2="13"></line>
                                <line x1="16" y1="17" x2="8" y2="17"></line>
                                <polyline points="10 9 9 9 8 9"></polyline>
                            </svg>
                            <h2 class="text-base font-bold text-slate-900 font-sans">
                                Pratinjau Lembaran Naskah Resmi (Standar Kertas A4)
                            </h2>
                        </div>
                        <p class="text-xs text-slate-500 mt-0.5">
                            Ukuran proporsional naskah dinas 210 × 297 mm — siap cetak dan ekspor PDF resolusi tinggi tanpa garis pemisah halaman.
                        </p>
                    </div>

                    <!-- PDF & Print Actions -->
                    <div class="flex items-center gap-2.5">
                        <button
                            type="button"
                            id="downloadPdfBtn"
                            class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-xs font-semibold text-white bg-slate-900 hover:bg-slate-800 transition-colors cursor-pointer shadow-xs"
                            title="Unduh Naskah Dokumen sebagai File PDF Ukuran A4"
                        >
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                <polyline points="7 10 12 15 17 10"></polyline>
                                <line x1="12" y1="15" x2="12" y2="3"></line>
                            </svg>
                            <span>Unduh PDF (A4)</span>
                        </button>

                        <button
                            type="button"
                            id="printDocBtn"
                            class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-xs font-semibold text-slate-700 bg-white hover:bg-slate-100 border border-slate-300 transition-colors cursor-pointer shadow-xs"
                            title="Cetak Naskah Dokumen ke Printer Fisik atau Simpan PDF"
                        >
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <polyline points="6 9 6 2 18 2 18 9"></polyline>
                                <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                                <rect x="6" y="14" width="12" height="8"></rect>
                            </svg>
                            <span>Cetak Naskah</span>
                        </button>
                    </div>
                </div>

                <!-- Print Desk Viewer (Clean Background, Completely Borderless) -->
                <div class="bg-slate-100 rounded-2xl p-6 sm:p-10 lg:p-12 flex justify-center shadow-inner overflow-x-auto print:p-0 print:bg-transparent">

                    <!-- Authentic Official Indonesian Presidential Decree Sheet (Pure White Paper, 100% Borderless) -->
                    <div
                        id="officialDocumentSheet"
                        class="w-full max-w-[794px] min-h-[1050px] bg-white border-0 border-none outline-none shadow-xl p-10 sm:p-14 lg:p-16 font-serif text-slate-900 text-xs sm:text-[13px] leading-relaxed relative selection:bg-crimson-100 print:shadow-none print:p-0 print:m-0"
                        style="box-sizing: border-box; border: none !important; outline: none !important; page-break-inside: avoid; break-inside: avoid;"
                    >

                        <!-- 1. Authentic Presidential Black Emblem (Seal_Presiden_black.png) -->
                        <div class="text-center pb-4">
                            <img
                                src="Assets/image/Seal_Presiden_black.png"
                                onerror="this.src='public/assets/img/Seal_Presiden_black.png'"
                                alt="Seal Presiden Republik Indonesia"
                                class="h-16 w-auto mx-auto object-contain mb-2"
                            >
                            <p class="font-serif font-bold text-xs tracking-[0.25em] text-slate-900 uppercase">
                                PRESIDEN
                            </p>
                            <p class="font-serif font-bold text-xs tracking-[0.25em] text-slate-900 uppercase">
                                REPUBLIK INDONESIA
                            </p>
                        </div>

                        <!-- 2. Document Title, Number & Subject Block -->
                        <div class="text-center space-y-1.5 my-4">
                            <h3 id="docPreviewType" class="font-bold text-xs sm:text-sm tracking-wide uppercase text-slate-900">
                                PERATURAN PRESIDEN REPUBLIK INDONESIA
                            </h3>
                            <p id="docPreviewNumber" class="font-bold text-xs tracking-wider uppercase text-slate-900">
                                NOMOR 18 TAHUN 2026
                            </p>
                            <p class="font-bold text-xs tracking-widest text-slate-900 pt-2 uppercase">
                                TENTANG
                            </p>
                            <h4 id="docPreviewTitle" class="font-bold text-xs sm:text-sm tracking-normal uppercase text-slate-900 max-w-xl mx-auto leading-snug pt-0.5">
                                PERCEPATAN PEMBANGUNAN KAWASAN INDUSTRI HILIRISASI NASIONAL
                            </h4>
                        </div>

                        <!-- 3. Spiritual Invocation -->
                        <div class="text-center space-y-0.5 my-4">
                            <p class="font-bold text-xs tracking-wider text-slate-900">DENGAN RAHMAT TUHAN YANG MAHA ESA</p>
                            <p class="font-bold text-xs tracking-wider text-slate-900">PRESIDEN REPUBLIK INDONESIA,</p>
                        </div>

                        <!-- 4. Preamble Structure (Menimbang, Mengingat) with Authentic Alignment -->
                        <div class="space-y-3 pt-1">
                            <!-- Menimbang (Dynamically populated from form inputs) -->
                            <div class="flex items-start text-justify">
                                <div class="w-28 shrink-0 font-medium">Menimbang</div>
                                <div class="w-4 shrink-0 text-center">:</div>
                                <div id="docPreviewMenimbangList" class="flex-1 space-y-1.5">
                                    <div class="flex items-start gap-2">
                                        <span class="shrink-0 font-medium">a.</span>
                                        <span>bahwa untuk mewujudkan kemandirian ekonomi, kedaulatan industri strategis, serta perlindungan kepentingan umum nasional, diperlukan percepatan tata kelola pembangunan yang terpadu dan berkelanjutan;</span>
                                    </div>
                                    <div class="flex items-start gap-2">
                                        <span class="shrink-0 font-medium">b.</span>
                                        <span>bahwa penyelenggaraan kebijakan strategis memerlukan koordinasi lintas kementerian dan integrasi perizinan terpadu;</span>
                                    </div>
                                    <div class="flex items-start gap-2">
                                        <span class="shrink-0 font-medium">c.</span>
                                        <span>bahwa berdasarkan pertimbangan sebagaimana dimaksud dalam huruf a dan huruf b, perlu menetapkan <span id="docPreviewMenimbangC" class="font-semibold">Peraturan Presiden tentang Percepatan Pembangunan Kawasan Industri Hilirisasi Nasional;</span></span>
                                    </div>
                                </div>
                            </div>

                            <!-- Mengingat (Dynamically populated from form inputs) -->
                            <div class="flex items-start text-justify pt-1">
                                <div class="w-28 shrink-0 font-medium">Mengingat</div>
                                <div class="w-4 shrink-0 text-center">:</div>
                                <div id="docPreviewMengingatList" class="flex-1 space-y-1.5">
                                    <div class="flex items-start gap-2">
                                        <span class="shrink-0 font-medium">1.</span>
                                        <span>Pasal 4 ayat (1) Undang-Undang Dasar Negara Republik Indonesia Tahun 1945;</span>
                                    </div>
                                    <div class="flex items-start gap-2">
                                        <span class="shrink-0 font-medium">2.</span>
                                        <span>Undang-Undang Nomor 12 Tahun 2011 tentang Pembentukan Peraturan Perundang-undangan sebagaimana telah diubah terakhir dengan Undang-Undang Nomor 13 Tahun 2022;</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 5. Decision & Enactment -->
                        <div class="text-center font-bold tracking-widest my-4 text-xs sm:text-sm">
                            MEMUTUSKAN :
                        </div>

                        <div class="space-y-3.5">
                            <div class="flex items-start text-justify">
                                <div class="w-28 shrink-0 font-medium">Menetapkan</div>
                                <div class="w-4 shrink-0 text-center">:</div>
                                <div class="flex-1">
                                    <p id="docPreviewMenetapkan" class="font-bold uppercase leading-relaxed text-xs sm:text-sm">
                                        PERATURAN PRESIDEN TENTANG PERCEPATAN PEMBANGUNAN KAWASAN INDUSTRI HILIRISASI NASIONAL.
                                    </p>
                                    <!-- Dynamic Container for Pasal 1, Pasal 2, Pasal 3... -->
                                    <div id="docPreviewPasalContainer" class="mt-3 space-y-3">
                                        <!-- Injected by JavaScript dynamically -->
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 6. Closing Signature Block with Presidential Handwritten Script Font -->
                        <div class="mt-8 pt-4 flex flex-col items-end text-right text-xs sm:text-[13px]">
                            <div class="space-y-0.5">
                                <p>Ditetapkan di Jakarta</p>
                                <p>pada tanggal <span id="docPreviewDate"><?= $todayDateFormatted ?></span></p>
                                <p class="font-bold tracking-wider pt-2 text-slate-900">PRESIDEN REPUBLIK INDONESIA,</p>
                            </div>

                            <!-- Presidential Signature Script in Font TTD -->
                            <div class="py-3.5 pr-8 min-h-[64px] flex items-center justify-end">
                                <span id="docPreviewSignatureScript" class="font-signature text-3xl sm:text-4xl text-slate-900 font-normal tracking-wide transform -rotate-2 select-none">
                                    <?= $signatureName ?>
                                </span>
                            </div>

                            <p class="font-bold text-xs sm:text-[13px] text-slate-900 tracking-wide uppercase underline">
                                <?= $presName ?>
                            </p>
                        </div>

                    </div>
                </div>
            </div>

        </div>

        <!-- History of Decrees Issued by the President -->
        <div class="pt-6 border-t border-slate-200 space-y-5 print-hidden">
            <div>
                <span class="text-xs font-mono uppercase tracking-wider text-crimson-700 font-semibold">Arsip Istana Kepresidenan</span>
                <h2 class="mt-1 font-serif text-2xl font-medium text-slate-900">
                    Riwayat Peraturan &amp; Keputusan Presiden yang Diterbitkan
                </h2>
            </div>

            <div class="bg-white border border-slate-200 rounded-xl divide-y divide-slate-200 overflow-hidden">
                <?php if (empty($presidentDecrees)): ?>
                    <div class="p-6 text-sm text-slate-500">Belum ada keputusan presiden yang diterbitkan.</div>
                <?php else: ?>
                    <?php foreach ($presidentDecrees as $decree): ?>
                        <div class="p-5 sm:p-6 flex flex-col sm:flex-row sm:items-start justify-between gap-4">
                            <div class="space-y-1.5 max-w-3xl">
                                <div class="flex flex-wrap items-center gap-2 text-xs font-mono">
                                    <span class="px-2 py-0.5 rounded bg-crimson-50 text-crimson-700 border border-crimson-100 font-semibold">
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

        <?php endif; ?>

    </div>
</main>

<!-- Custom Confirmation Modal for Presidential Act -->
<div
    id="executiveActConfirmModal"
    class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs print-hidden"
    role="dialog"
    aria-modal="true"
    aria-labelledby="execConfirmTitle"
>
    <div class="w-full max-w-md rounded-xl bg-white border border-slate-200 p-6 shadow-xl space-y-4">
        <div class="flex items-start gap-3">
            <div class="w-9 h-9 rounded-lg bg-crimson-50 border border-crimson-100 flex items-center justify-center text-crimson-700 shrink-0">
                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                </svg>
            </div>
            <div>
                <h3 id="execConfirmTitle" class="text-base font-bold text-slate-900">
                    Konfirmasi Pengesahan Keputusan Presiden
                </h3>
                <p class="mt-1 text-xs text-slate-600 leading-relaxed">
                    Apakah Anda yakin hendak mengesahkan dan mengumumkan naskah <strong id="execConfirmDocPreview" class="text-slate-900 font-semibold"></strong> ke dalam Lembaran Negara dan Berita Nasional?
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
                class="px-4 py-2 rounded-lg text-xs font-semibold text-white bg-crimson-700 hover:bg-crimson-800 transition-colors cursor-pointer"
            >
                Ya, Sahkan Sekarang
            </button>
        </div>
    </div>
</div>

<?php
require $componentsDir . 'footer.php';
?>
