<?php
// topnav.php
if (!isset($is_login))     { $is_login = isset($_SESSION['id_anggota']); }
if (!isset($current_page)) { $current_page = basename($_SERVER['PHP_SELF']); }
if (!isset($punya_hutang)) { $punya_hutang = false; }
?>
<style>
    .nav-icon {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 2px;
        padding: 0.3rem 0.5rem;
        border-radius: 0.8rem;
        color: #4C5372;
        opacity: 0.45;
        transition: all .2s ease;
        white-space: nowrap;
        flex-shrink: 0;
    }
    @media (min-width: 640px) {
        .nav-icon { padding: 0.4rem 0.8rem; border-radius: 1rem; }
    }
    .nav-icon:hover { opacity: 1; background: rgba(76,83,114,0.06); }
    .nav-icon-active { opacity: 1; background: rgba(76,83,114,0.1); }
    .nav-icon-label {
        display: none;
        font-size: 8px;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        margin-top: 1px;
    }
    @media (min-width: 640px) {
        .nav-icon-label { display: block; }
    }
    /* Sembunyikan scrollbar sepenuhnya di semua browser */
    .nav-scroll::-webkit-scrollbar { display: none; }
    .nav-scroll { scrollbar-width: none; -ms-overflow-style: none; }
</style>

<header class="sticky top-0 z-50 bg-white/85 backdrop-blur-md border-b border-[#4C5372]/10 w-full">
    <div class="max-w-6xl mx-auto px-3 sm:px-6 lg:px-8 h-16 sm:h-20 flex items-center justify-between gap-2">

        <!-- Logo -->
        <a href="<?= $is_login ? 'index.php' : 'katalog.php'; ?>" class="flex items-center shrink-0">
            <div class="bg-[#4C5372] text-white p-2 rounded-xl mr-0 sm:mr-3 font-black text-base sm:text-lg">H</div>
            <span class="hidden sm:inline text-lg sm:text-xl font-black tracking-tighter uppercase text-[#4C5372]">Harts</span>
        </a>

        <!-- Menu Navigasi -->
        <div class="nav-scroll flex items-center gap-1 sm:gap-2 overflow-x-auto">
            <?php if ($is_login): ?>
                <a href="index.php" title="Beranda" class="nav-icon <?= ($current_page == 'index.php') ? 'nav-icon-active' : ''; ?>">
                    <i data-lucide="layout-dashboard" class="w-5 h-5"></i>
                    <span class="nav-icon-label">Beranda</span>
                </a>
            <?php endif; ?>

            <a href="katalog.php" title="E-Katalog" class="nav-icon <?= ($current_page == 'katalog.php') ? 'nav-icon-active' : ''; ?>">
                <i data-lucide="book-open" class="w-5 h-5"></i>
                <span class="nav-icon-label">Katalog</span>
            </a>

            <?php if ($is_login): ?>
                <a href="pinjamanku.php" title="Pinjamanku" class="nav-icon <?= ($current_page == 'pinjamanku.php') ? 'nav-icon-active' : ''; ?>">
                    <i data-lucide="timer" class="w-5 h-5"></i>
                    <span class="nav-icon-label">Pinjam</span>
                </a>

                <a href="denda.php" title="Tagihan Denda" class="nav-icon relative <?= ($current_page == 'denda.php') ? 'nav-icon-active' : ''; ?>">
                    <i data-lucide="wallet" class="w-5 h-5"></i>
                    <span class="nav-icon-label">Denda</span>
                    <?php if ($punya_hutang): ?>
                        <span class="absolute top-1 right-1.5 w-2 h-2 bg-red-500 rounded-full animate-pulse"></span>
                    <?php endif; ?>
                </a>

                <a href="kartu_digital.php" title="Kartu Digital" class="nav-icon <?= ($current_page == 'kartu_digital.php') ? 'nav-icon-active' : ''; ?>">
                    <i data-lucide="vibrate" class="w-5 h-5"></i>
                    <span class="nav-icon-label">Kartu</span>
                </a>

                <span class="w-px h-6 bg-[#4C5372]/10 mx-1 shrink-0"></span>

                <a href="logout.php" title="Keluar" class="nav-icon text-red-500">
                    <i data-lucide="log-out" class="w-5 h-5"></i>
                    <span class="nav-icon-label">Keluar</span>
                </a>
            <?php else: ?>
                <span class="w-px h-6 bg-[#4C5372]/10 mx-1 shrink-0"></span>

                <a href="login.php" class="shrink-0 px-3 py-2 rounded-xl font-black text-[9px] sm:text-[11px] uppercase tracking-wider text-[#4C5372] hover:bg-[#4C5372]/5 transition-all">
                    Masuk
                </a>
                <a href="registrasi.php" class="shrink-0 px-3 py-2 rounded-xl font-black text-[9px] sm:text-[11px] uppercase tracking-wider bg-[#4C5372] text-white shadow-lg hover:opacity-90 transition-all">
                    Daftar
                </a>
            <?php endif; ?>
        </div>

    </div>
</header>