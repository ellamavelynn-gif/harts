<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit;
}
include 'config/koneksi.php';

// --- DOMPDF CONFIGURATION ---
require_once 'vendor/autoload.php'; 
use Dompdf\Dompdf;
use Dompdf\Options;

$current_page = 'laporan.php';
$admin_data   = $_SESSION['admin'];
$admin_name   = $admin_data['nama_petugas'] ?? "Admin";
$initial      = strtoupper(substr($admin_name, 0, 1));

// Base Query
$sql_base = "SELECT peminjaman.*, anggota.nama_anggota, buku.judul_buku 
            FROM peminjaman 
            LEFT JOIN anggota ON peminjaman.id_anggota = anggota.id_anggota 
            LEFT JOIN buku ON peminjaman.id_buku = buku.id_buku";

// --- LOGIKA DOWNLOAD PDF ---
if (isset($_GET['download']) && $_GET['download'] === 'pdf') {
    $pdf_status = $_GET['type'] ?? 'semua';
    
    if ($pdf_status === 'berlangsung') {
        $where_pdf = " WHERE LOWER(peminjaman.status) = 'dipinjam'";
        $title_pdf = "Laporan Peminjaman Buku Berlangsung";
    } elseif ($pdf_status === 'kembali') {
        $where_pdf = " WHERE LOWER(peminjaman.status) = 'kembali'";
        $title_pdf = "Laporan Riwayat Buku Dikembalikan";
    } else {
        $where_pdf = "";
        $title_pdf = "Laporan Keseluruhan Peminjaman Buku";
    }

    $pdf_query = mysqli_query($conn, $sql_base . $where_pdf . " ORDER BY peminjaman.kode_transaksi DESC");

    ob_start();
    ?>
    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <style>
            body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; color: #333333; font-size: 11px; line-height: 1.4; }
            .header-table { width: 100%; border-bottom: 2px solid #604D53; padding-bottom: 10px; margin-bottom: 20px; }
            .title { font-size: 18px; font-weight: bold; text-transform: uppercase; margin: 0; color: #604D53; }
            .subtitle { font-size: 9px; font-weight: bold; text-transform: uppercase; color: #DB7F8E; margin: 0; letter-spacing: 1px; }
            .date-print { text-align: right; font-size: 10px; color: #604D53; }
            table.data-table { width: 100%; border-collapse: collapse; margin-top: 15px; }
            table.data-table th { background-color: #604D53; color: #ffffff; font-size: 9px; font-weight: bold; text-transform: uppercase; padding: 8px 10px; border: 1px solid #604D53; text-align: left; }
            table.data-table td { padding: 8px 10px; border: 1px solid #D5C5C8; }
            .text-center { text-align: center; }
            .text-bold { font-weight: bold; }
            .text-italic { font-style: italic; color: #555555; }
            .badge { padding: 3px 8px; border-radius: 10px; font-size: 8px; font-weight: bold; text-transform: uppercase; display: inline-block; }
            .badge-dipinjam { background-color: #fef3c7; color: #b45309; }
            .badge-kembali { background-color: #d1fae5; color: #047857; }
            .signature-table { width: 100%; margin-top: 40px; }
            .signature-box { width: 35%; text-align: center; }
            .signature-role { font-size: 10px; color: #604D53; margin-bottom: 50px; }
            .signature-name { font-weight: bold; border-bottom: 1px solid #604D53; padding-bottom: 2px; display: inline-block; width: 150px; }
        </style>
    </head>
    <body>
        <table class="header-table">
            <tr>
                <td>
                    <div class="title"><?= $title_pdf; ?></div>
                    <div class="subtitle">Perpustakaan Digital HARTS</div>
                </td>
                <td class="date-print">
                    <strong>Dicetak Pada:</strong><br>
                    <?= date('d F Y'); ?>
                </td>
            </tr>
        </table>
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 6%; text-align: center;">No</th>
                    <th style="width: 26%;">Peminjam</th>
                    <th style="width: 34%;">Judul Buku</th>
                    <th style="width: 12%; text-align: center;">Pinjam</th>
                    <th style="width: 12%; text-align: center;">Kembali</th>
                    <th style="width: 10%; text-align: center;">Status</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $no = 1; 
                if (mysqli_num_rows($pdf_query) > 0):
                    while($row = mysqli_fetch_assoc($pdf_query)) : 
                        $tgl_p = !empty($row['tanggal_pinjam']) ? date('d/m/Y', strtotime($row['tanggal_pinjam'])) : '-';
                        $tgl_k = !empty($row['tanggal_kembali']) ? date('d/m/Y', strtotime($row['tanggal_kembali'])) : '-';
                        $st    = strtolower(trim($row['status']));
                ?>
                <tr>
                    <td class="text-center"><?= $no++; ?></td>
                    <td class="text-bold"><?= htmlspecialchars($row['nama_anggota'] ?? 'N/A'); ?></td>
                    <td class="text-italic">"<?= htmlspecialchars($row['judul_buku'] ?? 'N/A'); ?>"</td>
                    <td class="text-center"><?= $tgl_p; ?></td>
                    <td class="text-center"><?= $tgl_k; ?></td>
                    <td class="text-center">
                        <?php if ($st === 'dipinjam') : ?>
                            <span class="badge badge-dipinjam">Dipinjam</span>
                        <?php else : ?>
                            <span class="badge badge-kembali">Kembali</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; else: ?>
                <tr><td colspan="6" class="text-center" style="padding: 20px;">Tidak ada data peminjaman.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
        <table class="signature-table">
            <tr>
                <td style="width: 65%;"></td>
                <td class="signature-box">
                    <div class="signature-role">Petugas Perpustakaan,</div>
                    <div class="signature-name"><?= htmlspecialchars($_SESSION['admin']['nama_petugas'] ?? 'Admin'); ?></div>
                    <div style="font-size: 8px; color: #604D53; text-transform: uppercase; margin-top: 3px;">HARTS Library System</div>
                </td>
            </tr>
        </table>
    </body>
    </html>
    <?php
    $html_content = ob_get_clean();
    $options = new Options();
    $options->set('isHtml5ParserEnabled', true);
    $options->set('isRemoteEnabled', true); 
    $dompdf = new Dompdf($options);
    $dompdf->loadHtml($html_content);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();
    $file_name = "Laporan-HARTS-" . ucfirst($pdf_status) . "-" . date('Ymd') . ".pdf";
    $dompdf->stream($file_name, array("Attachment" => true));
    exit;
}

// Data Query Kartu / Statistik
$q_total_pinjam   = mysqli_query($conn, "SELECT COUNT(*) as total FROM peminjaman WHERE LOWER(status) = 'dipinjam'");
$count_berlangsung = mysqli_fetch_assoc($q_total_pinjam)['total'] ?? 0;

$q_total_kembali  = mysqli_query($conn, "SELECT COUNT(*) as total FROM peminjaman WHERE LOWER(status) = 'kembali'");
$count_kembali     = mysqli_fetch_assoc($q_total_kembali)['total'] ?? 0;

$total_semua = $count_berlangsung + $count_kembali;

// --- TOP 5 BUKU POPULER ---
$q_buku_populer = mysqli_query($conn, "
    SELECT buku.judul_buku, COUNT(peminjaman.id_buku) AS total_pinjam 
    FROM peminjaman 
    JOIN buku ON peminjaman.id_buku = buku.id_buku 
    GROUP BY peminjaman.id_buku 
    ORDER BY total_pinjam DESC 
    LIMIT 5
");

$labels_buku = [];
$data_buku   = [];
while ($row_buku = mysqli_fetch_assoc($q_buku_populer)) {
    $labels_buku[] = $row_buku['judul_buku'];
    $data_buku[]   = (int)$row_buku['total_pinjam'];
}

// --- PAGINATION & SEARCH SETUP ---
$limit = 5;

// 1. Paginasi Tabel Berlangsung
$page_b   = isset($_GET['page_b']) ? (int)$_GET['page_b'] : 1;
$start_b  = ($page_b > 1) ? ($page_b * $limit) - $limit : 0;
$search_b = isset($_GET['search_b']) ? mysqli_real_escape_string($conn, $_GET['search_b']) : '';

$where_clause_b = " WHERE LOWER(peminjaman.status) = 'dipinjam'";
if (!empty($search_b)) {
    $where_clause_b .= " AND (anggota.nama_anggota LIKE '%$search_b%' OR buku.judul_buku LIKE '%$search_b%')";
}

$q_total_b    = mysqli_query($conn, "SELECT COUNT(*) as total FROM peminjaman LEFT JOIN anggota ON peminjaman.id_anggota = anggota.id_anggota LEFT JOIN buku ON peminjaman.id_buku = buku.id_buku" . $where_clause_b);
$total_data_b = mysqli_fetch_assoc($q_total_b)['total'];
$total_pages_b = ceil($total_data_b / $limit);

$query_berlangsung = mysqli_query($conn, $sql_base . $where_clause_b . " ORDER BY peminjaman.kode_transaksi DESC LIMIT $start_b, $limit");

// 2. Paginasi Tabel Riwayat (Kembali)
$page_k   = isset($_GET['page_k']) ? (int)$_GET['page_k'] : 1;
$start_k  = ($page_k > 1) ? ($page_k * $limit) - $limit : 0;
$search_k = isset($_GET['search_k']) ? mysqli_real_escape_string($conn, $_GET['search_k']) : '';

$where_clause_k = " WHERE LOWER(peminjaman.status) = 'kembali'";
if (!empty($search_k)) {
    $where_clause_k .= " AND (anggota.nama_anggota LIKE '%$search_k%' OR buku.judul_buku LIKE '%$search_k%')";
}

$q_total_k    = mysqli_query($conn, "SELECT COUNT(*) as total FROM peminjaman LEFT JOIN anggota ON peminjaman.id_anggota = anggota.id_anggota LEFT JOIN buku ON peminjaman.id_buku = buku.id_buku" . $where_clause_k);
$total_data_k = mysqli_fetch_assoc($q_total_k)['total'];
$total_pages_k = ceil($total_data_k / $limit);

$query_kembali = mysqli_query($conn, $sql_base . $where_clause_k . " ORDER BY peminjaman.kode_transaksi DESC LIMIT $start_k, $limit");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Utama & Analytics - HARTS Admin</title>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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

        @media print {
            .no-print { display: none !important; }
            body { background: white; color: black; }
            .card-custom { border: none; box-shadow: none; }
        }

        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-thumb { background: var(--old-rose); border-radius: 10px; }
    </style>
</head>
<body class="min-h-screen flex flex-col md:flex-row antialiased">

    <!-- Mobile Top Header -->
    <div class="md:hidden flex items-center justify-between p-4 sidebar-theme text-white sticky top-0 z-50 no-print">
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

    <!-- Sidebar Overlay Mobile -->
    <div id="sidebarOverlay" class="fixed inset-0 bg-black/50 z-40 hidden md:hidden no-print"></div>

    <!-- Sidebar Menu -->
    <aside id="sidebar" class="fixed md:static inset-y-0 left-0 w-72 min-h-screen sidebar-theme p-6 flex flex-col shadow-sm z-50 transform -translate-x-full md:translate-x-0 transition-transform duration-300 ease-in-out no-print">
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
        
        <a href="profile.php" class="p-3.5 rounded-2xl mb-6 flex items-center transition-all group border border-white/20 bg-white/10 hover:bg-white/20">
            <div class="w-9 h-9 rounded-xl flex items-center justify-center font-bold text-sm mr-3 text-white transition-transform group-hover:scale-105 shadow-sm" style="background-color: var(--old-rose);">
                <?= $initial; ?>
            </div>
            <div class="overflow-hidden">
                <p class="text-[10px] font-bold uppercase tracking-widest text-white/70">Petugas</p>
                <p class="text-sm font-bold leading-tight text-white truncate"><?= htmlspecialchars($admin_name); ?></p>
            </div>
        </a>

        <nav class="space-y-1 flex-1 overflow-y-auto pr-1">
            <p class="text-[10px] font-extrabold uppercase tracking-widest ml-3 mb-2 text-white/60">Utama</p>
            <a href="index.php" class="nav-item flex items-center px-4 py-2.5 rounded-xl text-sm font-medium">
                <i class="fa-solid fa-house w-6 text-center text-sm mr-2.5"></i> Dashboard
            </a>
            
            <p class="text-[10px] font-extrabold uppercase tracking-widest ml-3 mt-6 mb-2 text-white/60">Layanan & Presensi</p>
            <a href="presensi.php" class="nav-item flex items-center px-4 py-2.5 rounded-xl text-sm font-medium">
                <i class="fa-solid fa-qrcode w-6 text-center text-sm mr-2.5"></i> Scanner Presensi
            </a>
            <a href="log_kunjungan.php" class="nav-item flex items-center px-4 py-2.5 rounded-xl text-sm font-medium">
                <i class="fa-solid fa-clipboard-user w-6 text-center text-sm mr-2.5"></i> Log Kunjungan
            </a>

            <p class="text-[10px] font-extrabold uppercase tracking-widest ml-3 mt-6 mb-2 text-white/60">Keuangan</p>
            <a href="kas_denda.php" class="nav-item flex items-center px-4 py-2.5 rounded-xl text-sm font-medium">
                <i class="fa-solid fa-wallet w-6 text-center text-sm mr-2.5"></i> Kas Denda
            </a>
            <a href="monitoring_denda.php" class="nav-item flex items-center px-4 py-2.5 rounded-xl text-sm font-medium">
                <i class="fa-solid fa-triangle-exclamation w-6 text-center text-sm mr-2.5"></i> Monitoring Denda
            </a>

            <p class="text-[10px] font-extrabold uppercase tracking-widest ml-3 mt-6 mb-2 text-white/60">Katalog & Anggota</p>
            <a href="modules/anggota/index.php?page=daftar" class="nav-item flex items-center px-4 py-2.5 rounded-xl text-sm font-medium">
                <i class="fa-solid fa-users w-6 text-center text-sm mr-2.5"></i> Data Anggota
            </a>
            <a href="modules/anggota/index.php?page=validasi" class="nav-item flex items-center px-4 py-2.5 rounded-xl text-sm font-medium">
                <i class="fa-solid fa-user-check w-6 text-center text-sm mr-2.5"></i> Validasi Akun
            </a>
            <a href="modules/buku/index.php" class="nav-item flex items-center px-4 py-2.5 rounded-xl text-sm font-medium">
                <i class="fa-solid fa-book w-6 text-center text-sm mr-2.5"></i> Katalog Buku
            </a>
            <a href="modules/rak/rak.php" class="nav-item flex items-center px-4 py-2.5 rounded-xl text-sm font-medium">
                <i class="fa-solid fa-cubes w-6 text-center text-sm mr-2.5"></i> Data Rak
            </a>

            <p class="text-[10px] font-extrabold uppercase tracking-widest ml-3 mt-6 mb-2 text-white/60">Laporan</p>
            <a href="laporan.php" class="nav-item flex items-center px-4 py-2.5 rounded-xl text-sm font-medium nav-active">
                <i class="fa-solid fa-chart-pie w-6 text-center text-sm mr-2.5"></i> Laporan Utama
            </a>
        </nav>
        
        <a href="logout.php" class="px-4 py-3 text-white/90 hover:text-white font-bold flex items-center hover:bg-white/10 rounded-xl mt-auto transition-colors text-sm">
            <i class="fa-solid fa-arrow-right-from-bracket mr-2.5 text-center w-6"></i> Keluar
        </a>
    </aside>

    <!-- Main Content Area -->
    <main class="flex-1 w-full min-w-0 p-4 sm:p-6 lg:p-10 overflow-y-auto">
        <div class="max-w-7xl mx-auto space-y-8">
            
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h2 class="text-xl sm:text-2xl lg:text-3xl font-extrabold tracking-tight" style="color: var(--taupe-grey);">
                        Laporan Utama & Analisis Data <i class="fa-solid fa-chart-pie text-lg sm:text-xl ml-1" style="color: var(--old-rose);"></i>
                    </h2>
                    <p class="text-xs sm:text-sm font-medium opacity-80 mt-1" style="color: var(--taupe-grey);">
                        Ringkasan aktivitas peminjaman, statistik, dan cetak dokumen resmi PDF.
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-2 no-print">
                    <button onclick="window.print()" class="px-4 py-2.5 rounded-xl text-xs font-bold text-white shadow-sm flex items-center gap-2 transition-all hover:opacity-90" style="background-color: var(--taupe-grey);">
                        <i class="fa-solid fa-print"></i> Cetak Halaman
                    </button>
                </div>
            </div>

            <!-- SECTION 1: DIAGRAM ANALISIS -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Donut Chart Status -->
                <div class="card-custom rounded-2xl sm:rounded-3xl p-5 lg:p-6 flex flex-col justify-between">
                    <div>
                        <h3 class="font-extrabold text-base mb-1" style="color: var(--taupe-grey);">
                            <i class="fa-solid fa-chart-donut text-sm mr-2" style="color: var(--old-rose);"></i> Distribusi Status
                        </h3>
                        <p class="text-xs text-stone-500 font-medium mb-4">Perbandingan peminjaman aktif & dikembalikan.</p>
                    </div>
                    <div class="relative w-48 h-48 mx-auto my-2">
                        <canvas id="statusChart"></canvas>
                    </div>
                    <div class="grid grid-cols-2 gap-2 mt-4 text-center">
                        <div class="p-2 rounded-xl bg-amber-50 border border-amber-200">
                            <p class="text-[10px] font-bold text-amber-700 uppercase">Berlangsung</p>
                            <p class="text-lg font-extrabold text-amber-800"><?= $count_berlangsung; ?></p>
                        </div>
                        <div class="p-2 rounded-xl bg-emerald-50 border border-emerald-200">
                            <p class="text-[10px] font-bold text-emerald-700 uppercase">Dikembalikan</p>
                            <p class="text-lg font-extrabold text-emerald-800"><?= $count_kembali; ?></p>
                        </div>
                    </div>
                </div>

                <!-- Bar Chart Ringkasan Transaksi -->
                <div class="card-custom rounded-2xl sm:rounded-3xl p-5 lg:p-6 flex flex-col justify-between">
                    <div>
                        <h3 class="font-extrabold text-base mb-1" style="color: var(--taupe-grey);">
                            <i class="fa-solid fa-chart-column text-sm mr-2" style="color: var(--old-rose);"></i> Ringkasan Total Peminjaman
                        </h3>
                        <p class="text-xs text-stone-500 font-medium mb-4">Visualisasi perbandingan kuantitas data transaksi.</p>
                    </div>
                    <div class="relative h-56 w-full">
                        <canvas id="barChart"></canvas>
                    </div>
                    <div class="flex items-center justify-between text-xs font-semibold pt-4 border-t border-stone-100 text-stone-600">
                        <span>Total Akumulasi: <strong><?= $total_semua; ?> Transaksi</strong></span>
                        <span class="text-[10px] text-stone-400">Ter-update otomatis</span>
                    </div>
                </div>

                <!-- Top Buku Populer Chart -->
                <div class="card-custom rounded-2xl sm:rounded-3xl p-5 lg:p-6 flex flex-col justify-between">
                    <div>
                        <h3 class="font-extrabold text-base mb-1" style="color: var(--taupe-grey);">
                            <i class="fa-solid fa-fire text-sm mr-2" style="color: var(--old-rose);"></i> Buku Paling Sering Dipinjam
                        </h3>
                        <p class="text-xs text-stone-500 font-medium mb-4">Top 5 buku paling terfavorit anggota.</p>
                    </div>
                    <div class="relative h-56 w-full">
                        <canvas id="topBooksChart"></canvas>
                    </div>
                    <div class="flex items-center justify-between text-xs font-semibold pt-4 border-t border-stone-100 text-stone-600">
                        <span>Status: <strong>Top 5 Buku</strong></span>
                        <span class="text-[10px] text-stone-400">Terfavorit</span>
                    </div>
                </div>
            </div>

            <!-- SECTION 2: PEMINJAMAN BERLANGSUNG (ACCORDION) -->
            <div id="section-berlangsung" class="card-custom rounded-2xl sm:rounded-3xl p-5 lg:p-7">
                <div onclick="toggleAccordion('content-berlangsung', 'icon-berlangsung')" class="flex flex-col md:flex-row md:items-center justify-between gap-4 cursor-pointer select-none">
                    <div>
                        <h3 class="text-lg font-extrabold flex items-center gap-2" style="color: var(--taupe-grey);">
                            <span class="w-3 h-3 rounded-full bg-amber-500 inline-block"></span>
                            Peminjaman Buku Berlangsung (Sedang Dipinjam)
                            <i id="icon-berlangsung" class="fa-solid fa-chevron-down text-sm transition-transform duration-300 ml-2"></i>
                        </h3>
                        <p class="text-xs text-stone-500 font-medium mt-0.5">Klik untuk melihat daftar peminjaman aktif yang belum dikembalikan.</p>
                    </div>
                </div>

                <div id="content-berlangsung" class="hidden mt-6 pt-4 border-t border-stone-100">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-4">
                        <div></div>
                        <div class="flex flex-wrap items-center gap-2">
                            <form method="GET" class="flex items-center gap-2">
                                <input type="hidden" name="page_b" value="1">
                                <input type="text" name="search_b" value="<?= htmlspecialchars($search_b); ?>" placeholder="Cari peminjam/buku..." class="px-3 py-1.5 text-xs rounded-lg border border-stone-300 focus:outline-none focus:border-amber-500">
                                <button type="submit" class="px-3 py-1.5 text-xs font-bold bg-stone-100 text-stone-700 rounded-lg hover:bg-stone-200"><i class="fa-solid fa-search"></i></button>
                            </form>
                            <a href="?download=pdf&type=berlangsung" class="no-print px-3 py-1.5 rounded-lg text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200 hover:bg-amber-100 transition-colors inline-flex items-center gap-1.5">
                                <i class="fa-solid fa-download"></i> PDF
                            </a>
                        </div>
                    </div>

                    <div class="overflow-x-auto w-full">
                        <table class="w-full text-left border-collapse table-auto">
                            <thead>
                                <tr class="border-b text-[10px] uppercase tracking-widest font-extrabold" style="border-color: var(--pale-slate); color: var(--taupe-grey);">
                                    <th class="pb-3 px-2 w-10 text-center">No</th>
                                    <th class="pb-3 px-3">Peminjam</th>
                                    <th class="pb-3 px-3">Judul Buku</th>
                                    <th class="pb-3 px-2 text-center">Tgl Pinjam</th>
                                    <th class="pb-3 px-2 text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y text-xs sm:text-sm font-semibold" style="border-color: var(--pale-slate); color: var(--taupe-grey);">
                                <?php 
                                $no1 = $start_b + 1;
                                if (mysqli_num_rows($query_berlangsung) > 0):
                                    while ($row = mysqli_fetch_assoc($query_berlangsung)): 
                                        $tgl_pinjam = !empty($row['tanggal_pinjam']) ? date('d M Y', strtotime($row['tanggal_pinjam'])) : '-';
                                ?>
                                <tr class="hover:bg-amber-50/40 transition-colors">
                                    <td class="py-3 px-2 text-center font-bold"><?= $no1++; ?></td>
                                    <td class="py-3 px-3 font-bold break-words"><?= htmlspecialchars($row['nama_anggota'] ?? 'N/A'); ?></td>
                                    <td class="py-3 px-3 italic break-words">"<?= htmlspecialchars($row['judul_buku'] ?? 'N/A'); ?>"</td>
                                    <td class="py-3 px-2 text-center opacity-80 whitespace-nowrap"><?= $tgl_pinjam; ?></td>
                                    <td class="py-3 px-2 text-center whitespace-nowrap">
                                        <span class="px-2.5 py-1 rounded-xl text-[10px] font-extrabold uppercase tracking-wider bg-amber-50 text-amber-700 border border-amber-200 inline-block">
                                            Sedang Dipinjam
                                        </span>
                                    </td>
                                </tr>
                                <?php endwhile; else: ?>
                                <tr><td colspan="5" class="py-8 text-center text-xs font-semibold italic opacity-60">Tidak ada data peminjaman yang sedang berlangsung.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <?php if ($total_pages_b > 1): ?>
                    <div class="flex items-center justify-between mt-4 pt-3 border-t border-stone-100 text-xs">
                        <span class="text-stone-500 font-medium">Menampilkan halaman <?= $page_b; ?> dari <?= $total_pages_b; ?></span>
                        <div class="flex gap-1">
                            <?php for($i=1; $i<=$total_pages_b; $i++): ?>
                                <a href="?page_b=<?= $i; ?>&search_b=<?= urlencode($search_b); ?>#section-berlangsung" class="px-3 py-1 rounded-lg font-bold border <?= ($page_b == $i) ? 'bg-amber-500 text-white border-amber-500' : 'bg-white text-stone-600 border-stone-200 hover:bg-stone-50'; ?>">
                                    <?= $i; ?>
                                </a>
                            <?php endfor; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- SECTION 3: RIWAYAT PENGEMBALIAN (ACCORDION) -->
            <div id="section-kembali" class="card-custom rounded-2xl sm:rounded-3xl p-5 lg:p-7">
                <div onclick="toggleAccordion('content-kembali', 'icon-kembali')" class="flex flex-col md:flex-row md:items-center justify-between gap-4 cursor-pointer select-none">
                    <div>
                        <h3 class="text-lg font-extrabold flex items-center gap-2" style="color: var(--taupe-grey);">
                            <span class="w-3 h-3 rounded-full bg-emerald-500 inline-block"></span>
                            Riwayat Peminjaman Buku (Sudah Dikembalikan)
                            <i id="icon-kembali" class="fa-solid fa-chevron-down text-sm transition-transform duration-300 ml-2"></i>
                        </h3>
                        <p class="text-xs text-stone-500 font-medium mt-0.5">Klik untuk melihat catatan arsip transaksi yang telah selesai.</p>
                    </div>
                </div>

                <div id="content-kembali" class="hidden mt-6 pt-4 border-t border-stone-100">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-4">
                        <div></div>
                        <div class="flex flex-wrap items-center gap-2">
                            <form method="GET" class="flex items-center gap-2">
                                <input type="hidden" name="page_k" value="1">
                                <input type="text" name="search_k" value="<?= htmlspecialchars($search_k); ?>" placeholder="Cari riwayat..." class="px-3 py-1.5 text-xs rounded-lg border border-stone-300 focus:outline-none focus:border-emerald-500">
                                <button type="submit" class="px-3 py-1.5 text-xs font-bold bg-stone-100 text-stone-700 rounded-lg hover:bg-stone-200"><i class="fa-solid fa-search"></i></button>
                            </form>
                            <a href="?download=pdf&type=kembali" class="no-print px-3 py-1.5 rounded-lg text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 hover:bg-emerald-100 transition-colors inline-flex items-center gap-1.5">
                                <i class="fa-solid fa-download"></i> PDF
                            </a>
                        </div>
                    </div>

                    <div class="overflow-x-auto w-full">
                        <table class="w-full text-left border-collapse table-auto">
                            <thead>
                                <tr class="border-b text-[10px] uppercase tracking-widest font-extrabold" style="border-color: var(--pale-slate); color: var(--taupe-grey);">
                                    <th class="pb-3 px-2 w-10 text-center">No</th>
                                    <th class="pb-3 px-3">Peminjam</th>
                                    <th class="pb-3 px-3">Judul Buku</th>
                                    <th class="pb-3 px-2 text-center">Tgl Pinjam</th>
                                    <th class="pb-3 px-2 text-center">Tgl Kembali</th>
                                    <th class="pb-3 px-2 text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y text-xs sm:text-sm font-semibold" style="border-color: var(--pale-slate); color: var(--taupe-grey);">
                                <?php 
                                $no2 = $start_k + 1;
                                if (mysqli_num_rows($query_kembali) > 0):
                                    while ($row = mysqli_fetch_assoc($query_kembali)): 
                                        $tgl_pinjam  = !empty($row['tanggal_pinjam']) ? date('d M Y', strtotime($row['tanggal_pinjam'])) : '-';
                                        $tgl_kembali = !empty($row['tanggal_kembali']) ? date('d M Y', strtotime($row['tanggal_kembali'])) : '-';
                                ?>
                                <tr class="hover:bg-emerald-50/40 transition-colors">
                                    <td class="py-3 px-2 text-center font-bold"><?= $no2++; ?></td>
                                    <td class="py-3 px-3 font-bold break-words"><?= htmlspecialchars($row['nama_anggota'] ?? 'N/A'); ?></td>
                                    <td class="py-3 px-3 italic break-words">"<?= htmlspecialchars($row['judul_buku'] ?? 'N/A'); ?>"</td>
                                    <td class="py-3 px-2 text-center opacity-80 whitespace-nowrap"><?= $tgl_pinjam; ?></td>
                                    <td class="py-3 px-2 text-center opacity-80 whitespace-nowrap"><?= $tgl_kembali; ?></td>
                                    <td class="py-3 px-2 text-center whitespace-nowrap">
                                        <span class="px-2.5 py-1 rounded-xl text-[10px] font-extrabold uppercase tracking-wider bg-emerald-50 text-emerald-700 border border-emerald-200 inline-block">
                                            Dikembalikan
                                        </span>
                                    </td>
                                </tr>
                                <?php endwhile; else: ?>
                                <tr><td colspan="6" class="py-8 text-center text-xs font-semibold italic opacity-60">Belum ada data riwayat peminjaman yang cocok.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <?php if ($total_pages_k > 1): ?>
                    <div class="flex items-center justify-between mt-4 pt-3 border-t border-stone-100 text-xs">
                        <span class="text-stone-500 font-medium">Menampilkan halaman <?= $page_k; ?> dari <?= $total_pages_k; ?></span>
                        <div class="flex gap-1">
                            <?php for($i=1; $i<=$total_pages_k; $i++): ?>
                                <a href="?page_k=<?= $i; ?>&search_k=<?= urlencode($search_k); ?>#section-kembali" class="px-3 py-1 rounded-lg font-bold border <?= ($page_k == $i) ? 'bg-emerald-600 text-white border-emerald-600' : 'bg-white text-stone-600 border-stone-200 hover:bg-stone-50'; ?>">
                                    <?= $i; ?>
                                </a>
                            <?php endfor; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </main>

    <script>
        // Fungsi Accordion
        function toggleAccordion(contentId, iconId) {
            const content = document.getElementById(contentId);
            const icon = document.getElementById(iconId);
            content.classList.toggle('hidden');
            icon.classList.toggle('rotate-180');
        }

        // Otomatis Buka Accordion Berdasarkan Parameter / Hash URL saat Halaman Dimuat
        document.addEventListener("DOMContentLoaded", function() {
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.has('page_b') || urlParams.has('search_b') || window.location.hash === '#section-berlangsung') {
                const contentB = document.getElementById('content-berlangsung');
                const iconB = document.getElementById('icon-berlangsung');
                if (contentB && iconB) {
                    contentB.classList.remove('hidden');
                    iconB.classList.add('rotate-180');
                }
            }

            if (urlParams.has('page_k') || urlParams.has('search_k') || window.location.hash === '#section-kembali') {
                const contentK = document.getElementById('content-kembali');
                const iconK = document.getElementById('icon-kembali');
                if (contentK && iconK) {
                    contentK.classList.remove('hidden');
                    iconK.classList.add('rotate-180');
                }
            }
        });

        const sidebar = document.getElementById('sidebar');
        const toggleSidebarBtn = document.getElementById('toggleSidebar');
        const closeSidebarBtn = document.getElementById('closeSidebar');
        const overlay = document.getElementById('sidebarOverlay');

        toggleSidebarBtn?.addEventListener('click', () => {
            sidebar.classList.remove('-translate-x-full');
            overlay.classList.remove('hidden');
        });
        closeSidebarBtn?.addEventListener('click', () => {
            sidebar.classList.add('-translate-x-full');
            overlay.classList.add('hidden');
        });
        overlay?.addEventListener('click', () => {
            sidebar.classList.add('-translate-x-full');
            overlay.classList.add('hidden');
        });

        const ctxStatus = document.getElementById('statusChart').getContext('2d');
        new Chart(ctxStatus, {
            type: 'doughnut',
            data: {
                labels: ['Berlangsung', 'Dikembalikan'],
                datasets: [{
                    data: [<?= $count_berlangsung; ?>, <?= $count_kembali; ?>],
                    backgroundColor: ['#f59e0b', '#10b981'],
                    borderWidth: 2,
                    borderColor: '#ffffff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                cutout: '70%'
            }
        });

        const ctxBar = document.getElementById('barChart').getContext('2d');
        new Chart(ctxBar, {
            type: 'bar',
            data: {
                labels: ['Sedang Dipinjam', 'Sudah Dikembalikan', 'Total Transaksi'],
                datasets: [{
                    label: 'Jumlah',
                    data: [<?= $count_berlangsung; ?>, <?= $count_kembali; ?>, <?= $total_semua; ?>],
                    backgroundColor: ['#f59e0b', '#10b981', '#DB7F8E'],
                    borderRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
            }
        });

        const ctxBooks = document.getElementById('topBooksChart').getContext('2d');
        new Chart(ctxBooks, {
            type: 'bar',
            data: {
                labels: <?= json_encode($labels_buku); ?>,
                datasets: [{
                    label: 'Jumlah Dipinjam',
                    data: <?= json_encode($data_buku); ?>,
                    backgroundColor: '#604D53',
                    borderRadius: 6
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: { 
                    legend: { display: false } 
                },
                scales: { 
                    x: { 
                        beginAtZero: true, 
                        ticks: { stepSize: 1 } 
                    },
                    y: {
                        ticks: {
                            callback: function(value, index) {
                                let label = this.getLabelForValue(value);
                                return label.length > 18 ? label.substring(0, 16) + '...' : label;
                            }
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>