<?php

declare(strict_types=1);

/**
 * Custom Citizen Registration & Career Track Selection Modal.
 * Enforces custom UI validation (`novalidate` on form, zero native HTML5 validation attributes,
 * custom confirmation modal, and inline field-level error containers).
 */
?>
<!-- Primary Citizen Registration Modal -->
<div
    id="citizenshipModal"
    class="fixed inset-0 z-50 hidden items-center justify-center p-4 sm:p-6 bg-slate-900/50 backdrop-blur-xs overflow-y-auto"
    role="dialog"
    aria-modal="true"
    aria-labelledby="citizenshipModalTitle"
>
    <div class="relative w-full max-w-xl rounded-xl bg-white border border-slate-200 shadow-xl overflow-hidden my-8">

        <!-- Modal Header -->
        <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between bg-slate-50/70">
            <div>
                <span class="text-[11px] font-mono uppercase tracking-wider text-crimson-700 font-semibold block">
                    Pendaftaran Kependudukan Nasional
                </span>
                <h2 id="citizenshipModalTitle" class="text-base font-bold text-slate-900">
                    Formulir Pembuatan E-KTP &amp; Jalur Pengabdian
                </h2>
            </div>

            <button
                type="button"
                data-close-citizenship-modal
                class="p-2 rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-200/60 transition-colors cursor-pointer"
                aria-label="Tutup Jendela Pendaftaran"
            >
                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>

        <!-- Form Container (Strictly uses novalidate and custom validation) -->
        <form id="citizenshipForm" class="p-6 space-y-4" novalidate>
            <!-- Global Form Error Banner -->
            <div id="citizenshipGlobalError" class="hidden p-3.5 rounded-lg bg-crimson-50 border border-crimson-100 text-xs text-crimson-700 flex items-start gap-2.5">
                <svg class="w-4 h-4 text-crimson-600 shrink-0 mt-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" y1="8" x2="12" y2="12"></line>
                    <line x1="12" y1="16" x2="12.01" y2="16"></line>
                </svg>
                <span id="citizenshipGlobalErrorText">Harap periksa kembali kolom isian yang ditandai merah di bawah ini.</span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <!-- Character Full Name -->
                <div>
                    <label for="character_name" class="block text-xs font-semibold text-slate-700 mb-1.5">
                        Nama Lengkap Karakter <span class="text-crimson-600">*</span>
                    </label>
                    <input
                        type="text"
                        id="character_name"
                        name="character_name"
                        placeholder="Dr. Arya Mahendra, S.H."
                        class="w-full px-3.5 py-2 rounded-lg bg-white border border-slate-300 text-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:border-slate-900 transition-colors"
                    >
                    <p id="err-character_name" class="hidden mt-1 text-xs text-crimson-600 font-medium"></p>
                </div>

                <!-- Account Username -->
                <div>
                    <label for="discord_handle" class="block text-xs font-semibold text-slate-700 mb-1.5">
                        Username Akun <span class="text-crimson-600">*</span>
                    </label>
                    <input
                        type="text"
                        id="discord_handle"
                        name="discord_handle"
                        autocomplete="username"
                        placeholder="aryamahendra"
                        class="w-full px-3.5 py-2 rounded-lg bg-white border border-slate-300 text-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:border-slate-900 transition-colors"
                    >
                    <p id="err-discord_handle" class="hidden mt-1 text-xs text-crimson-600 font-medium"></p>
                </div>
            </div>

            <!-- Account Password -->
            <div>
                <label for="account_password" class="block text-xs font-semibold text-slate-700 mb-1.5">
                    Kata Sandi Akun (Min. 6 Karakter) <span class="text-crimson-600">*</span>
                </label>
                <input
                    type="password"
                    id="account_password"
                    name="account_password"
                    autocomplete="new-password"
                    placeholder="Buat kata sandi untuk masuk ke portal"
                    class="w-full px-3.5 py-2 rounded-lg bg-white border border-slate-300 text-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:border-slate-900 transition-colors"
                >
                <p id="err-account_password" class="hidden mt-1 text-xs text-crimson-600 font-medium"></p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <!-- Career Track Selection -->
                <div>
                    <label for="career_track" class="block text-xs font-semibold text-slate-700 mb-1.5">
                        Jalur Pengabdian Awal <span class="text-crimson-600">*</span>
                    </label>
                    <select
                        id="career_track"
                        name="career_track"
                        class="w-full px-3.5 py-2 rounded-lg bg-white border border-slate-300 text-sm text-slate-900 focus:outline-none focus:border-slate-900 transition-colors"
                    >
                        <option value="">Pilih jalur karir...</option>
                        <option value="TRACK_WARGA_PENGUSAHA">Warga Sipil &amp; Pengusaha Swasta</option>
                        <option value="TRACK_KADER_PARTAI">Kader Partai Politik &amp; Caleg</option>
                        <option value="TRACK_CPNS_KEMENTERIAN">Birokrat ASN &amp; Staf Kementerian</option>
                        <option value="TRACK_AKADEMI_HANKAM">Taruna Akademi TNI / Polri</option>
                        <option value="TRACK_JURNALIS_PERS">Jurnalis Pers Nasional</option>
                    </select>
                    <p id="err-career_track" class="hidden mt-1 text-xs text-crimson-600 font-medium"></p>
                </div>

                <!-- Regional Domicile (Dapil) -->
                <div>
                    <label for="domicile" class="block text-xs font-semibold text-slate-700 mb-1.5">
                        Provinsi Domisili (Dapil) <span class="text-crimson-600">*</span>
                    </label>
                    <select
                        id="domicile"
                        name="domicile"
                        class="w-full px-3.5 py-2 rounded-lg bg-white border border-slate-300 text-sm text-slate-900 focus:outline-none focus:border-slate-900 transition-colors"
                    >
                        <option value="">Pilih provinsi domisili...</option>
                        <option value="DKI Jakarta — Pusat Pemerintahan">DKI Jakarta — Pusat Pemerintahan</option>
                        <option value="IKN Nusantara — Kalimantan Timur">IKN Nusantara — Kalimantan Timur</option>
                        <option value="Jawa Barat — Koridor Industri">Jawa Barat — Koridor Industri</option>
                        <option value="Jawa Timur — Poros Maritim Timur">Jawa Timur — Poros Maritim Timur</option>
                        <option value="Sumatera Utara — Gerbang Barat">Sumatera Utara — Gerbang Barat</option>
                        <option value="Sulawesi Selatan — Hub Indonesia Timur">Sulawesi Selatan — Hub Indonesia Timur</option>
                    </select>
                    <p id="err-domicile" class="hidden mt-1 text-xs text-crimson-600 font-medium"></p>
                </div>
            </div>

            <!-- Character Backstory -->
            <div>
                <div class="flex items-center justify-between mb-1.5">
                    <label for="character_backstory" class="block text-xs font-semibold text-slate-700">
                        Latar Belakang Singkat Karakter (Min. 25 Karakter) <span class="text-crimson-600">*</span>
                    </label>
                    <span id="backstoryCharCounter" class="text-xs font-mono text-slate-400">0 / 500</span>
                </div>
                <textarea
                    id="character_backstory"
                    name="character_backstory"
                    rows="3"
                    placeholder="Tuliskan latar belakang pendidikan atau profesi karakter yang akan Anda perankan..."
                    class="w-full px-3.5 py-2 rounded-lg bg-white border border-slate-300 text-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:border-slate-900 transition-colors"
                ></textarea>
                <p id="err-character_backstory" class="hidden mt-1 text-xs text-crimson-600 font-medium"></p>
            </div>

            <!-- Constitutional Oath Checkbox -->
            <div class="p-3.5 rounded-lg bg-slate-50 border border-slate-200">
                <label class="flex items-start gap-2.5 cursor-pointer">
                    <input
                        type="checkbox"
                        id="oath_accepted"
                        name="oath_accepted"
                        value="1"
                        class="mt-0.5 w-4 h-4 rounded border-slate-300 text-slate-900 focus:ring-slate-900"
                    >
                    <span class="text-xs text-slate-600 leading-relaxed">
                        Saya bersumpah akan mematuhi <strong class="text-slate-900">tata tertib Discord Indonesia Simulator</strong>, menghormati batas kewenangan setiap jabatan negara, serta menjaga etika berkomunitas.
                    </span>
                </label>
                <p id="err-oath_accepted" class="hidden mt-1.5 text-xs text-crimson-600 font-medium"></p>
            </div>

            <!-- Form Footer Actions -->
            <div class="pt-3 flex items-center justify-between gap-2.5 border-t border-slate-200">
                <button
                    type="button"
                    data-switch-to-login
                    class="text-xs font-semibold text-crimson-700 hover:underline cursor-pointer"
                >
                    Sudah terdaftar? Masuk
                </button>

                <div class="flex items-center gap-2">
                    <button
                        type="button"
                        data-close-citizenship-modal
                        class="px-4 py-2 rounded-lg text-sm font-medium text-slate-600 hover:text-slate-900 hover:bg-slate-100 transition-colors cursor-pointer"
                    >
                        Batal
                    </button>
                    <button
                        type="submit"
                        id="citizenshipSubmitBtn"
                        class="inline-flex items-center gap-2 px-5 py-2 rounded-lg text-sm font-semibold text-white bg-slate-900 hover:bg-slate-800 transition-colors cursor-pointer"
                    >
                        <span>Terbitkan E-KTP</span>
                    </button>
                </div>
            </div>
        </form>

        <!-- Digital E-KTP Issued Result View (Hidden until validated) -->
        <div id="citizenshipSuccessPanel" class="hidden p-6 space-y-5">
            <div class="p-3.5 rounded-lg bg-emerald-50 border border-emerald-200 flex items-center gap-2.5 text-emerald-800 text-xs font-medium">
                <svg class="w-4 h-4 text-emerald-600 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                    <polyline points="22 4 12 14.01 9 11.01"></polyline>
                </svg>
                <span id="citizenshipSuccessMsg">Data kependudukan Anda berhasil dicatat dan E-KTP telah diterbitkan.</span>
            </div>

            <!-- Clean E-KTP Card Preview -->
            <div class="rounded-xl bg-slate-50 border border-slate-200 p-5 space-y-4">
                <div class="flex items-center justify-between border-b border-slate-200 pb-3">
                    <div>
                        <span class="text-[10px] font-mono uppercase tracking-wider text-slate-500 block">DISCORD INDONESIA SIMULATOR</span>
                        <h3 class="text-sm font-bold text-slate-900">Kartu Identitas Kependudukan</h3>
                    </div>
                    <span id="cardNikRp" class="px-2.5 py-1 rounded bg-white text-slate-800 font-mono text-xs font-semibold border border-slate-200">
                        NIK: -
                    </span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                    <div>
                        <span class="text-slate-500 block">Nama Lengkap</span>
                        <strong id="cardCharName" class="text-sm text-slate-900 font-semibold mt-0.5 block">-</strong>
                    </div>
                    <div>
                        <span class="text-slate-500 block">Provinsi Domisili</span>
                        <strong id="cardDomicile" class="text-sm text-slate-800 font-medium mt-0.5 block">-</strong>
                    </div>
                    <div class="sm:col-span-2 pt-2 border-t border-slate-200/80">
                        <span class="text-slate-500 block">Kedudukan &amp; Jalur Pengabdian:</span>
                        <strong id="cardRbacRole" class="text-xs text-crimson-700 font-semibold mt-0.5 block">-</strong>
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-2.5">
                <button
                    type="button"
                    id="resetCitizenshipFormBtn"
                    class="px-4 py-2 rounded-lg text-xs font-medium text-slate-600 hover:bg-slate-100 border border-slate-200 transition-colors cursor-pointer"
                >
                    Daftarkan Karakter Lain
                </button>
                <button
                    type="button"
                    data-switch-to-login
                    class="px-5 py-2 rounded-lg text-xs font-semibold text-white bg-slate-900 hover:bg-slate-800 transition-colors cursor-pointer"
                >
                    Masuk Sekarang
                </button>
            </div>
        </div>

    </div>
</div>

<!-- Custom Action Confirmation Modal (Replaces forbidden native confirm() popup) -->
<div
    id="oathConfirmationModal"
    class="fixed inset-0 z-[60] hidden items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs"
    role="alertdialog"
    aria-modal="true"
    aria-labelledby="oathConfirmTitle"
>
    <div class="w-full max-w-md rounded-xl bg-white border border-slate-200 p-6 shadow-xl space-y-4">
        <h3 id="oathConfirmTitle" class="text-base font-bold text-slate-900">
            Konfirmasi Sumpah Kewarganegaraan
        </h3>

        <p class="text-sm text-slate-600 leading-relaxed">
            Apakah Anda yakin data kependudukan atas nama <strong id="confirmCharNamePreview" class="text-slate-900 font-semibold">-</strong> sudah benar untuk disahkan?
        </p>

        <div class="pt-3 border-t border-slate-200 flex items-center justify-end gap-2.5">
            <button
                type="button"
                id="cancelOathConfirmBtn"
                class="px-4 py-2 rounded-lg text-xs font-medium text-slate-600 hover:bg-slate-100 transition-colors cursor-pointer"
            >
                Periksa Kembali
            </button>
            <button
                type="button"
                id="proceedOathConfirmBtn"
                class="px-4 py-2 rounded-lg text-xs font-semibold text-white bg-crimson-700 hover:bg-crimson-800 transition-colors cursor-pointer"
            >
                Ya, Sahkan Pendaftaran
            </button>
        </div>
    </div>
</div>
