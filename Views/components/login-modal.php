<?php

declare(strict_types=1);

/**
 * Portal Login Modal Component (`Views/components/login-modal.php`).
 * Allows Pejabat Negara (Presiden, Wakil Presiden) and Warga Negara to sign in.
 * Enforces custom UI validation (`novalidate` on form, zero native browser popups).
 */
?>
<div
    id="loginModal"
    class="fixed inset-0 z-50 hidden items-center justify-center p-4 sm:p-6 bg-slate-900/50 backdrop-blur-xs overflow-y-auto"
    role="dialog"
    aria-modal="true"
    aria-labelledby="loginModalTitle"
>
    <div class="relative w-full max-w-md rounded-xl bg-white border border-slate-200 shadow-xl overflow-hidden my-8">

        <!-- Modal Header -->
        <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between bg-slate-50/70">
            <div>
                <span class="text-[11px] font-mono uppercase tracking-wider text-crimson-700 font-semibold block">
                    Portal Masuk Kenegaraan
                </span>
                <h2 id="loginModalTitle" class="text-base font-bold text-slate-900">
                    Masuk Akun Warga &amp; Pejabat Negara
                </h2>
            </div>

            <button
                type="button"
                data-close-login-modal
                class="p-2 rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-200/60 transition-colors cursor-pointer"
                aria-label="Tutup Jendela Masuk"
            >
                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>

        <!-- Login Form (Strictly uses novalidate) -->
        <form id="loginForm" class="p-6 space-y-4" novalidate>
            <div id="loginGlobalError" class="hidden p-3.5 rounded-lg bg-crimson-50 border border-crimson-100 text-xs text-crimson-700 flex items-start gap-2.5">
                <svg class="w-4 h-4 text-crimson-600 shrink-0 mt-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" y1="8" x2="12" y2="12"></line>
                    <line x1="12" y1="16" x2="12.01" y2="16"></line>
                </svg>
                <span id="loginGlobalErrorText">Identitas atau kata sandi tidak sesuai.</span>
            </div>

            <div>
                <label for="login_identifier" class="block text-xs font-semibold text-slate-700 mb-1.5">
                    Username atau Nomor Induk Kependudukan (NIK) <span class="text-crimson-600">*</span>
                </label>
                <input
                    type="text"
                    id="login_identifier"
                    name="login_identifier"
                    autocomplete="username"
                    placeholder="Masukkan username atau nomor NIK Anda"
                    class="w-full px-3.5 py-2 rounded-lg bg-white border border-slate-300 text-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:border-slate-900 transition-colors"
                >
                <p id="err-login_identifier" class="hidden mt-1 text-xs text-crimson-600 font-medium"></p>
            </div>

            <div>
                <label for="login_password" class="block text-xs font-semibold text-slate-700 mb-1.5">
                    Kata Sandi <span class="text-crimson-600">*</span>
                </label>
                <input
                    type="password"
                    id="login_password"
                    name="login_password"
                    autocomplete="current-password"
                    placeholder="Masukkan kata sandi akun"
                    class="w-full px-3.5 py-2 rounded-lg bg-white border border-slate-300 text-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:border-slate-900 transition-colors"
                >
                <p id="err-login_password" class="hidden mt-1 text-xs text-crimson-600 font-medium"></p>
            </div>

            <div class="pt-3 flex items-center justify-between border-t border-slate-200">
                <button
                    type="button"
                    data-switch-to-register
                    class="text-xs font-semibold text-crimson-700 hover:underline cursor-pointer"
                >
                    Belum punya E-KTP? Daftar
                </button>

                <div class="flex items-center gap-2">
                    <button
                        type="button"
                        data-close-login-modal
                        class="px-4 py-2 rounded-lg text-sm font-medium text-slate-600 hover:text-slate-900 hover:bg-slate-100 transition-colors cursor-pointer"
                    >
                        Batal
                    </button>
                    <button
                        type="submit"
                        id="loginSubmitBtn"
                        class="px-5 py-2 rounded-lg text-sm font-semibold text-white bg-slate-900 hover:bg-slate-800 transition-colors cursor-pointer"
                    >
                        Masuk
                    </button>
                </div>
            </div>
        </form>

        <!-- Authenticated Session Card (Displayed upon successful Login) -->
        <div id="loginSuccessPanel" class="hidden p-6 space-y-5">
            <div class="p-3.5 rounded-lg bg-emerald-50 border border-emerald-200 flex items-center gap-2.5 text-emerald-800 text-xs font-medium">
                <svg class="w-4 h-4 text-emerald-600 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                    <polyline points="22 4 12 14.01 9 11.01"></polyline>
                </svg>
                <span id="loginSuccessMsg">Otorisasi identitas berhasil diverifikasi.</span>
            </div>

            <div class="rounded-xl bg-slate-50 border border-slate-200 p-5 space-y-3 text-xs">
                <div class="flex items-center justify-between border-b border-slate-200 pb-3">
                    <div>
                        <span class="text-[10px] font-mono uppercase tracking-wider text-crimson-700 font-semibold block" id="authRoleTitle">-</span>
                        <h3 class="text-base font-bold text-slate-900 mt-0.5" id="authCharName">-</h3>
                    </div>
                    <span id="authNikRp" class="px-2.5 py-1 rounded bg-white text-slate-800 font-mono text-xs font-semibold border border-slate-200">
                        -
                    </span>
                </div>

                <div class="grid grid-cols-1 gap-2.5 pt-1">
                    <div>
                        <span class="text-slate-500 block">Dasar Kedudukan Konstitusional</span>
                        <strong id="authConstitutionalBasis" class="text-slate-800 font-medium mt-0.5 block">-</strong>
                    </div>
                    <div class="grid grid-cols-2 gap-2 pt-2 border-t border-slate-200/80">
                        <div>
                            <span class="text-slate-500 block">Ketetapan / Akta</span>
                            <strong id="authDecree" class="text-slate-800 font-medium mt-0.5 block">-</strong>
                        </div>
                        <div>
                            <span class="text-slate-500 block">Wilayah Kedudukan</span>
                            <strong id="authDomicile" class="text-slate-800 font-medium mt-0.5 block">-</strong>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-2.5">
                <button
                    type="button"
                    id="logoutSwitchAccountBtn"
                    class="px-4 py-2 rounded-lg text-xs font-medium text-slate-600 hover:bg-slate-100 border border-slate-200 transition-colors cursor-pointer"
                >
                    Ganti Akun
                </button>
                <button
                    type="button"
                    data-close-login-modal
                    class="px-5 py-2 rounded-lg text-xs font-semibold text-white bg-slate-900 hover:bg-slate-800 transition-colors cursor-pointer"
                >
                    Lanjutkan ke Portal
                </button>
            </div>
        </div>

    </div>
</div>

