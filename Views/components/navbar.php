<?php

declare(strict_types=1);

/**
 * Modern Clean Navigation Bar Component (`Views/components/navbar.php`).
 * Provides high-clarity navigation, strict whitespace prevention, refined typography,
 * and responsive controls for both public information and official executive workspaces.
 *
 * @var string $appName Application name
 * @var string $activePage Current active page (`beranda`, `presiden`, or `wakil-presiden`)
 * @var array<string, mixed> $telemetry Live national metrics from MySQL
 * @var array<string, mixed>|null $authUser Currently authenticated user session
 */
$currentPage = (string) ($activePage ?? 'beranda');
$authRoleCode = (string) ($authUser['role_code'] ?? '');
$authName = htmlspecialchars((string) ($authUser['character_name'] ?? ''), ENT_QUOTES, 'UTF-8');
$authRoleTitle = htmlspecialchars((string) ($authUser['role_title'] ?? ''), ENT_QUOTES, 'UTF-8');
$initialLetter = mb_substr($authName !== '' ? $authName : 'P', 0, 1);
?>
<header class="sticky top-0 z-40 bg-white/95 backdrop-blur-md border-b border-slate-200/90 shadow-xs">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between gap-4">

        <!-- Left: Brand Identity -->
        <a href="?page=beranda" class="flex items-center gap-3 shrink-0 group">
            <img src="Assets/image/Logo_DIS.png" alt="Logo Discord Indonesia Simulator" class="w-8 h-8 rounded-lg object-contain shrink-0 shadow-xs transition-transform group-hover:scale-105">
            <div class="flex flex-col">
                <span class="text-sm font-bold tracking-tight text-slate-900 group-hover:text-crimson-700 transition-colors whitespace-nowrap">
                    Discord Indonesia Simulator
                </span>
                <span class="hidden sm:block text-[10px] font-mono text-slate-400 tracking-wider uppercase">
                    Portal Informasi Nasional
                </span>
            </div>
        </a>

        <!-- Center: Desktop Navigation Links (Strictly whitespace-nowrap) -->
        <nav class="hidden lg:flex items-center gap-1 xl:gap-2 text-xs font-semibold text-slate-600" aria-label="Navigasi Utama">
            <a
                href="?page=beranda"
                class="px-3 py-1.5 rounded-lg whitespace-nowrap transition-colors <?= $currentPage === 'beranda' ? 'text-slate-900 bg-slate-100 font-bold' : 'hover:text-slate-900 hover:bg-slate-50' ?>"
            >
                Beranda
            </a>
            <a
                href="?page=beranda#berita-nasional"
                class="px-3 py-1.5 rounded-lg whitespace-nowrap hover:text-slate-900 hover:bg-slate-50 transition-colors"
            >
                Berita
            </a>
            <a
                href="?page=beranda#indikator-ekonomi"
                class="px-3 py-1.5 rounded-lg whitespace-nowrap hover:text-slate-900 hover:bg-slate-50 transition-colors"
            >
                Ekonomi
            </a>
            <a
                href="?page=beranda#komposisi-dpr"
                class="px-3 py-1.5 rounded-lg whitespace-nowrap hover:text-slate-900 hover:bg-slate-50 transition-colors"
            >
                DPR RI
            </a>
            <a
                href="?page=beranda#uu-aktif"
                class="px-3 py-1.5 rounded-lg whitespace-nowrap hover:text-slate-900 hover:bg-slate-50 transition-colors"
            >
                UU Aktif
            </a>
            <a
                href="?page=beranda#partai-politik"
                class="px-3 py-1.5 rounded-lg whitespace-nowrap hover:text-slate-900 hover:bg-slate-50 transition-colors"
            >
                Partai Politik
            </a>

            <!-- Dedicated Official Workspace Badge Button -->
            <?php if ($authRoleCode === 'ROLE_PRESIDEN'): ?>
                <a
                    href="?page=presiden"
                    class="ml-1 inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold whitespace-nowrap transition-all <?= $currentPage === 'presiden' ? 'bg-crimson-700 text-white shadow-xs' : 'bg-crimson-50 text-crimson-700 border border-crimson-200/80 hover:bg-crimson-100 hover:border-crimson-300' ?>"
                >
                    <span class="w-1.5 h-1.5 rounded-full <?= $currentPage === 'presiden' ? 'bg-white' : 'bg-crimson-600 animate-pulse' ?>" aria-hidden="true"></span>
                    <span>Menu Presiden</span>
                </a>
            <?php elseif ($authRoleCode === 'ROLE_WAKIL_PRESIDEN'): ?>
                <a
                    href="?page=wakil-presiden"
                    class="ml-1 inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold whitespace-nowrap transition-all <?= $currentPage === 'wakil-presiden' ? 'bg-slate-900 text-white shadow-xs' : 'bg-slate-100 text-slate-900 border border-slate-300 hover:bg-slate-200' ?>"
                >
                    <span class="w-1.5 h-1.5 rounded-full <?= $currentPage === 'wakil-presiden' ? 'bg-white' : 'bg-slate-700 animate-pulse' ?>" aria-hidden="true"></span>
                    <span>Menu Wapres</span>
                </a>
            <?php endif; ?>
        </nav>

        <!-- Right: Official Profile / Guest Auth Actions & Mobile Menu Toggle -->
        <div class="flex items-center gap-2 sm:gap-3 shrink-0">
            <?php if ($authUser !== null): ?>
                <!-- Clean Divider -->
                <div class="hidden sm:block h-5 w-px bg-slate-200" aria-hidden="true"></div>

                <!-- User Profile Chip -->
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-full bg-slate-100 border border-slate-200 flex items-center justify-center text-slate-800 font-mono text-xs font-bold shrink-0 shadow-2xs">
                        <?= $initialLetter ?>
                    </div>
                    <div class="hidden md:flex flex-col text-left">
                        <span class="text-xs font-bold text-slate-900 leading-tight truncate max-w-[160px]" title="<?= $authName ?>">
                            <?= $authName ?>
                        </span>
                        <span class="text-[10px] font-mono text-crimson-700 leading-tight truncate max-w-[160px]">
                            <?= $authRoleTitle ?>
                        </span>
                    </div>
                </div>

                <!-- Logout Button -->
                <button
                    type="button"
                    id="logoutBtn"
                    class="inline-flex items-center gap-1.5 px-2.5 sm:px-3 py-1.5 rounded-lg text-xs font-semibold text-slate-600 hover:text-crimson-700 hover:bg-crimson-50 border border-slate-200 hover:border-crimson-200 transition-colors cursor-pointer shrink-0"
                    title="Keluar dari akun kenegaraan"
                >
                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                        <polyline points="16 17 21 12 16 7"></polyline>
                        <line x1="21" y1="12" x2="9" y2="12"></line>
                    </svg>
                    <span class="hidden sm:inline">Keluar</span>
                </button>
            <?php else: ?>
                <button
                    type="button"
                    data-open-login-modal
                    class="inline-flex items-center justify-center px-3.5 py-1.5 rounded-lg text-xs font-semibold text-slate-700 hover:text-slate-900 hover:bg-slate-100 border border-slate-200 transition-colors cursor-pointer whitespace-nowrap"
                >
                    Masuk
                </button>

                <button
                    type="button"
                    data-open-citizenship-modal
                    class="inline-flex items-center justify-center px-3.5 py-1.5 rounded-lg text-xs font-semibold text-white bg-slate-900 hover:bg-slate-800 transition-colors cursor-pointer whitespace-nowrap"
                >
                    Daftar Warga
                </button>
            <?php endif; ?>

            <!-- Mobile Menu Toggle Button (Visible on screens < lg) -->
            <button
                type="button"
                id="mobileMenuToggleBtn"
                class="lg:hidden p-2 rounded-lg text-slate-500 hover:text-slate-800 hover:bg-slate-100 border border-slate-200 transition-colors cursor-pointer"
                aria-label="Buka Menu Navigasi"
                aria-expanded="false"
            >
                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <line x1="3" y1="12" x2="21" y2="12"></line>
                    <line x1="3" y1="6" x2="21" y2="6"></line>
                    <line x1="3" y1="18" x2="21" y2="18"></line>
                </svg>
            </button>
        </div>

    </div>

    <!-- Mobile Drawer Menu (Hidden by default, toggled via JS) -->
    <div id="mobileNavDrawer" class="hidden lg:hidden border-t border-slate-200 bg-white px-4 py-3 space-y-2">
        <nav class="flex flex-col space-y-1 text-xs font-semibold text-slate-700" aria-label="Navigasi Mobile">
            <a href="?page=beranda" class="px-3 py-2 rounded-lg <?= $currentPage === 'beranda' ? 'bg-slate-100 text-slate-900 font-bold' : 'hover:bg-slate-50' ?>">
                Beranda
            </a>
            <a href="?page=beranda#berita-nasional" class="px-3 py-2 rounded-lg hover:bg-slate-50">
                Berita Nasional
            </a>
            <a href="?page=beranda#indikator-ekonomi" class="px-3 py-2 rounded-lg hover:bg-slate-50">
                Indikator Ekonomi
            </a>
            <a href="?page=beranda#komposisi-dpr" class="px-3 py-2 rounded-lg hover:bg-slate-50">
                Komposisi Kursi DPR RI
            </a>
            <a href="?page=beranda#uu-aktif" class="px-3 py-2 rounded-lg hover:bg-slate-50">
                Undang-Undang &amp; Regulasi Aktif
            </a>
            <a href="?page=beranda#partai-politik" class="px-3 py-2 rounded-lg hover:bg-slate-50">
                Partai Politik &amp; Ideologi
            </a>

            <?php if ($authRoleCode === 'ROLE_PRESIDEN'): ?>
                <div class="pt-2 border-t border-slate-100">
                    <a href="?page=presiden" class="flex items-center gap-2 px-3 py-2 rounded-lg text-xs font-bold bg-crimson-50 text-crimson-700 border border-crimson-200">
                        <span class="w-2 h-2 rounded-full bg-crimson-600"></span>
                        <span>Ruang Kerja Presiden RI</span>
                    </a>
                </div>
            <?php elseif ($authRoleCode === 'ROLE_WAKIL_PRESIDEN'): ?>
                <div class="pt-2 border-t border-slate-100">
                    <a href="?page=wakil-presiden" class="flex items-center gap-2 px-3 py-2 rounded-lg text-xs font-bold bg-slate-100 text-slate-900 border border-slate-300">
                        <span class="w-2 h-2 rounded-full bg-slate-800"></span>
                        <span>Ruang Kerja Wakil Presiden RI</span>
                    </a>
                </div>
            <?php endif; ?>
        </nav>
    </div>
</header>
