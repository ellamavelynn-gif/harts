<?php
session_start();
include '../../config/koneksi.php';

// Proteksi Halaman
if (!isset($_SESSION['admin'])) {
    header("Location: ../../login.php");
    exit;
}

$current_page = 'buku';
$admin_data   = $_SESSION['admin'];
$admin_name   = isset($admin_data['nama_petugas']) ? $admin_data['nama_petugas'] : "Admin";
$initial      = strtoupper(substr($admin_name, 0, 1));

// --- LOGIKA SEARCH & FILTER ---
$search          = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$kategori_filter = isset($_GET['kategori']) ? mysqli_real_escape_string($conn, $_GET['kategori']) : '';

// Query Data Buku
$sql = "SELECT b.*, p.nama_pengarang, r.kode_rak, r.lokasi 
        FROM buku b 
        LEFT JOIN pengarang p ON b.id_pengarang = p.id_pengarang 
        LEFT JOIN rak r ON b.id_rak = r.kode_rak 
        WHERE (b.judul_buku LIKE '%$search%' OR p.nama_pengarang LIKE '%$search%')";

if ($kategori_filter != '') {
    $sql .= " AND b.kategori = '$kategori_filter'";
}

$sql .= " ORDER BY b.id_buku DESC";
$query = mysqli_query($conn, $sql);
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
    <title>Katalog Buku - HARTS Admin</title>
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

        .buku-card {
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

        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-thumb { background: var(--old-rose); border-radius: 10px; }
    </style>
</head>
<body class="flex flex-col lg:flex-row h-screen overflow-hidden" style="height: 100vh; height: 100dvh;">

    <!-- Overlay Sidebar Mobile -->
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
            
            <a href="../anggota/index.php?page=daftar" class="nav-item flex items-center px-4 py-2.5 rounded-xl text-sm font-medium">
                <i class="fa-solid fa-users w-6 text-center text-sm mr-2.5"></i> Data Anggota
            </a>

            <a href="../anggota/index.php?page=validasi" class="nav-item flex items-center px-4 py-2.5 rounded-xl text-sm font-medium">
                <i class="fa-solid fa-user-check w-6 text-center text-sm mr-2.5"></i> Validasi Akun
            </a>
            
            <a href="index.php" class="nav-item flex items-center px-4 py-2.5 rounded-xl text-sm font-medium nav-active">
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
            
            <!-- Header Section -->
            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4 mb-6 lg:mb-8">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-xl sm:text-2xl lg:text-3xl font-extrabold tracking-tight" style="color: var(--taupe-grey);">Katalog Buku</h2>
                        <p class="mt-0.5 sm:mt-1 text-xs sm:text-sm font-medium opacity-80" style="color: var(--taupe-grey);">Daftar koleksi modul dan mata pelajaran sekolah.</p>
                    </div>
                    <!-- Hamburger Mobile -->
                    <button onclick="toggleSidebar()" class="lg:hidden p-2.5 rounded-2xl bg-white shadow-sm border border-stone-200 text-stone-700 hover:bg-stone-50 shrink-0">
                        <i class="fa-solid fa-bars text-lg"></i>
                    </button>
                </div>
                
                <a href="tambah.php" class="px-4 py-2.5 sm:px-5 sm:py-2.5 text-white font-bold text-xs sm:text-sm rounded-2xl shadow-sm hover:opacity-90 transition-all flex items-center justify-center gap-2 uppercase tracking-wider shrink-0" style="background-color: var(--old-rose);">
                    <i class="fa-solid fa-plus"></i> Buku Baru
                </a>
            </div>

            <!-- Filter & Search Controls -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 mb-6 lg:mb-8">
                <div class="sm:col-span-2">
                    <form action="" method="GET">
                        <?php if($kategori_filter != ''): ?>
                            <input type="hidden" name="kategori" value="<?= htmlspecialchars($kategori_filter); ?>">
                        <?php endif; ?>
                        <div class="relative">
                            <input type="text" name="search" value="<?= htmlspecialchars($search); ?>" placeholder="Cari judul atau pengarang..." 
                                   class="w-full pl-11 pr-5 py-3 rounded-2xl card-custom outline-none font-semibold text-xs sm:text-sm transition-all focus:bg-stone-50" style="color: var(--taupe-grey);">
                            <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 opacity-40 text-sm" style="color: var(--taupe-grey);"></i>
                        </div>
                    </form>
                </div>

                <div>
                    <form action="" method="GET" id="filterForm">
                        <?php if($search != ''): ?>
                            <input type="hidden" name="search" value="<?= htmlspecialchars($search); ?>">
                        <?php endif; ?>
                        <select name="kategori" onchange="document.getElementById('filterForm').submit()" 
                                class="w-full px-4 sm:px-5 py-3 rounded-2xl card-custom outline-none font-semibold text-xs sm:text-sm cursor-pointer" style="color: var(--taupe-grey);">
                            <option value="">Semua Mapel</option>
                            <?php 
                            $mapels = ['MTK', 'IPA', 'IPS', 'B.Indonesia', 'B.Inggris', 'Agama', 'PJOK', 'Seni', 'Fiksi', 'Lainnya'];
                            foreach($mapels as $m): 
                                $sel = ($kategori_filter == $m) ? 'selected' : '';
                                echo "<option value='$m' $sel>$m</option>";
                            endforeach; 
                            ?>
                        </select>
                    </form>
                </div>

                <div>
                    <a href="index.php" class="w-full py-3 card-custom rounded-2xl font-bold text-xs tracking-wider uppercase text-center block hover:bg-stone-50 transition-colors shadow-sm" style="color: var(--taupe-grey);">
                        Reset Filter
                    </a>
                </div>
            </div>

            <!-- Data Table Card -->
            <div class="card-custom p-4 sm:p-6 lg:p-8 rounded-3xl mb-8">

                <!-- ================= MOBILE CARD VIEW (< sm) ================= -->
                <div class="sm:hidden space-y-3">
                    <?php if($query && mysqli_num_rows($query) > 0): ?>
                        <?php 
                        mysqli_data_seek($query, 0);
                        while($b = mysqli_fetch_assoc($query)): 
                        ?>
                        <div class="buku-card rounded-2xl p-4">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <span class="px-2.5 py-0.5 rounded-lg text-[9px] font-extrabold uppercase tracking-wider border mb-1.5 inline-block" style="background-color: var(--soft-blush); border-color: var(--pale-slate); color: var(--taupe-grey);">
                                        <?= htmlspecialchars($b['kategori'] ?? 'Lainnya'); ?>
                                    </span>
                                    <p class="font-bold text-sm leading-snug" style="color: var(--taupe-grey);"><?= htmlspecialchars($b['judul_buku']); ?></p>
                                    <p class="text-[10px] font-semibold opacity-60 mt-0.5" style="color: var(--taupe-grey);">Tahun <?= htmlspecialchars($b['tahun_buku']); ?></p>
                                </div>
                                <div class="shrink-0 text-center px-3 py-1.5 rounded-xl border" style="background-color: var(--soft-blush); border-color: var(--pale-slate);">
                                    <p class="text-[8px] font-extrabold uppercase tracking-widest opacity-60" style="color: var(--taupe-grey);">Stok</p>
                                    <p class="font-black text-base leading-tight" style="color: var(--taupe-grey);"><?= htmlspecialchars($b['jumlah_buku']); ?></p>
                                </div>
                            </div>

                            <div class="flex items-center justify-between mt-3 pt-3 border-t" style="border-color: var(--pale-slate);">
                                <div class="min-w-0">
                                    <p class="text-[9px] font-extrabold uppercase tracking-widest opacity-50" style="color: var(--taupe-grey);">Pengarang</p>
                                    <p class="text-xs font-bold truncate" style="color: var(--taupe-grey);"><?= htmlspecialchars($b['nama_pengarang'] ?? '-'); ?></p>
                                </div>
                                <span class="shrink-0 px-2.5 py-1 rounded-xl text-xs font-bold border inline-block ml-3" style="background-color: var(--soft-blush); border-color: var(--pale-slate);">
                                    <?= htmlspecialchars($b['kode_rak'] ?? '-'); ?>
                                </span>
                            </div>

                            <div class="grid grid-cols-2 gap-2 mt-3">
                                <a href="edit.php?id=<?= $b['id_buku']; ?>" class="inline-flex items-center justify-center gap-1.5 px-2 py-2.5 text-white font-extrabold text-[11px] rounded-2xl shadow-sm active:opacity-80 transition-opacity uppercase tracking-wide" style="background-color: var(--cool-steel);">
                                    <i class="fa-solid fa-pen-to-square"></i> Edit
                                </a>
                                <a href="hapus.php?id=<?= $b['id_buku']; ?>" class="btn-hapus inline-flex items-center justify-center gap-1.5 px-2 py-2.5 text-white font-extrabold text-[11px] rounded-2xl shadow-sm active:opacity-80 transition-opacity cursor-pointer uppercase tracking-wide" style="background-color: var(--old-rose);">
                                    <i class="fa-solid fa-trash"></i> Hapus
                                </a>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="text-center py-12 text-xs italic opacity-60" style="color: var(--taupe-grey);">
                            <i class="fa-solid fa-book-open text-2xl mb-2 block" style="color: var(--old-rose);"></i> Tidak ada data buku ditemukan.
                        </div>
                    <?php endif; ?>
                </div>

                <!-- ================= DESKTOP / TABLET TABLE VIEW (>= sm) ================= -->
                <div class="hidden sm:block overflow-x-auto table-scroll">
                    <table class="w-full text-left border-collapse min-w-[550px]">
                        <thead>
                            <tr class="border-b text-[10px] sm:text-[11px] uppercase tracking-wider opacity-60" style="border-color: var(--pale-slate); color: var(--taupe-grey);">
                                <th class="pb-3.5 font-extrabold">Info Buku</th>
                                <th class="pb-3.5 font-extrabold">Pengarang</th>
                                <th class="pb-3.5 font-extrabold">Lokasi Rak</th> 
                                <th class="pb-3.5 font-extrabold text-center">Stok</th>
                                <th class="pb-3.5 font-extrabold text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y" style="border-color: var(--pale-slate);">
                            <?php if($query && mysqli_num_rows($query) > 0): ?>
                                <?php 
                                mysqli_data_seek($query, 0);
                                while($b = mysqli_fetch_assoc($query)): 
                                ?>
                                <tr class="hover:bg-stone-50/50 transition-colors">
                                    <td class="py-3.5 sm:py-4">
                                        <span class="px-2.5 py-0.5 rounded-lg text-[9px] font-extrabold uppercase tracking-wider border mb-1 inline-block" style="background-color: var(--soft-blush); border-color: var(--pale-slate); color: var(--taupe-grey);">
                                            <?= htmlspecialchars($b['kategori'] ?? 'Lainnya'); ?>
                                        </span>
                                        <p class="font-bold text-xs sm:text-sm leading-snug" style="color: var(--taupe-grey);"><?= htmlspecialchars($b['judul_buku']); ?></p>
                                        <p class="text-[10px] sm:text-[11px] font-semibold opacity-60 mt-0.5" style="color: var(--taupe-grey);">Tahun <?= htmlspecialchars($b['tahun_buku']); ?></p>
                                    </td>
                                    <td class="py-3.5 sm:py-4 font-bold text-xs sm:text-sm" style="color: var(--taupe-grey);">
                                        <?= htmlspecialchars($b['nama_pengarang'] ?? '-'); ?>
                                    </td>
                                    <td class="py-3.5 sm:py-4 font-bold text-xs sm:text-sm" style="color: var(--taupe-grey);">
                                        <span class="px-2.5 py-1 rounded-xl text-xs font-bold border inline-block" style="background-color: var(--soft-blush); border-color: var(--pale-slate);">
                                            <?= htmlspecialchars($b['kode_rak'] ?? '-'); ?>
                                        </span>
                                    </td>
                                    <td class="py-3.5 sm:py-4 text-center font-black text-sm sm:text-base" style="color: var(--taupe-grey);">
                                        <?= htmlspecialchars($b['jumlah_buku']); ?>
                                    </td>
                                    <td class="py-3.5 sm:py-4 text-center">
                                        <div class="flex justify-center gap-1.5 sm:gap-2">
                                            <!-- Edit -->
                                            <a href="edit.php?id=<?= $b['id_buku']; ?>" class="w-8 h-8 flex items-center justify-center text-white rounded-xl shadow-sm hover:opacity-90 transition-opacity shrink-0" style="background-color: var(--cool-steel);" title="Edit Data">
                                                <i class="fa-solid fa-pen-to-square text-xs"></i>
                                            </a>

                                            <!-- Hapus -->
                                            <a href="hapus.php?id=<?= $b['id_buku']; ?>" class="btn-hapus w-8 h-8 flex items-center justify-center text-white rounded-xl shadow-sm hover:opacity-90 transition-opacity cursor-pointer shrink-0" style="background-color: var(--old-rose);" title="Hapus Data">
                                                <i class="fa-solid fa-trash text-xs"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center py-12 text-xs lg:text-sm italic opacity-60" style="color: var(--taupe-grey);">
                                        <i class="fa-solid fa-book-open text-2xl mb-2 block" style="color: var(--old-rose);"></i> Tidak ada data buku ditemukan.
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

    document.querySelectorAll('.btn-hapus').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const url = this.getAttribute('href');

            Swal.fire({
                title: 'Hapus buku ini?',
                text: "Data buku yang dihapus tidak bisa dikembalikan!",
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