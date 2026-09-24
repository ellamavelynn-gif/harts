<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit;
}
include 'config/koneksi.php';

$current_page = basename($_SERVER['PHP_SELF']);

// --- FUNGSI HITUNG DENDA ---
function hitungDenda($tgl_deadline) {
    if (!$tgl_deadline || $tgl_deadline == '0000-00-00') return 0;
    date_default_timezone_set('Asia/Jakarta');
    $tgl_sekarang = new DateTime(date('Y-m-d')); 
    $tgl_tenggat = new DateTime(date('Y-m-d', strtotime($tgl_deadline)));
    if ($tgl_sekarang > $tgl_tenggat) {
        $selisih = $tgl_sekarang->diff($tgl_tenggat);
        return $selisih->days * 5000; // Denda Rp 5.000 / hari
    }
    return 0;
}

// --- LOGIKA APPROVE ---
if (isset($_GET['approve_id'])) {
    $id_pinjam = $_GET['approve_id'];
    $data = mysqli_query($conn, "SELECT id_buku, jumlah FROM peminjaman WHERE kode_transaksi = '$id_pinjam'");
    $row_peminjaman = mysqli_fetch_assoc($data);
    $id_buku = $row_peminjaman['id_buku'];
    $jml_diminta = $row_peminjaman['jumlah'] ?? 1;

    $update = mysqli_query($conn, "UPDATE peminjaman SET status = 'Dipinjam' WHERE kode_transaksi = '$id_pinjam'");
    if ($update) {
        mysqli_query($conn, "UPDATE buku SET jumlah_buku = jumlah_buku - $jml_diminta WHERE id_buku = '$id_buku'");
        header("Location: index.php?status=approved");
        exit;
    }
}

// --- LOGIKA REJECT ---
if (isset($_GET['reject_id'])) {
    $id_reject = $_GET['reject_id'];
    $delete = mysqli_query($conn, "DELETE FROM peminjaman WHERE kode_transaksi = '$id_reject'");
    if ($delete) {
        header("Location: index.php?status=rejected");
        exit;
    }
}

$admin_data = $_SESSION['admin'];
$admin_name = $admin_data['nama_petugas'] ?? "Admin";
$initial    = strtoupper(substr($admin_name, 0, 1));

// --- STATISTIK KONTEN ---
$total_buku = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM buku"))['total'];
$total_anggota = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM anggota"))['total'];
$total_pengajuan = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM peminjaman WHERE status = 'Menunggu' OR status IS NULL OR status = ''"))['total'];

$total_pinjam = 0;
$total_terlambat = 0;

$q_denda_check = mysqli_query($conn, "SELECT tanggal_kembali FROM peminjaman WHERE status = 'Dipinjam'");

if (!$q_denda_check) {
    die("SQL Error (index.php - cek dipinjam/terlambat): " . mysqli_error($conn));
}

while ($row_d = mysqli_fetch_assoc($q_denda_check)) {
    $tgl_t = $row_d['tanggal_kembali'] ?? date('Y-m-d');
    if (hitungDenda($tgl_t) > 0) {
        $total_terlambat++;
    } else {
        $total_pinjam++;
    }
}

// Query List Persetujuan -> Simpan ke array
$query_approval = mysqli_query($conn, "SELECT peminjaman.*, anggota.nama_anggota, buku.judul_buku FROM peminjaman LEFT JOIN anggota ON peminjaman.id_anggota = anggota.id_anggota LEFT JOIN buku ON peminjaman.id_buku = buku.id_buku WHERE (peminjaman.status = 'Menunggu' OR peminjaman.status = '' OR peminjaman.status IS NULL)");

$data_approval = [];
if ($query_approval) {
    while ($row_app = mysqli_fetch_assoc($query_approval)) {
        $data_approval[] = $row_app;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HARTS - Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
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

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 24px -6px rgba(96, 77, 83, 0.08);
        }

        .icon-box {
            background-color: var(--soft-blush);
            color: var(--taupe-grey);
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
                        <h2 class="text-xl sm:text-2xl lg:text-3xl font-extrabold tracking-tight" style="color: var(--taupe-grey);">Selamat Bertugas, <?= htmlspecialchars(explode(' ', $admin_name)[0]); ?> 👋</h2>
                        <p class="mt-0.5 sm:mt-1 text-xs sm:text-sm font-medium opacity-80" style="color: var(--taupe-grey);">Ringkasan aktivitas dan persetujuan peminjaman hari ini.</p>
                    </div>
                    <!-- Hamburger Button untuk Layar Kecil -->
                    <button onclick="toggleSidebar()" class="lg:hidden p-2.5 rounded-2xl bg-white shadow-sm border border-stone-200 text-stone-700 hover:bg-stone-50 shrink-0">
                        <i class="fa-solid fa-bars text-lg"></i>
                    </button>
                </div>
                
                <div class="self-start sm:self-auto bg-white px-4 py-2 rounded-2xl font-semibold text-xs sm:text-sm shadow-sm flex items-center gap-2 border shrink-0" style="border-color: var(--pale-slate); color: var(--taupe-grey);">
                    <i class="fa-regular fa-calendar-check text-base" style="color: var(--old-rose);"></i> <?= date('d F Y'); ?>
                </div>
            </div>

            <!-- Stats Box (Grid Responsive) -->
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3 sm:gap-4 mb-6 sm:mb-8">
                <!-- Card 1: Total Buku -->
                <div class="stat-card p-4 sm:p-5 rounded-2xl flex flex-col justify-between">
                    <div class="flex justify-between items-center">
                        <span class="text-[10px] sm:text-[11px] font-bold uppercase tracking-wider opacity-70" style="color: var(--taupe-grey);">Total Buku</span>
                        <div class="icon-box w-7 h-7 sm:w-8 sm:h-8 rounded-lg flex items-center justify-center text-xs shrink-0">
                            <i class="fa-solid fa-book"></i>
                        </div>
                    </div>
                    <h3 class="text-lg sm:text-2xl lg:text-3xl font-extrabold mt-3" style="color: var(--taupe-grey);"><?= $total_buku; ?></h3>
                </div>

                <!-- Card 2: Anggota Aktif -->
                <div class="stat-card p-4 sm:p-5 rounded-2xl flex flex-col justify-between">
                    <div class="flex justify-between items-center">
                        <span class="text-[10px] sm:text-[11px] font-bold uppercase tracking-wider opacity-70" style="color: var(--taupe-grey);">Anggota</span>
                        <div class="icon-box w-7 h-7 sm:w-8 sm:h-8 rounded-lg flex items-center justify-center text-xs shrink-0">
                            <i class="fa-solid fa-users"></i>
                        </div>
                    </div>
                    <h3 class="text-lg sm:text-2xl lg:text-3xl font-extrabold mt-3" style="color: var(--taupe-grey);"><?= $total_anggota; ?></h3>
                </div>

                <!-- Card 3: Pengajuan -->
                <div class="stat-card p-4 sm:p-5 rounded-2xl flex flex-col justify-between">
                    <div class="flex justify-between items-center">
                        <span class="text-[10px] sm:text-[11px] font-bold uppercase tracking-wider opacity-70" style="color: var(--taupe-grey);">Pengajuan</span>
                        <div class="icon-box w-7 h-7 sm:w-8 sm:h-8 rounded-lg flex items-center justify-center text-xs text-white shrink-0" style="background-color: var(--old-rose);">
                            <i class="fa-solid fa-clock-rotate-left"></i>
                        </div>
                    </div>
                    <h3 class="text-lg sm:text-2xl lg:text-3xl font-extrabold mt-3" style="color: var(--taupe-grey);"><?= $total_pengajuan; ?></h3>
                </div>

                <!-- Card 4: Sedang Dipinjam -->
                <a href="peminjaman_aktif.php" class="stat-card p-4 sm:p-5 rounded-2xl flex flex-col justify-between group">
                    <div class="flex justify-between items-center">
                        <span class="text-[10px] sm:text-[11px] font-bold uppercase tracking-wider opacity-70" style="color: var(--taupe-grey);">Dipinjam</span>
                        <div class="icon-box w-7 h-7 sm:w-8 sm:h-8 rounded-lg flex items-center justify-center text-xs shrink-0">
                            <i class="fa-solid fa-book-open"></i>
                        </div>
                    </div>
                    <h3 class="text-lg sm:text-2xl lg:text-3xl font-extrabold mt-3" style="color: var(--taupe-grey);"><?= $total_pinjam; ?></h3>
                </a>

                <!-- Card 5: Kena Denda -->
                <a href="monitoring_denda.php" class="stat-card p-4 sm:p-5 rounded-2xl flex flex-col justify-between group col-span-2 sm:col-span-1">
                    <div class="flex justify-between items-center">
                        <span class="text-[10px] sm:text-[11px] font-bold uppercase tracking-wider" style="color: var(--old-rose);">Terlambat</span>
                        <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-lg flex items-center justify-center text-xs text-white shrink-0" style="background-color: var(--old-rose);">
                            <i class="fa-solid fa-triangle-exclamation"></i>
                        </div>
                    </div>
                    <h3 class="text-lg sm:text-2xl lg:text-3xl font-extrabold mt-3" style="color: var(--old-rose);"><?= $total_terlambat; ?></h3>
                </a>
            </div>

            <!-- Table / Card Area: Menunggu Persetujuan -->
            <div class="card-custom p-4 sm:p-6 lg:p-8 rounded-3xl mb-8">
                <div class="flex items-center mb-4 sm:mb-6">
                    <div class="w-1.5 h-5 rounded-full mr-3 shrink-0" style="background-color: var(--old-rose);"></div>
                    <h3 class="text-base sm:text-lg font-bold tracking-tight" style="color: var(--taupe-grey);">Menunggu Persetujuan</h3>
                </div>

                <!-- TAMPILAN MOBILE (Kartu Rapi & Responsif) -->
                <div class="space-y-3 block sm:hidden">
                    <?php if(!empty($data_approval)): ?>
                        <?php foreach($data_approval as $row) : ?>
                            <div class="p-4 rounded-2xl border flex flex-col gap-3" style="border-color: var(--pale-slate); background-color: #fafafa;">
                                <div class="flex justify-between items-start gap-2">
                                    <div class="overflow-hidden">
                                        <div class="font-extrabold text-sm truncate" style="color: var(--taupe-grey);"><?= htmlspecialchars($row['nama_anggota'] ?? 'Siswa'); ?></div>
                                        <div class="text-xs opacity-80 mt-0.5 truncate" style="color: var(--taupe-grey);">"<?= htmlspecialchars($row['judul_buku'] ?? 'Buku'); ?>"</div>
                                    </div>
                                    <span class="px-2.5 py-1 rounded-lg text-xs font-bold border shrink-0" style="background-color: var(--soft-blush); color: var(--taupe-grey); border-color: var(--pale-slate);">
                                        <?= $row['jumlah'] ?? '1'; ?> Buku
                                    </span>
                                </div>
                                <div class="flex gap-2 pt-2 border-t border-stone-200">
                                    <button onclick="confirmApprove(<?= $row['kode_transaksi']; ?>)" class="flex-1 py-2 rounded-xl text-xs font-bold text-white transition-all shadow-sm active:scale-95" style="background-color: var(--taupe-grey);">Terima</button>
                                    <button onclick="confirmReject(<?= $row['kode_transaksi']; ?>)" class="flex-1 py-2 rounded-xl text-xs font-bold transition-all active:scale-95 border" style="background-color: var(--soft-blush); color: var(--taupe-grey); border-color: var(--pale-slate);">Tolak</button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-center py-8 text-xs italic opacity-60" style="color: var(--taupe-grey);">
                            <i class="fa-regular fa-folder-open text-2xl mb-2 block" style="color: var(--old-rose);"></i> Tidak ada pengajuan peminjaman saat ini.
                        </div>
                    <?php endif; ?>
                </div>

                <!-- TAMPILAN TABLE (Untuk Tablet & Laptop) -->
                <div class="hidden sm:block overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b text-[11px] uppercase tracking-wider opacity-60" style="border-color: var(--pale-slate); color: var(--taupe-grey);">
                                <th class="pb-3.5 font-extrabold">Siswa & Buku</th>
                                <th class="pb-3.5 font-extrabold text-center">Jumlah</th>
                                <th class="pb-3.5 font-extrabold text-center">Tindakan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y" style="border-color: var(--pale-slate);">
                            <?php if(!empty($data_approval)): ?>
                                <?php foreach($data_approval as $row) : ?>
                                <tr class="hover:bg-stone-50/50 transition-colors">
                                    <td class="py-4">
                                        <div class="font-bold text-sm" style="color: var(--taupe-grey);"><?= htmlspecialchars($row['nama_anggota'] ?? 'Siswa'); ?></div>
                                        <div class="text-xs opacity-75 mt-0.5" style="color: var(--taupe-grey);">"<?= htmlspecialchars($row['judul_buku'] ?? 'Buku'); ?>"</div>
                                    </td>
                                    <td class="py-4 text-center font-bold text-sm" style="color: var(--taupe-grey);"><?= $row['jumlah'] ?? '1'; ?></td>
                                    <td class="py-4 text-center space-x-2">
                                        <button onclick="confirmApprove(<?= $row['kode_transaksi']; ?>)" class="px-3.5 py-1.5 rounded-xl text-xs font-bold text-white transition-all shadow-sm hover:opacity-90" style="background-color: var(--taupe-grey);">Terima</button>
                                        <button onclick="confirmReject(<?= $row['kode_transaksi']; ?>)" class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all hover:bg-stone-200" style="background-color: var(--soft-blush); color: var(--taupe-grey);">Tolak</button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="3" class="text-center py-10 text-sm italic opacity-60" style="color: var(--taupe-grey);">
                                        <i class="fa-regular fa-folder-open text-2xl mb-2 block" style="color: var(--old-rose);"></i> Tidak ada pengajuan peminjaman saat ini.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

            </div>

        </div>
    </main>

    <!-- Script Menu Toggle Mobile & SweetAlert -->
    <script>
    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebarOverlay');
        
        sidebar.classList.toggle('-translate-x-full');
        overlay.classList.toggle('hidden');
    }

    function confirmApprove(id) { 
        Swal.fire({ 
            title: 'Setujui Peminjaman?', 
            icon: 'question', 
            showCancelButton: true, 
            confirmButtonColor: '#604D53',
            cancelButtonColor: '#DB7F8E',
            confirmButtonText: 'Ya, Setujui',
            cancelButtonText: 'Batal'
        }).then((r) => { 
            if (r.isConfirmed) window.location.href = "?approve_id="+id; 
        }); 
    }
    
    function confirmReject(id) { 
        Swal.fire({ 
            title: 'Tolak Pengajuan?', 
            icon: 'warning', 
            showCancelButton: true, 
            confirmButtonColor: '#DB7F8E',
            cancelButtonColor: '#604D53',
            confirmButtonText: 'Ya, Tolak',
            cancelButtonText: 'Batal'
        }).then((r) => { 
            if (r.isConfirmed) window.location.href = "?reject_id="+id; 
        }); 
    }

    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('status') === 'approved') Swal.fire({ title: 'Berhasil disetujui!', icon: 'success', timer: 1500, showConfirmButton: false });
    if (urlParams.get('status') === 'rejected') Swal.fire({ title: 'Pengajuan ditolak.', icon: 'error', timer: 1500, showConfirmButton: false });
    </script>
</body>
</html>