<?php
session_start();
include '../../config/koneksi.php';

// Proteksi halaman admin
if (!isset($_SESSION['admin'])) {
    header("Location: ../../login.php");
    exit;
}

$current_page = 'anggota';
$admin_data = $_SESSION['admin'];
$admin_name = $admin_data['nama_petugas'] ?? "Admin";
$initial    = strtoupper(substr($admin_name, 0, 1));

// LOGIKA SELESAI (Aktifkan Akun)
if (isset($_GET['action']) && $_GET['action'] == 'selesai') {
    $id = $_GET['id'];
    mysqli_query($conn, "UPDATE anggota SET status_akun = 'aktif' WHERE id_anggota = '$id'");
    header("Location: index.php?page=validasi");
    exit;
}

// Logika Halaman & Query
$page = isset($_GET['page']) ? $_GET['page'] : 'daftar';
$keyword = "";

if ($page == 'validasi') {
    $query_sql = "SELECT * FROM anggota WHERE status_akun = 'baru' ORDER BY id_anggota DESC";
} else {
    $query_sql = "SELECT * FROM anggota WHERE status_akun = 'aktif' ORDER BY nama_anggota ASC";
    if (isset($_POST['cari'])) {
        $keyword = mysqli_real_escape_string($conn, $_POST['keyword']);
        $query_sql = "SELECT * FROM anggota WHERE 
                      (nama_anggota LIKE '%$keyword%' OR nis LIKE '%$keyword%') 
                      AND status_akun = 'aktif' 
                      ORDER BY nama_anggota ASC";
    }
}

$query = mysqli_query($conn, $query_sql);

// Fungsi pembantu untuk membuat URL WhatsApp yang valid
function generateWaUrl($nama, $no_tlp, $is_baru = false) {
    $clean_phone = preg_replace('/[^0-9]/', '', $no_tlp);
    
    if (substr($clean_phone, 0, 1) === '0') {
        $clean_phone = '62' . substr($clean_phone, 1);
    }
    
    if ($is_baru) {
        $pesan = "Halo *{$nama}*, pendaftaran akun kamu di Perpustakaan HARTS telah kami terima. Silakan datang ke perpustakaan untuk proses aktivasi akun ya. Terima kasih!";
    } else {
        $pesan = "Halo *{$nama}*, ada yang bisa kami bantu dari Perpustakaan HARTS? Simpan nomor ini untuk informasi seputar perpustakaan kamu ya.";
    }
    
    return "https://api.whatsapp.com/send?phone=" . $clean_phone . "&text=" . urlencode($pesan);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="theme-color" content="#9DA3A4">
    <title>Data Anggota - HARTS Admin</title>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --soft-blush: #FFDBDA;
            --old-rose: #DB7F8E;
            --pale-slate: #D5C5C8;
            --cool-steel: #9DA3A4;
            --taupe-grey: #604D53;
        }

        html, body { -webkit-text-size-adjust: 100%; text-size-adjust: 100%; }

        body { 
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; 
            background-color: var(--soft-blush); 
            color: var(--taupe-grey);
        }

        button, a { -webkit-tap-highlight-color: transparent; }

        aside#sidebar {
            padding-top: calc(1.5rem + env(safe-area-inset-top));
            padding-bottom: calc(1.5rem + env(safe-area-inset-bottom));
        }

        main { padding-bottom: calc(1rem + env(safe-area-inset-bottom)); }

        .table-scroll { -webkit-overflow-scrolling: touch; scrollbar-width: thin; }

        .anggota-card {
            background-color: #ffffff;
            border: 1px solid var(--pale-slate);
            box-shadow: 0 6px 16px -4px rgba(96, 77, 83, 0.06);
        }

        .sidebar-theme { 
            background-color: var(--cool-steel); 
            border-right: 1px solid rgba(96, 77, 83, 0.12); 
        }

        .nav-item {
            color: #ffffff;
            transition: all 0.2s ease-in-out;
        }

        .nav-item:hover {
            background-color: rgba(255, 255, 255, 0.2);
            color: #ffffff;
        }

        .nav-active { 
            background-color: #ffffff !important; 
            color: var(--taupe-grey) !important; 
            box-shadow: 0 4px 14px rgba(96, 77, 83, 0.12); 
            font-weight: 700;
        }

        .card-custom { 
            background-color: #ffffff; 
            border: 1px solid var(--pale-slate); 
            box-shadow: 0 10px 25px -5px rgba(96, 77, 83, 0.04);
        }

        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-thumb { background: var(--old-rose); border-radius: 10px; }
    </style>
</head>
<body class="flex flex-col lg:flex-row h-screen overflow-hidden" style="height: 100vh; height: 100dvh;">

    <!-- Overlay Sidebar untuk Layar Mobile -->
    <div id="sidebarOverlay" onclick="toggleSidebar()" class="fixed inset-0 bg-stone-900/50 z-40 hidden lg:hidden backdrop-blur-sm transition-opacity"></div>

    <!-- Sidebar Menu Responsive (Off-Canvas) -->
    <aside id="sidebar" class="fixed lg:static inset-y-0 left-0 w-72 h-full sidebar-theme p-6 flex flex-col shadow-lg lg:shadow-none shrink-0 z-50 transform -translate-x-full lg:translate-x-0 transition-transform duration-300 ease-in-out">
        <!-- Logo -->
        <div class="flex items-center justify-between mb-8 px-2">
            <div class="flex items-center">
                <div class="w-10 h-10 rounded-xl mr-3 shadow-sm flex items-center justify-center text-white shrink-0" style="background-color: var(--taupe-grey);">
                    <i class="fa-solid fa-book-bookmark text-lg"></i>
                </div>
                <div>
                    <h1 class="text-xl font-extrabold tracking-tight uppercase text-white leading-none">Harts</h1>
                    <span class="text-[10px] font-semibold tracking-wider text-white/80 uppercase">Library System</span>
                </div>
            </div>
            <!-- Tombol Close Sidebar Mobile -->
            <button onclick="toggleSidebar()" class="lg:hidden text-white/80 hover:text-white p-1">
                <i class="fa-solid fa-xmark text-xl"></i>
            </button>
        </div>
        
        <!-- User Badge -->
        <a href="../../profile.php" class="p-3.5 rounded-2xl mb-6 flex items-center transition-all group border border-white/20 bg-white/10 hover:bg-white/20">
            <div class="w-9 h-9 rounded-xl flex items-center justify-center font-bold text-sm mr-3 text-white transition-transform group-hover:scale-105 shadow-sm shrink-0" style="background-color: var(--old-rose);">
                <?= $initial; ?>
            </div>
            <div class="overflow-hidden">
                <p class="text-[10px] font-bold uppercase tracking-widest text-white/70">Petugas</p>
                <p class="text-sm font-bold leading-tight text-white truncate"><?= htmlspecialchars($admin_name); ?></p>
            </div>
        </a>

        <!-- Navigation Links -->
        <nav class="space-y-1 flex-1 overflow-y-auto pr-1">
            <p class="text-[10px] font-extrabold uppercase tracking-widest ml-3 mb-2 text-white/60">Utama</p>
            
            <a href="../../index.php" class="nav-item flex items-center px-4 py-2.5 rounded-xl text-sm font-medium">
                <i class="fa-solid fa-house w-6 text-center text-sm mr-2.5"></i> Dashboard
            </a>
            
            <p class="text-[10px] font-extrabold uppercase tracking-widest ml-3 mt-6 mb-2 text-white/60">Layanan & Presensi</p>
            
            <a href="../../presensi.php" class="nav-item flex items-center px-4 py-2.5 rounded-xl text-sm font-medium">
                <i class="fa-solid fa-qrcode w-6 text-center text-sm mr-2.5"></i> Scanner Presensi
            </a>
            
            <a href="../../log_kunjungan.php" class="nav-item flex items-center px-4 py-2.5 rounded-xl text-sm font-medium">
                <i class="fa-solid fa-clipboard-user w-6 text-center text-sm mr-2.5"></i> Log Kunjungan
            </a>

            <p class="text-[10px] font-extrabold uppercase tracking-widest ml-3 mt-6 mb-2 text-white/60">Keuangan</p>
            
            <a href="../../kas_denda.php" class="nav-item flex items-center px-4 py-2.5 rounded-xl text-sm font-medium">
                <i class="fa-solid fa-wallet w-6 text-center text-sm mr-2.5"></i> Kas Denda
            </a>

            <p class="text-[10px] font-extrabold uppercase tracking-widest ml-3 mt-6 mb-2 text-white/60">Katalog & Anggota</p>
            
            <a href="index.php?page=daftar" class="nav-item flex items-center px-4 py-2.5 rounded-xl text-sm font-medium <?= ($page == 'daftar') ? 'nav-active' : ''; ?>">
                <i class="fa-solid fa-users w-6 text-center text-sm mr-2.5"></i> Data Anggota
            </a>

            <a href="index.php?page=validasi" class="nav-item flex items-center px-4 py-2.5 rounded-xl text-sm font-medium <?= ($page == 'validasi') ? 'nav-active' : ''; ?>">
                <i class="fa-solid fa-user-check w-6 text-center text-sm mr-2.5"></i> Validasi Akun
            </a>
            
            <a href="../buku/index.php" class="nav-item flex items-center px-4 py-2.5 rounded-xl text-sm font-medium">
                <i class="fa-solid fa-book w-6 text-center text-sm mr-2.5"></i> Katalog Buku
            </a>

            <a href="../rak/rak.php" class="nav-item flex items-center px-4 py-2.5 rounded-xl text-sm font-medium">
                <i class="fa-solid fa-cubes w-6 text-center text-sm mr-2.5"></i> Data Rak
            </a>

            <p class="text-[10px] font-extrabold uppercase tracking-widest ml-3 mt-6 mb-2 text-white/60">Laporan</p>
            
            <a href="../../laporan.php" class="nav-item flex items-center px-4 py-2.5 rounded-xl text-sm font-medium">
                <i class="fa-solid fa-chart-pie w-6 text-center text-sm mr-2.5"></i> Laporan Utama
            </a>
        </nav>
        
        <!-- Logout -->
        <a href="../../logout.php" class="px-4 py-3 text-white/90 hover:text-white font-bold flex items-center hover:bg-white/10 rounded-xl mt-auto transition-colors text-sm">
            <i class="fa-solid fa-arrow-right-from-bracket mr-2.5 text-center w-6"></i> Keluar
        </a>
    </aside>

    <!-- Main Content Area -->
    <main class="flex-1 h-full overflow-y-auto p-4 sm:p-6 lg:p-10">
        <div class="max-w-6xl mx-auto">
            
            <?php if ($page == 'validasi') : ?>
                <!-- Header Validasi -->
                <div class="flex items-center justify-between gap-4 mb-6 lg:mb-8">
                    <div>
                        <h2 class="text-xl sm:text-2xl lg:text-3xl font-extrabold tracking-tight" style="color: var(--taupe-grey);">Validasi Akun Siswa</h2>
                        <p class="mt-0.5 sm:mt-1 text-xs sm:text-sm font-medium opacity-80" style="color: var(--taupe-grey);">Cocokkan token dengan siswa, lalu klik Selesai untuk mengaktifkan akun.</p>
                    </div>
                    <!-- Hamburger Button Mobile -->
                    <button onclick="toggleSidebar()" class="lg:hidden p-2.5 rounded-2xl bg-white shadow-sm border border-stone-200 text-stone-700 hover:bg-stone-50 shrink-0">
                        <i class="fa-solid fa-bars text-lg"></i>
                    </button>
                </div>

                <div class="space-y-4">
                    <?php if(mysqli_num_rows($query) > 0) : ?>
                        <?php while($row = mysqli_fetch_assoc($query)) : ?>
                        <div class="card-custom p-4 sm:p-6 rounded-3xl flex flex-col md:flex-row md:items-center justify-between gap-4 transition-all">
                            <div class="flex items-center gap-3.5 md:w-1/3">
                                <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-2xl flex items-center justify-center font-extrabold text-white text-base sm:text-lg shadow-sm shrink-0" style="background-color: var(--old-rose);">
                                    <?= strtoupper(substr($row['nama_anggota'], 0, 1)); ?>
                                </div>
                                <div class="overflow-hidden">
                                    <p class="font-bold text-sm sm:text-base leading-tight truncate" style="color: var(--taupe-grey);"><?= htmlspecialchars($row['nama_anggota']); ?></p>
                                    <p class="text-[10px] sm:text-[11px] font-semibold opacity-60 uppercase tracking-wider mt-0.5" style="color: var(--taupe-grey);"><?= $row['nis']; ?> • <?= $row['kelas']; ?></p>
                                </div>
                            </div>

                            <div class="flex-1 flex items-center gap-3">
                                <div class="flex-1 overflow-hidden">
                                    <p class="text-[9px] sm:text-[10px] font-extrabold uppercase tracking-widest opacity-60 mb-0.5" style="color: var(--taupe-grey);">Kontak & Alamat</p>
                                    <p class="text-xs font-bold" style="color: var(--taupe-grey);"><?= $row['no_tlp']; ?></p>
                                    <p class="text-[10px] sm:text-[11px] opacity-60 italic truncate" style="color: var(--taupe-grey);"><?= $row['alamat']; ?></p>
                                </div>
                                <a href="<?= generateWaUrl($row['nama_anggota'], $row['no_tlp'], true); ?>" target="_blank" rel="noopener" class="w-8 h-8 flex items-center justify-center bg-emerald-500 text-white rounded-xl shadow-sm hover:opacity-90 transition-opacity shrink-0" title="Hubungi via WhatsApp">
                                    <i class="fa-brands fa-whatsapp text-sm"></i>
                                </a>
                            </div>
                            
                            <div class="flex items-center justify-between md:justify-end gap-3 pt-3 md:pt-0 border-t md:border-t-0" style="border-color: var(--pale-slate);">
                                <div class="text-center px-4 py-1.5 rounded-2xl border shrink-0" style="background-color: var(--soft-blush); border-color: var(--pale-slate);">
                                    <p class="text-[8px] sm:text-[9px] font-extrabold uppercase tracking-widest mb-0.5" style="color: var(--taupe-grey);">Token Siswa</p>
                                    <p class="font-mono font-black text-base sm:text-xl tracking-widest" style="color: var(--taupe-grey);"><?= $row['token_regis']; ?></p>
                                </div>
                                
                                <a href="index.php?page=validasi&action=selesai&id=<?= $row['id_anggota']; ?>" 
                                   class="text-white px-5 py-2.5 sm:px-6 sm:py-3 rounded-2xl font-bold text-xs hover:opacity-90 transition-opacity shadow-sm uppercase tracking-wider text-center shrink-0" style="background-color: var(--taupe-grey);">
                                    Selesai
                                </a>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    <?php else : ?>
                        <div class="card-custom text-center py-16 rounded-3xl">
                            <i class="fa-solid fa-user-check text-3xl mb-3 block opacity-40" style="color: var(--taupe-grey);"></i>
                            <p class="text-xs font-bold uppercase tracking-widest opacity-60" style="color: var(--taupe-grey);">Semua akun baru sudah tervalidasi!</p>
                        </div>
                    <?php endif; ?>
                </div>

            <?php else : ?>
                <!-- Header Data Anggota -->
                <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4 mb-6 lg:mb-8">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-xl sm:text-2xl lg:text-3xl font-extrabold tracking-tight" style="color: var(--taupe-grey);">Data Anggota</h2>
                            <p class="mt-0.5 sm:mt-1 text-xs sm:text-sm font-medium opacity-80" style="color: var(--taupe-grey);">Daftar seluruh siswa aktif terdaftar di perpustakaan.</p>
                        </div>
                        <!-- Hamburger Button Mobile -->
                        <button onclick="toggleSidebar()" class="lg:hidden p-2.5 rounded-2xl bg-white shadow-sm border border-stone-200 text-stone-700 hover:bg-stone-50 shrink-0">
                            <i class="fa-solid fa-bars text-lg"></i>
                        </button>
                    </div>
                    
                    <a href="tambah.php" class="px-4 py-2.5 sm:px-5 sm:py-2.5 text-white font-bold text-xs sm:text-sm rounded-2xl shadow-sm hover:opacity-90 transition-all flex items-center justify-center gap-2 uppercase tracking-wider shrink-0" style="background-color: var(--old-rose);">
                        <i class="fa-solid fa-user-plus"></i> Tambah Anggota
                    </a>
                </div>

                <form action="" method="POST" class="mb-6 lg:mb-8">
                    <div class="relative">
                        <input type="text" name="keyword" value="<?= htmlspecialchars($keyword); ?>" placeholder="Cari nama atau NIS siswa..." class="w-full pl-4 sm:pl-5 pr-14 sm:pr-16 py-3 rounded-2xl card-custom outline-none font-semibold text-xs sm:text-sm transition-all focus:bg-stone-50" style="color: var(--taupe-grey);">
                        <button type="submit" name="cari" title="Cari" class="absolute right-1.5 top-1/2 -translate-y-1/2 w-9 h-9 sm:w-10 sm:h-10 flex items-center justify-center text-white rounded-xl shadow-sm hover:opacity-90 active:opacity-80 transition-opacity shrink-0" style="background-color: var(--taupe-grey);">
                            <i class="fa-solid fa-magnifying-glass text-xs sm:text-sm"></i>
                        </button>
                    </div>
                </form>

                <div class="card-custom p-4 sm:p-6 lg:p-8 rounded-3xl mb-8">

                    <!-- ================= MOBILE CARD VIEW (< sm) ================= -->
                    <div class="sm:hidden space-y-3">
                        <?php if(mysqli_num_rows($query) > 0) : ?>
                            <?php 
                            // Reset pointer query karena dipakai dua kali (mobile dan desktop)
                            mysqli_data_seek($query, 0);
                            while($row = mysqli_fetch_assoc($query)) : 
                            ?>
                            <div class="anggota-card rounded-2xl p-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl flex items-center justify-center font-bold text-white text-sm shadow-sm shrink-0" style="background-color: var(--old-rose);">
                                        <?= strtoupper(substr($row['nama_anggota'], 0, 1)); ?>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <p class="font-bold text-sm truncate" style="color: var(--taupe-grey);"><?= htmlspecialchars($row['nama_anggota']); ?></p>
                                        <p class="text-[10px] font-semibold opacity-60 uppercase mt-0.5" style="color: var(--taupe-grey);"><?= $row['nis']; ?> &middot; <?= $row['kelas']; ?></p>
                                    </div>
                                </div>

                                <div class="flex items-center justify-between mt-3 pt-3 border-t" style="border-color: var(--pale-slate);">
                                    <span class="px-2.5 py-1 rounded-xl text-xs font-bold border inline-block" style="background-color: var(--soft-blush); border-color: var(--pale-slate); color: var(--taupe-grey);">
                                        <?= $row['no_tlp']; ?>
                                    </span>
                                    <div class="flex justify-center gap-1.5">
                                        <a href="<?= generateWaUrl($row['nama_anggota'], $row['no_tlp'], false); ?>" target="_blank" rel="noopener" class="w-8 h-8 flex items-center justify-center text-white bg-emerald-500 rounded-xl shadow-sm active:opacity-80 transition-opacity shrink-0" title="Chat WhatsApp">
                                            <i class="fa-brands fa-whatsapp text-sm"></i>
                                        </a>
                                        <a href="edit.php?id=<?= $row['id_anggota']; ?>" class="w-8 h-8 flex items-center justify-center text-white rounded-xl shadow-sm active:opacity-80 transition-opacity shrink-0" style="background-color: var(--cool-steel);" title="Edit Data">
                                            <i class="fa-solid fa-pen-to-square text-xs"></i>
                                        </a>
                                        <a href="hapus.php?id=<?= $row['id_anggota']; ?>" class="btn-hapus w-8 h-8 flex items-center justify-center text-white rounded-xl shadow-sm active:opacity-80 transition-opacity cursor-pointer shrink-0" style="background-color: var(--old-rose);" title="Hapus Data">
                                            <i class="fa-solid fa-trash text-xs"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <?php endwhile; ?>
                        <?php else : ?>
                            <div class="text-center py-12 text-xs italic opacity-60" style="color: var(--taupe-grey);">
                                <i class="fa-solid fa-users-slash text-2xl mb-2 block" style="color: var(--old-rose);"></i> Tidak ada data anggota ditemukan.
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- ================= DESKTOP / TABLET TABLE VIEW (>= sm) ================= -->
                    <div class="hidden sm:block overflow-x-auto table-scroll">
                        <table class="w-full text-left border-collapse min-w-[500px]">
                            <thead>
                                <tr class="border-b text-[10px] sm:text-[11px] uppercase tracking-wider opacity-60" style="border-color: var(--pale-slate); color: var(--taupe-grey);">
                                    <th class="pb-3.5 font-extrabold">Siswa</th>
                                    <th class="pb-3.5 font-extrabold">Kelas</th>
                                    <th class="pb-3.5 font-extrabold">No. Telepon</th>
                                    <th class="pb-3.5 font-extrabold text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y" style="border-color: var(--pale-slate);">
                                <?php if(mysqli_num_rows($query) > 0) : ?>
                                    <?php 
                                    // Reset pointer query sekali lagi untuk tabel desktop
                                    mysqli_data_seek($query, 0);
                                    while($row = mysqli_fetch_assoc($query)) : 
                                    ?>
                                    <tr class="hover:bg-stone-50/50 transition-colors">
                                        <td class="py-3.5 sm:py-4 flex items-center">
                                            <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl mr-3 flex items-center justify-center font-bold text-white text-sm sm:text-base shadow-sm shrink-0" style="background-color: var(--old-rose);">
                                                <?= strtoupper(substr($row['nama_anggota'], 0, 1)); ?>
                                            </div>
                                            <div>
                                                <p class="font-bold text-xs sm:text-sm" style="color: var(--taupe-grey);"><?= htmlspecialchars($row['nama_anggota']); ?></p>
                                                <p class="text-[10px] sm:text-[11px] font-semibold opacity-60 uppercase mt-0.5" style="color: var(--taupe-grey);"><?= $row['nis']; ?></p>
                                            </div>
                                        </td>
                                        <td class="py-3.5 sm:py-4 font-bold text-xs sm:text-sm" style="color: var(--taupe-grey);"><?= $row['kelas']; ?></td>
                                        <td class="py-3.5 sm:py-4">
                                            <span class="px-2.5 py-1 rounded-xl text-xs font-bold border inline-block" style="background-color: var(--soft-blush); border-color: var(--pale-slate); color: var(--taupe-grey);">
                                                <?= $row['no_tlp']; ?>
                                            </span>
                                        </td>
                                        <td class="py-3.5 sm:py-4 text-center">
                                            <div class="flex justify-center gap-1.5 sm:gap-2">
                                                <!-- WhatsApp -->
                                                <a href="<?= generateWaUrl($row['nama_anggota'], $row['no_tlp'], false); ?>" target="_blank" rel="noopener" class="w-8 h-8 flex items-center justify-center text-white bg-emerald-500 rounded-xl shadow-sm hover:opacity-90 transition-opacity shrink-0" title="Chat WhatsApp">
                                                    <i class="fa-brands fa-whatsapp text-sm"></i>
                                                </a>

                                                <!-- Edit -->
                                                <a href="edit.php?id=<?= $row['id_anggota']; ?>" class="w-8 h-8 flex items-center justify-center text-white rounded-xl shadow-sm hover:opacity-90 transition-opacity shrink-0" style="background-color: var(--cool-steel);" title="Edit Data">
                                                    <i class="fa-solid fa-pen-to-square text-xs"></i>
                                                </a>

                                                <!-- Hapus -->
                                                <a href="hapus.php?id=<?= $row['id_anggota']; ?>" class="btn-hapus w-8 h-8 flex items-center justify-center text-white rounded-xl shadow-sm hover:opacity-90 transition-opacity cursor-pointer shrink-0" style="background-color: var(--old-rose);" title="Hapus Data">
                                                    <i class="fa-solid fa-trash text-xs"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                <?php else : ?>
                                    <tr>
                                        <td colspan="4" class="text-center py-12 text-xs lg:text-sm italic opacity-60" style="color: var(--taupe-grey);">
                                            <i class="fa-solid fa-users-slash text-2xl mb-2 block" style="color: var(--old-rose);"></i> Tidak ada data anggota ditemukan.
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                </div> <!-- Tutup card-custom -->
            <?php endif; ?>

        </div>
    </main>

    <script>
    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebarOverlay');
        
        sidebar.classList.toggle('-translate-x-full');
        overlay.classList.toggle('hidden');
    }

    document.querySelectorAll('.btn-hapus').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const url = this.getAttribute('href');

            Swal.fire({
                title: 'Hapus data ini?',
                text: "Data yang dihapus tidak bisa dikembalikan!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#DB7F8E', // Old Rose
                cancelButtonColor: '#9DA3A4',  // Cool Steel
                confirmButtonText: 'Ya, Hapus',
                cancelButtonText: 'Batal',
                background: '#ffffff',
                customClass: {
                    popup: 'rounded-3xl'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = url;
                }
            });
        });
    });
    </script>
</body>
</html>