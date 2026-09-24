<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit;
}
include 'config/koneksi.php';

$current_page = basename($_SERVER['PHP_SELF']);
$admin_data = $_SESSION['admin'];
$admin_name = $admin_data['nama_petugas'] ?? "Admin";
$initial    = strtoupper(substr($admin_name, 0, 1));

$tanggal_filter = isset($_GET['filter_tgl']) ? $_GET['filter_tgl'] : date('Y-m-d');

// 1. Total Kunjungan Unik (Prepared Statement)
$stmt_total = mysqli_prepare($conn, "SELECT COUNT(DISTINCT id_anggota) as total FROM kunjungan WHERE DATE(waktu_masuk) = ?");
mysqli_stmt_bind_param($stmt_total, "s", $tanggal_filter);
mysqli_stmt_execute($stmt_total);
$res_total = mysqli_stmt_get_result($stmt_total);
$row_total = mysqli_fetch_assoc($res_total);
$total_masuk = $row_total['total'] ?? 0;
mysqli_stmt_close($stmt_total);

// 2. Sedang Di Dalam (Prepared Statement)
$stmt_dalam = mysqli_prepare($conn, "SELECT COUNT(id_kunjungan) as total FROM kunjungan WHERE waktu_keluar IS NULL AND DATE(waktu_masuk) = ?");
mysqli_stmt_bind_param($stmt_dalam, "s", $tanggal_filter);
mysqli_stmt_execute($stmt_dalam);
$res_dalam = mysqli_stmt_get_result($stmt_dalam);
$row_dalam = mysqli_fetch_assoc($res_dalam);
$sedang_diperpus = $row_dalam['total'] ?? 0;
mysqli_stmt_close($stmt_dalam);

// Ambil data list kunjungan ke array untuk dukungan tampilan mobile (Card) & desktop (Table)
$data_kunjungan = [];
$stmt_list = mysqli_prepare($conn, "SELECT kunjungan.*, anggota.nama_anggota 
                                    FROM kunjungan 
                                    JOIN anggota ON kunjungan.id_anggota = anggota.id_anggota 
                                    WHERE DATE(waktu_masuk) = ?
                                    ORDER BY waktu_masuk DESC");
mysqli_stmt_bind_param($stmt_list, "s", $tanggal_filter);
mysqli_stmt_execute($stmt_list);
$query_list = mysqli_stmt_get_result($stmt_list);
if ($query_list) {
    while ($row_l = mysqli_fetch_assoc($query_list)) {
        $data_kunjungan[] = $row_l;
    }
}
mysqli_stmt_close($stmt_list);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log Kunjungan - HARTS Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --soft-blush: #FFDBDA;
            --old-rose: #DB7F8E;
            --pale-slate: #D5C5C8;
            --cool-steel: #9DA3A4;
            --taupe-grey: #604D53;
        }

        body { 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            background-color: var(--soft-blush); 
            color: var(--taupe-grey);
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

        .stat-card {
            background-color: #ffffff;
            border: 1px solid var(--pale-slate);
            transition: all 0.2s ease;
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
<body class="flex flex-col lg:flex-row h-screen overflow-hidden">

    <!-- Overlay Sidebar untuk Layar Mobile -->
    <div id="sidebarOverlay" onclick="toggleSidebar()" class="fixed inset-0 bg-stone-900/50 z-40 hidden lg:hidden backdrop-blur-sm transition-opacity"></div>

    <!-- Sidebar Menu Responsive (Off-Canvas) -->
    <aside id="sidebar" class="fixed lg:static inset-y-0 left-0 w-72 h-full sidebar-theme p-6 flex flex-col shadow-lg lg:shadow-none shrink-0 z-50 transform -translate-x-full lg:translate-x-0 transition-transform duration-300 ease-in-out">
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
        <a href="profile.php" class="p-3.5 rounded-2xl mb-6 flex items-center transition-all group border border-white/20 bg-white/10 hover:bg-white/20">
            <div class="w-9 h-9 rounded-xl flex items-center justify-center font-bold text-sm mr-3 text-white transition-transform group-hover:scale-105 shadow-sm shrink-0" style="background-color: var(--old-rose);">
                <?= $initial; ?>
            </div>
            <div class="overflow-hidden">
                <p class="text-[10px] font-bold uppercase tracking-widest text-white/70">Petugas</p>
                <p class="text-sm font-bold leading-tight text-white truncate"><?= htmlspecialchars($admin_name); ?></p>
            </div>
        </a>

        <!-- Navigation Link -->
        <nav class="space-y-1 flex-1 overflow-y-auto pr-1">
            <p class="text-[10px] font-extrabold uppercase tracking-widest ml-3 mb-2 text-white/60">Utama</p>
            
            <a href="index.php" class="nav-item flex items-center px-4 py-2.5 rounded-xl text-sm font-medium <?= ($current_page == 'index.php') ? 'nav-active' : ''; ?>">
                <i class="fa-solid fa-house w-6 text-center text-sm mr-2.5"></i> Dashboard
            </a>
            
            <p class="text-[10px] font-extrabold uppercase tracking-widest ml-3 mt-6 mb-2 text-white/60">Layanan & Presensi</p>
            
            <a href="presensi.php" class="nav-item flex items-center px-4 py-2.5 rounded-xl text-sm font-medium <?= ($current_page == 'presensi.php') ? 'nav-active' : ''; ?>">
                <i class="fa-solid fa-qrcode w-6 text-center text-sm mr-2.5"></i> Scanner Presensi
            </a>
            
            <a href="log_kunjungan.php" class="nav-item flex items-center px-4 py-2.5 rounded-xl text-sm font-medium <?= ($current_page == 'log_kunjungan.php') ? 'nav-active' : ''; ?>">
                <i class="fa-solid fa-clipboard-user w-6 text-center text-sm mr-2.5"></i> Log Kunjungan
            </a>

            <p class="text-[10px] font-extrabold uppercase tracking-widest ml-3 mt-6 mb-2 text-white/60">Keuangan</p>
            
            <a href="kas_denda.php" class="nav-item flex items-center px-4 py-2.5 rounded-xl text-sm font-medium <?= ($current_page == 'kas_denda.php') ? 'nav-active' : ''; ?>">
                <i class="fa-solid fa-wallet w-6 text-center text-sm mr-2.5"></i> Kas Denda
            </a>

            <p class="text-[10px] font-extrabold uppercase tracking-widest ml-3 mt-6 mb-2 text-white/60">Katalog & Anggota</p>
            
            <a href="modules/anggota/index.php?page=daftar" class="nav-item flex items-center px-4 py-2.5 rounded-xl text-sm font-medium <?= (isset($_GET['page']) && $_GET['page'] == 'daftar') ? 'nav-active' : ''; ?>">
                <i class="fa-solid fa-users w-6 text-center text-sm mr-2.5"></i> Data Anggota
            </a>

            <a href="modules/anggota/index.php?page=validasi" class="nav-item flex items-center px-4 py-2.5 rounded-xl text-sm font-medium <?= (isset($_GET['page']) && $_GET['page'] == 'validasi') ? 'nav-active' : ''; ?>">
                <i class="fa-solid fa-user-check w-6 text-center text-sm mr-2.5"></i> Validasi Akun
            </a>
            
            <a href="modules/buku/index.php" class="nav-item flex items-center px-4 py-2.5 rounded-xl text-sm font-medium <?= (strpos($_SERVER['REQUEST_URI'], 'buku') !== false) ? 'nav-active' : ''; ?>">
                <i class="fa-solid fa-book w-6 text-center text-sm mr-2.5"></i> Katalog Buku
            </a>

            <a href="modules/rak/rak.php" class="nav-item flex items-center px-4 py-2.5 rounded-xl text-sm font-medium <?= (strpos($_SERVER['REQUEST_URI'], 'rak') !== false) ? 'nav-active' : ''; ?>">
                <i class="fa-solid fa-cubes w-6 text-center text-sm mr-2.5"></i> Data Rak
            </a>

            <p class="text-[10px] font-extrabold uppercase tracking-widest ml-3 mt-6 mb-2 text-white/60">Laporan</p>
            
            <a href="laporan.php" class="nav-item flex items-center px-4 py-2.5 rounded-xl text-sm font-medium <?= ($current_page == 'laporan.php') ? 'nav-active' : ''; ?>">
                <i class="fa-solid fa-chart-pie w-6 text-center text-sm mr-2.5"></i> Laporan Utama
            </a>
        </nav>
        
        <!-- Logout -->
        <a href="logout.php" class="px-4 py-3 text-white/90 hover:text-white font-bold flex items-center hover:bg-white/10 rounded-xl mt-auto transition-colors text-sm">
            <i class="fa-solid fa-arrow-right-from-bracket mr-2.5 text-center w-6"></i> Keluar
        </a>
    </aside>

    <!-- Main Content Area -->
    <main class="flex-1 h-full overflow-y-auto p-4 sm:p-6 lg:p-10"> 
        <div class="max-w-6xl mx-auto">
            
            <!-- Header Ringkas & Hamburger Trigger -->
            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4 mb-6 lg:mb-8">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-xl sm:text-2xl lg:text-3xl font-extrabold tracking-tight" style="color: var(--taupe-grey);">Log Kunjungan</h2>
                        <p class="mt-0.5 sm:mt-1 text-xs sm:text-sm font-medium opacity-80" style="color: var(--taupe-grey);">Pantau lalu lintas kehadiran siswa secara real-time.</p>
                    </div>
                    <!-- Hamburger Button untuk Layar Kecil -->
                    <button onclick="toggleSidebar()" class="lg:hidden p-2.5 rounded-2xl bg-white shadow-sm border border-stone-200 text-stone-700 hover:bg-stone-50 shrink-0">
                        <i class="fa-solid fa-bars text-lg"></i>
                    </button>
                </div>
                
                <div class="flex items-center gap-2 self-start sm:self-auto shrink-0">
                    <form method="GET" class="bg-white px-3 sm:px-4 py-2 rounded-2xl border shadow-sm flex items-center" style="border-color: var(--pale-slate);">
                        <i class="fa-regular fa-calendar text-sm mr-2.5 opacity-70" style="color: var(--taupe-grey);"></i>
                        <input type="date" name="filter_tgl" 
                               value="<?= htmlspecialchars($tanggal_filter); ?>" 
                               onchange="this.form.submit()"
                               class="bg-transparent border-none outline-none font-bold text-xs sm:text-sm cursor-pointer" style="color: var(--taupe-grey);">
                    </form>
                    <?php if(isset($_GET['filter_tgl'])): ?>
                        <a href="log_kunjungan.php" class="bg-white p-2.5 rounded-2xl border shadow-sm hover:bg-stone-50 transition-all text-xs flex items-center justify-center w-10 h-10" style="border-color: var(--pale-slate); color: var(--taupe-grey);" title="Reset Filter">
                            <i class="fa-solid fa-rotate-right"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Stats Box -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6 sm:mb-8">
                <div class="stat-card p-4 sm:p-5 rounded-2xl flex items-center justify-between">
                    <div>
                        <p class="text-[10px] sm:text-[11px] font-bold uppercase tracking-wider opacity-70" style="color: var(--taupe-grey);">Total Kunjungan</p>
                        <h3 class="text-xl sm:text-2xl lg:text-3xl font-extrabold mt-1" style="color: var(--taupe-grey);"><?= $total_masuk; ?> <span class="text-xs font-semibold opacity-60">Siswa</span></h3>
                    </div>
                    <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl flex items-center justify-center text-white text-base shadow-sm shrink-0" style="background-color: var(--cool-steel);">
                        <i class="fa-solid fa-users"></i>
                    </div>
                </div>
                
                <div class="stat-card p-4 sm:p-5 rounded-2xl flex items-center justify-between">
                    <div>
                        <p class="text-[10px] sm:text-[11px] font-bold uppercase tracking-wider opacity-70" style="color: var(--taupe-grey);">Sedang Di Dalam</p>
                        <h3 class="text-xl sm:text-2xl lg:text-3xl font-extrabold mt-1" style="color: var(--old-rose);"><?= $sedang_diperpus; ?> <span class="text-xs font-semibold opacity-60">Siswa</span></h3>
                    </div>
                    <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl flex items-center justify-center text-white text-base shadow-sm shrink-0" style="background-color: var(--old-rose);">
                        <i class="fa-solid fa-person-shelter"></i>
                    </div>
                </div>
            </div>

            <!-- Card Area Riwayat Kunjungan -->
            <div class="card-custom p-4 sm:p-6 lg:p-8 rounded-3xl mb-8">
                <div class="flex items-center mb-4 sm:mb-6">
                    <div class="w-1.5 h-5 rounded-full mr-3 shrink-0" style="background-color: var(--old-rose);"></div>
                    <h3 class="text-base sm:text-lg font-bold tracking-tight" style="color: var(--taupe-grey);">Riwayat Kunjungan Hari Ini</h3>
                </div>

                <!-- TAMPILAN MOBILE (Card List yang Responsif) -->
                <div class="space-y-3 block sm:hidden">
                    <?php if(!empty($data_kunjungan)) : ?>
                        <?php foreach($data_kunjungan as $row) : ?>
                            <div class="p-4 rounded-2xl border flex flex-col gap-3" style="border-color: var(--pale-slate); background-color: #fafafa;">
                                <div class="flex justify-between items-start gap-2">
                                    <div class="font-bold text-sm truncate" style="color: var(--taupe-grey);"><?= htmlspecialchars($row['nama_anggota']); ?></div>
                                    <div>
                                        <?php if($row['waktu_keluar']) : ?>
                                            <span class="px-2 py-0.5 bg-stone-100 rounded-lg text-[10px] font-bold border inline-block" style="border-color: var(--pale-slate); color: var(--taupe-grey);">
                                                Keluar: <?= date('H:i', strtotime($row['waktu_keluar'])); ?>
                                            </span>
                                        <?php else : ?>
                                            <span class="animate-pulse font-extrabold uppercase text-[10px] tracking-wider inline-flex items-center" style="color: var(--old-rose);">
                                                <span class="w-1.5 h-1.5 rounded-full mr-1" style="background-color: var(--old-rose);"></span> Di Dalam
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="flex justify-between items-center text-xs pt-2 border-t border-stone-200">
                                    <div>
                                        <span class="opacity-70">Masuk:</span> 
                                        <strong style="color: var(--taupe-grey);"><?= date('H:i', strtotime($row['waktu_masuk'])); ?> WIB</strong>
                                    </div>
                                    <div>
                                        <span class="opacity-70">Durasi:</span> 
                                        <strong style="color: var(--taupe-grey);">
                                            <?php 
                                            if($row['waktu_keluar']) {
                                                $awal  = strtotime($row['waktu_masuk']);
                                                $akhir = strtotime($row['waktu_keluar']);
                                                $diff  = $akhir - $awal;
                                                $jam   = floor($diff / 3600);
                                                $menit = floor(($diff % 3600) / 60);
                                                echo ($jam > 0) ? "{$jam} jm {$menit} mnt" : "{$menit} mnt";
                                            } else { 
                                                echo "-"; 
                                            }
                                            ?>
                                        </strong>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-center py-8 text-xs italic opacity-60" style="color: var(--taupe-grey);">
                            <i class="fa-solid fa-clipboard-user text-2xl mb-2 block" style="color: var(--old-rose);"></i> Belum ada aktivitas kunjungan pada tanggal ini.
                        </div>
                    <?php endif; ?>
                </div>

                <!-- TAMPILAN TABLE (Untuk Tablet & Laptop) -->
                <div class="hidden sm:block overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b text-[11px] uppercase tracking-wider opacity-60" style="border-color: var(--pale-slate); color: var(--taupe-grey);">
                                <th class="pb-3.5 font-extrabold">Nama Siswa</th>
                                <th class="pb-3.5 font-extrabold text-center">Masuk</th>
                                <th class="pb-3.5 font-extrabold text-center">Keluar</th>
                                <th class="pb-3.5 font-extrabold text-center">Durasi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y" style="border-color: var(--pale-slate);">
                            <?php if(!empty($data_kunjungan)) : ?>
                                <?php foreach($data_kunjungan as $row) : ?>
                                <tr class="hover:bg-stone-50/50 transition-colors">
                                    <td class="py-4">
                                        <div class="font-bold text-sm" style="color: var(--taupe-grey);"><?= htmlspecialchars($row['nama_anggota']); ?></div>
                                    </td>
                                    <td class="py-4 text-center">
                                        <span class="px-3 py-1.5 rounded-xl font-bold text-xs inline-block" style="background-color: var(--soft-blush); color: var(--taupe-grey);">
                                            <i class="fa-regular fa-clock mr-1 text-[10px]" style="color: var(--old-rose);"></i> <?= date('H:i', strtotime($row['waktu_masuk'])); ?> WIB
                                        </span>
                                    </td>
                                    <td class="py-4 text-center font-bold">
                                        <?php if($row['waktu_keluar']) : ?>
                                            <span class="px-3 py-1.5 bg-stone-100 rounded-xl text-xs inline-block border" style="border-color: var(--pale-slate); color: var(--taupe-grey);">
                                                <?= date('H:i', strtotime($row['waktu_keluar'])); ?> WIB
                                            </span>
                                        <?php else : ?>
                                            <span class="animate-pulse font-extrabold uppercase text-[10px] tracking-wider inline-flex items-center" style="color: var(--old-rose);">
                                                <span class="w-2 h-2 rounded-full mr-1.5" style="background-color: var(--old-rose);"></span> Di Dalam
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-4 text-center text-xs font-semibold opacity-80" style="color: var(--taupe-grey);">
                                        <?php 
                                        if($row['waktu_keluar']) {
                                            $awal  = strtotime($row['waktu_masuk']);
                                            $akhir = strtotime($row['waktu_keluar']);
                                            $diff  = $akhir - $awal;
                                            
                                            $jam   = floor($diff / 3600);
                                            $menit = floor(($diff % 3600) / 60);

                                            if ($jam > 0) {
                                                echo "{$jam} jm {$menit} mnt";
                                            } else {
                                                echo "{$menit} mnt";
                                            }
                                        } else { 
                                            echo "-"; 
                                        }
                                        ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                            <tr>
                                <td colspan="4" class="text-center py-12 text-sm italic opacity-60" style="color: var(--taupe-grey);">
                                    <i class="fa-solid fa-clipboard-user text-2xl mb-2 block" style="color: var(--old-rose);"></i> Belum ada aktivitas kunjungan pada tanggal ini.
                                </td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </main>

    <script>
    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebarOverlay');
        
        sidebar.classList.toggle('-translate-x-full');
        overlay.classList.toggle('hidden');
    }
    </script>
</body>
</html>