<?php
session_start();
include '../../config/koneksi.php';

// Proteksi Halaman
if (!isset($_SESSION['admin'])) {
    header("Location: ../../login.php");
    exit;
}

$current_page = 'rak';
$admin_data   = $_SESSION['admin'];
$admin_name   = isset($admin_data['nama_petugas']) ? $admin_data['nama_petugas'] : "Admin";
$initial      = strtoupper(substr($admin_name, 0, 1));

// Query Data Rak + Jumlah Koleksi Buku
$sql = "SELECT r.*, COUNT(b.id_buku) as total_buku 
        FROM rak r 
        LEFT JOIN buku b ON r.kode_rak = b.id_rak 
        GROUP BY r.kode_rak"; 
$query_rak = mysqli_query($conn, $sql);

if (!$query_rak) {
    die("Error pada database: " . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Rak Buku - HARTS Admin</title>
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

        .card-custom { 
            background-color: #ffffff; 
            border: 1px solid var(--pale-slate); 
            box-shadow: 0 10px 25px -5px rgba(96, 77, 83, 0.04);
        }

        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-thumb { background: var(--old-rose); border-radius: 10px; }
    </style>
</head>
<body class="min-h-screen flex flex-col md:flex-row antialiased">

    <!-- Mobile Top Header -->
    <div class="md:hidden flex items-center justify-between p-4 sidebar-theme text-white sticky top-0 z-50">
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-lg flex items-center justify-center text-white" style="background-color: var(--taupe-grey);">
                <i class="fa-solid fa-book-bookmark text-sm"></i>
            </div>
            <span class="font-extrabold text-lg uppercase tracking-wider">Harts</span>
        </div>
        <button id="toggleSidebar" class="p-2 rounded-lg bg-white/10 hover:bg-white/20 text-white focus:outline-none">
            <i class="fa-solid fa-bars text-xl"></i>
        </button>
    </div>

    <!-- Sidebar Overlay for Mobile -->
    <div id="sidebarOverlay" class="fixed inset-0 bg-black/50 z-40 hidden md:hidden"></div>

    <!-- Sidebar Menu -->
    <aside id="sidebar" class="fixed md:static inset-y-0 left-0 w-72 h-full sidebar-theme p-6 flex flex-col shadow-sm z-50 transform -translate-x-full md:translate-x-0 transition-transform duration-300 ease-in-out">
        <!-- Logo -->
        <div class="flex items-center justify-between mb-8 px-2">
            <div class="flex items-center">
                <div class="w-10 h-10 rounded-xl mr-3 shadow-sm flex items-center justify-center text-white" style="background-color: var(--taupe-grey);">
                    <i class="fa-solid fa-book-bookmark text-lg"></i>
                </div>
                <div>
                    <h1 class="text-xl font-extrabold tracking-tight uppercase text-white leading-none">Harts</h1>
                    <span class="text-[10px] font-semibold tracking-wider text-white/80 uppercase">Library System</span>
                </div>
            </div>
            <button id="closeSidebar" class="md:hidden text-white/80 hover:text-white">
                <i class="fa-solid fa-xmark text-xl"></i>
            </button>
        </div>
        
        <!-- User Badge -->
        <a href="../../profile.php" class="p-3.5 rounded-2xl mb-6 flex items-center transition-all group border border-white/20 bg-white/10 hover:bg-white/20">
            <div class="w-9 h-9 rounded-xl flex items-center justify-center font-bold text-sm mr-3 text-white transition-transform group-hover:scale-105 shadow-sm" style="background-color: var(--old-rose);">
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
            
            <a href="../buku/index.php" class="nav-item flex items-center px-4 py-2.5 rounded-xl text-sm font-medium">
                <i class="fa-solid fa-book w-6 text-center text-sm mr-2.5"></i> Katalog Buku
            </a>

            <a href="rak.php" class="nav-item flex items-center px-4 py-2.5 rounded-xl text-sm font-medium nav-active">
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
    <main class="flex-1 w-full min-w-0 p-4 sm:p-6 lg:p-10 overflow-y-auto">
        <div class="max-w-7xl mx-auto">
            
            <!-- Header Bar -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6 sm:mb-8">
                <div>
                    <h2 class="text-xl sm:text-2xl lg:text-3xl font-extrabold tracking-tight" style="color: var(--taupe-grey);">Data Rak Buku <i class="fa-solid fa-cubes text-lg sm:text-xl ml-1" style="color: var(--old-rose);"></i></h2>
                    <p class="text-xs sm:text-sm font-medium opacity-80 mt-1" style="color: var(--taupe-grey);">Kelola lokasi penyimpanan dan tata letak koleksi buku.</p>
                </div>
                <a href="tambah_rak.php" class="inline-flex items-center justify-center px-4 py-2.5 sm:px-5 sm:py-3 rounded-2xl font-bold text-xs sm:text-sm text-white shadow-sm hover:opacity-90 active:scale-95 transition-all uppercase tracking-wider self-start sm:self-auto" style="background-color: var(--old-rose);">
                    <i class="fa-solid fa-plus mr-2"></i> Rak Baru
                </a>
            </div>

            <!-- Tabel Data Rak -->
            <div class="card-custom rounded-2xl sm:rounded-3xl p-4 sm:p-6 lg:p-8">
                <div class="overflow-x-auto w-full">
                    <table class="w-full text-left border-collapse min-w-[500px]">
                        <thead>
                            <tr class="border-b text-[10px] uppercase tracking-widest font-extrabold" style="border-color: var(--pale-slate); color: var(--taupe-grey);">
                                <th class="pb-4 px-3 sm:px-4">No</th>
                                <th class="pb-4 px-3 sm:px-4">Kode Rak</th>
                                <th class="pb-4 px-3 sm:px-4">Lokasi / Keterangan</th>
                                <th class="pb-4 px-3 sm:px-4 text-center">Isi Koleksi</th>
                                <th class="pb-4 px-3 sm:px-4 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y text-xs sm:text-sm font-semibold" style="border-color: var(--pale-slate); color: var(--taupe-grey);">
                            <?php 
                            if($query_rak && mysqli_num_rows($query_rak) > 0): 
                                $no = 1;
                                while($row = mysqli_fetch_assoc($query_rak)): ?>
                                <tr class="hover:bg-stone-50 transition-colors">
                                    <td class="py-3 sm:py-4 px-3 sm:px-4 font-bold opacity-60"><?= $no++; ?></td>
                                    <td class="py-3 sm:py-4 px-3 sm:px-4">
                                        <span class="px-2.5 py-1 rounded-xl font-bold text-[11px] sm:text-xs uppercase border" style="background-color: var(--soft-blush); border-color: var(--pale-slate); color: var(--taupe-grey);">
                                            <?= htmlspecialchars($row['kode_rak']); ?>
                                        </span>
                                    </td>
                                    <td class="py-3 sm:py-4 px-3 sm:px-4 font-semibold"><?= htmlspecialchars($row['lokasi']); ?></td>
                                    
                                    <td class="py-3 sm:py-4 px-3 sm:px-4 text-center">
                                        <span class="font-bold text-sm sm:text-base" style="color: var(--old-rose);"><?= $row['total_buku']; ?></span>
                                        <span class="text-[10px] font-bold uppercase ml-1 opacity-70">Judul</span>
                                    </td>

                                    <td class="py-3 sm:py-4 px-3 sm:px-4 text-center">
                                        <div class="flex justify-center gap-2">
                                            <a href="edit_rak.php?id=<?= urlencode($row['kode_rak']); ?>" class="w-8 h-8 sm:w-9 sm:h-9 rounded-xl flex items-center justify-center border transition-all hover:bg-stone-100" style="border-color: var(--pale-slate); color: var(--taupe-grey);" title="Edit Rak">
                                                <i class="fa-solid fa-pen-to-square text-xs sm:text-sm"></i>
                                            </a>
                                            <button onclick="confirmDelete('<?= $row['kode_rak']; ?>')" class="w-8 h-8 sm:w-9 sm:h-9 rounded-xl flex items-center justify-center border transition-all hover:bg-red-50 text-red-500" style="border-color: var(--pale-slate);" title="Hapus Rak">
                                                <i class="fa-solid fa-trash-can text-xs sm:text-sm"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="py-12 text-center text-xs sm:text-sm font-semibold italic opacity-60">
                                        Belum ada data rak buku.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div> 
        </div>
    </main>

    <!-- Script Drawer Toggle Sidebar & Alert -->
    <script>
        const sidebar = document.getElementById('sidebar');
        const toggleSidebarBtn = document.getElementById('toggleSidebar');
        const closeSidebarBtn = document.getElementById('closeSidebar');
        const overlay = document.getElementById('sidebarOverlay');

        function openSidebar() {
            sidebar.classList.remove('-translate-x-full');
            overlay.classList.remove('hidden');
        }

        function closeSidebar() {
            sidebar.classList.add('-translate-x-full');
            overlay.classList.add('hidden');
        }

        toggleSidebarBtn?.addEventListener('click', openSidebar);
        closeSidebarBtn?.addEventListener('click', closeSidebar);
        overlay?.addEventListener('click', closeSidebar);

        const urlParams = new URLSearchParams(window.location.search);
        const status = urlParams.get('status');
        const msg = urlParams.get('msg');

        if (status === 'success') {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: msg || 'Data rak berhasil diproses.',
                confirmButtonColor: '#DB7F8E'
            });
        } else if (status === 'error') {
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: msg || 'Terjadi kesalahan sistem.',
                confirmButtonColor: '#DB7F8E'
            });
        }

        function confirmDelete(kode) {
            Swal.fire({
                title: 'Hapus Rak?',
                text: `Apakah Anda yakin ingin menghapus rak ${kode}?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#DB7F8E',
                cancelButtonColor: '#9DA3A4',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = `hapus_rak.php?id=${kode}`;
                }
            });
        }
    </script>
</body>
</html>