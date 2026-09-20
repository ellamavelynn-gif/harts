<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit;
}
include 'config/koneksi.php';

$current_page = 'monitoring_denda.php';

// --- FUNGSI HITUNG KETERLAMBATAN & DENDA ---
function getInfoKeterlambatan($tgl_deadline) {
    if (!$tgl_deadline || $tgl_deadline == '0000-00-00' || trim($tgl_deadline) == '') {
        return ['telat_hari' => 0, 'nominal_denda' => 0];
    }
    
    date_default_timezone_set('Asia/Jakarta');
    $time_deadline = strtotime($tgl_deadline);

    if (!$time_deadline) {
        return ['telat_hari' => 0, 'nominal_denda' => 0];
    }

    $tgl_sekarang = new DateTime(date('Y-m-d')); 
    $tgl_tenggat  = new DateTime(date('Y-m-d', $time_deadline));
    
    if ($tgl_sekarang > $tgl_tenggat) {
        $selisih = $tgl_tenggat->diff($tgl_sekarang);
        $telat_hari = (int)$selisih->days; // Menggunakan ->days agar perhitungan jumlah hari tepat
        if ($telat_hari > 0) {
            $nominal_denda = $telat_hari * 5000; // Rp 5.000 / hari
            return ['telat_hari' => $telat_hari, 'nominal_denda' => $nominal_denda];
        }
    }
    return ['telat_hari' => 0, 'nominal_denda' => 0];
}

$admin_data = $_SESSION['admin'];
$admin_name = $admin_data['nama_petugas'] ?? "Admin";
$initial    = strtoupper(substr($admin_name, 0, 1));

// QUERY: Ambil semua peminjaman yang statusnya dipinjam
$query_pinjam = mysqli_query($conn, "SELECT peminjaman.*, 
    anggota.*, 
    buku.judul_buku 
    FROM peminjaman 
    LEFT JOIN anggota ON peminjaman.id_anggota = anggota.id_anggota 
    LEFT JOIN buku ON peminjaman.id_buku = buku.id_buku 
    WHERE LOWER(TRIM(peminjaman.status)) = 'dipinjam'");

if (!$query_pinjam) {
    die("SQL Error (monitoring_denda.php): " . mysqli_error($conn));
}

$list_denda = [];
$total_denda_keseluruhan = 0;

if ($query_pinjam) {
    while ($row = mysqli_fetch_assoc($query_pinjam)) {
        $tgl_tenggat = $row['tgl_kembali'] ?? $row['tanggal_kembali'] ?? '';
        $info_denda = getInfoKeterlambatan($tgl_tenggat);

        // Hanya masukkan ke daftar jika TELAT MINIMAL 1 HARI
        if ($info_denda['telat_hari'] >= 1) {
            $row['telat_hari'] = $info_denda['telat_hari'];
            $row['nominal_denda'] = $info_denda['nominal_denda'];
            
            $kemungkinan_kolom_hp = ['no_telepon', 'no_telp', 'nohp', 'no_hp', 'telepon', 'hp'];
            $no_hp_raw = '';
            foreach ($kemungkinan_kolom_hp as $kolom) {
                if (!empty($row[$kolom])) {
                    $no_hp_raw = $row[$kolom];
                    break;
                }
            }

            $no_hp = trim($no_hp_raw);
            if (substr($no_hp, 0, 1) === '0') {
                $no_hp = '62' . substr($no_hp, 1);
            }
            $row['no_hp_wa'] = $no_hp;

            $list_denda[] = $row;
            $total_denda_keseluruhan += $info_denda['nominal_denda'];
        }
    }
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
    <title>Monitoring Denda - HARTS Admin</title>
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
            padding-left: env(safe-area-inset-left);
            padding-right: env(safe-area-inset-right);
        }

        aside#sidebar {
            padding-top: calc(1.5rem + env(safe-area-inset-top));
            padding-bottom: calc(1.5rem + env(safe-area-inset-bottom));
        }

        main {
            padding-bottom: calc(1rem + env(safe-area-inset-bottom));
        }

        /* Wrapper tabel: scroll horizontal mulus di iOS/Safari & Android lama */
        .table-scroll {
            -webkit-overflow-scrolling: touch;
            scrollbar-width: thin;
        }

        /* Card view untuk layar sangat kecil (pengganti tabel) */
        .denda-card {
            background-color: #ffffff;
            border: 1px solid var(--pale-slate);
            box-shadow: 0 6px 16px -4px rgba(96, 77, 83, 0.06);
        }

        button, a { -webkit-tap-highlight-color: transparent; }

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
<body class="min-h-screen flex flex-col lg:flex-row antialiased overflow-x-hidden">

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
            <a href="monitoring_denda.php" class="nav-item flex items-center px-4 py-2.5 rounded-xl text-sm font-medium nav-active">
                <i class="fa-solid fa-clock-rotate-left w-6 text-center text-sm mr-2.5"></i> Monitoring Denda
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
            <a href="laporan.php" class="nav-item flex items-center px-4 py-2.5 rounded-xl text-sm font-medium">
                <i class="fa-solid fa-chart-pie w-6 text-center text-sm mr-2.5"></i> Laporan Utama
            </a>
        </nav>
        
        <a href="logout.php" class="px-4 py-3 text-white/90 hover:text-white font-bold flex items-center hover:bg-white/10 rounded-xl mt-auto transition-colors text-sm">
            <i class="fa-solid fa-arrow-right-from-bracket mr-2.5 text-center w-6"></i> Keluar
        </a>
    </aside>

    <!-- Main Content Area -->
    <main class="flex-1 h-full overflow-y-auto p-4 sm:p-6 lg:p-10">
        <div class="max-w-7xl mx-auto">
            
            <!-- Page Header & Hamburger Trigger -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-xl sm:text-2xl lg:text-3xl font-extrabold tracking-tight" style="color: var(--taupe-grey);">
                            Monitoring Keterlambatan <i class="fa-solid fa-triangle-exclamation text-lg sm:text-xl ml-1" style="color: var(--old-rose);"></i>
                        </h2>
                        <p class="text-xs sm:text-sm font-medium opacity-80 mt-1" style="color: var(--taupe-grey);">Daftar peminjaman yang telah melewati batas tenggat waktu.</p>
                    </div>
                    <!-- Hamburger Button untuk Layar Kecil -->
                    <button onclick="toggleSidebar()" class="lg:hidden p-2.5 rounded-2xl bg-white shadow-sm border border-stone-200 text-stone-700 hover:bg-stone-50 shrink-0">
                        <i class="fa-solid fa-bars text-lg"></i>
                    </button>
                </div>
                <div class="px-4 py-2 rounded-2xl font-extrabold text-xs uppercase tracking-wider border flex items-center gap-2 shadow-sm w-fit bg-white" style="border-color: var(--pale-slate); color: var(--taupe-grey);">
                    <i class="fa-solid fa-users" style="color: var(--old-rose);"></i>
                    <span>Total Telat: <b><?= count($list_denda); ?> Anggota</b></span>
                </div>
            </div>

            <!-- Banner Info Alur Keuangan -->
            <div class="p-4 rounded-2xl bg-amber-50 border border-amber-200 text-amber-900 text-xs font-semibold mb-6 flex items-start gap-3 shadow-sm">
                <i class="fa-solid fa-circle-info text-amber-600 text-base mt-0.5 shrink-0"></i>
                <div>
                    <b>Informasi Estimasi Denda:</b> Nominal denda di bawah ini adalah estimasi yang terus berjalan per hari (Rp 5.000/hari). Nominal ini <u>belum dicatat ke Kas</u>. Daftar ini otomatis diambil dari data yang sama dengan <b>Peminjaman Aktif</b> (khusus yang sudah telat) — proses "Kembali" di sini akan langsung menghapus data dari kedua halaman.
                </div>
            </div>

            <!-- Card Area Tabel Responsive dengan Wrapper Flex/Grid Alternatif untuk Mobile atau Standard Responsive Table -->
            <div class="card-custom rounded-2xl sm:rounded-3xl p-3 sm:p-6 lg:p-8">
                
                <!-- ================= MOBILE CARD VIEW (< sm) ================= -->
                <div class="sm:hidden space-y-3">
                    <?php if (count($list_denda) > 0): ?>
                        <?php foreach ($list_denda as $item):
                            $nama_siswa  = $item['nama_anggota'] ?? 'Anggota';
                            $judul_buku  = $item['judul_buku'] ?? 'Buku';
                            $telat       = $item['telat_hari'];
                            $nominal     = $item['nominal_denda'];
                            $jumlah_buku = $item['jumlah'] ?? 1;
                            $kode_trans  = $item['kode_transaksi'] ?? '';

                            $teks_wa = "Halo *{$nama_siswa}*, kami dari Perpustakaan HARTS.\n\nMau mengingatkan bahwa peminjaman buku *'{$judul_buku}'* ({$jumlah_buku} buku) telah terlambat selama *{$telat} hari*.\n\nNominal denda keterlambatan saat ini: *Rp " . number_format($nominal, 0, ',', '.') . "*.\nMohon segera mengembalikan buku ke perpustakaan. Terima kasih!";
                            $url_wa  = "https://api.whatsapp.com/send?phone=" . $item['no_hp_wa'] . "&text=" . urlencode($teks_wa);
                        ?>
                        <?php $initial_anggota = strtoupper(substr($nama_siswa, 0, 1)); ?>
                        <div class="denda-card relative rounded-3xl p-4 pl-5 pt-5 overflow-hidden">
                            <!-- Aksen warna kiri sesuai level keterlambatan -->
                            <div class="absolute inset-y-0 left-0 w-1.5" style="background: linear-gradient(180deg, var(--old-rose), var(--soft-blush));"></div>

                            <!-- Badge hari telat, nempel di pojok kanan atas kartu -->
                            <span class="absolute top-3 right-3 flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-extrabold uppercase tracking-wider bg-red-600 text-white shadow-sm">
                                <i class="fa-solid fa-clock text-[9px]"></i> <?= $telat; ?> Hari
                            </span>

                            <div class="flex items-center gap-3 pr-16">
                                <div class="w-11 h-11 shrink-0 rounded-2xl flex items-center justify-center font-extrabold text-base text-white shadow-sm" style="background-color: var(--old-rose);">
                                    <?= htmlspecialchars($initial_anggota); ?>
                                </div>
                                <div class="min-w-0">
                                    <p class="font-extrabold text-sm truncate" style="color: var(--taupe-grey);"><?= htmlspecialchars($nama_siswa); ?></p>
                                    <p class="text-xs font-medium opacity-70 truncate mt-0.5">
                                        <i class="fa-solid fa-book text-[10px] mr-1"></i>"<?= htmlspecialchars($judul_buku); ?>" &middot; <?= $jumlah_buku; ?> Buku
                                    </p>
                                </div>
                            </div>

                            <div class="flex items-center justify-between mt-4 px-3.5 py-3 rounded-2xl" style="background-color: var(--soft-blush);">
                                <span class="text-[10px] font-extrabold uppercase tracking-widest opacity-70">Estimasi Denda</span>
                                <span class="text-lg font-extrabold" style="color: var(--old-rose);">
                                    Rp <?= number_format($nominal, 0, ',', '.'); ?>
                                </span>
                            </div>

                            <div class="grid grid-cols-2 gap-2 mt-3">
                                <button onclick="inputDenda('<?= $kode_trans; ?>', <?= (int)$nominal; ?>)"
                                        class="inline-flex items-center justify-center gap-1.5 px-2 py-3 text-white font-extrabold text-[11px] rounded-2xl bg-red-600 active:bg-red-700 shadow-sm transition-all uppercase tracking-wide">
                                    <i class="fa-solid fa-triangle-exclamation"></i> Kembali
                                </button>
                                <a href="<?= $url_wa; ?>" target="_blank" rel="noopener"
                                   class="inline-flex items-center justify-center gap-1.5 px-2 py-3 bg-emerald-600 active:bg-emerald-700 text-white rounded-2xl font-extrabold text-[11px] shadow-sm uppercase tracking-wide">
                                    <i class="fa-brands fa-whatsapp"></i> WA Ortu
                                </a>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="py-12 text-center text-sm font-semibold italic opacity-60">
                            Tidak ada anggota yang terlambat mengembalikan buku.
                        </div>
                    <?php endif; ?>
                </div>

                <!-- ================= DESKTOP / TABLET TABLE VIEW (>= sm) ================= -->
                <div class="hidden sm:block w-full overflow-x-auto table-scroll">
                    <table class="w-full text-left border-collapse min-w-[700px]">
                        <thead>
                            <tr class="border-b text-[11px] uppercase tracking-widest font-extrabold" style="border-color: var(--pale-slate); color: var(--taupe-grey);">
                                <th class="pb-4 px-3 sm:px-4">Nama Anggota</th>
                                <th class="pb-4 px-3 sm:px-4">Judul Buku</th>
                                <th class="pb-4 px-3 sm:px-4 text-center">Jumlah</th>
                                <th class="pb-4 px-3 sm:px-4 text-center">Telat Hari</th>
                                <th class="pb-4 px-3 sm:px-4 text-center">Estimasi Denda</th>
                                <th class="pb-4 px-3 sm:px-4 text-center">Tindakan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y text-xs sm:text-sm font-semibold" style="border-color: var(--pale-slate); color: var(--taupe-grey);">
                            <?php if (count($list_denda) > 0): ?>
                                <?php foreach ($list_denda as $item): 
                                    $nama_siswa  = $item['nama_anggota'] ?? 'Anggota';
                                    $judul_buku  = $item['judul_buku'] ?? 'Buku';
                                    $telat       = $item['telat_hari'];
                                    $nominal     = $item['nominal_denda'];
                                    $jumlah_buku = $item['jumlah'] ?? 1;
                                    $kode_trans  = $item['kode_transaksi'] ?? '';

                                    $teks_wa = "Halo *{$nama_siswa}*, kami dari Perpustakaan HARTS.\n\nMau mengingatkan bahwa peminjaman buku *'{$judul_buku}'* ({$jumlah_buku} buku) telah terlambat selama *{$telat} hari*.\n\nNominal denda keterlambatan saat ini: *Rp " . number_format($nominal, 0, ',', '.') . "*.\nMohon segera mengembalikan buku ke perpustakaan. Terima kasih!";
                                    $url_wa  = "https://api.whatsapp.com/send?phone=" . $item['no_hp_wa'] . "&text=" . urlencode($teks_wa);
                                ?>
                                <tr class="hover:bg-stone-50/80 transition-colors">
                                    <td class="py-4 px-3 sm:px-4 font-bold whitespace-nowrap"><?= htmlspecialchars($nama_siswa); ?></td>
                                    <td class="py-4 px-3 sm:px-4 font-semibold max-w-xs truncate">"<?= htmlspecialchars($judul_buku); ?>"</td>
                                    <td class="py-4 px-3 sm:px-4 text-center font-bold whitespace-nowrap" style="color: var(--taupe-grey);"><?= $jumlah_buku; ?> Buku</td>
                                    <td class="py-4 px-3 sm:px-4 text-center whitespace-nowrap">
                                        <span class="px-2.5 py-1 rounded-xl text-[10px] font-extrabold uppercase tracking-wider bg-red-50 text-red-600 border border-red-200 inline-block shadow-xs">
                                            <?= $telat; ?> Hari
                                        </span>
                                    </td>
                                    <td class="py-4 px-3 sm:px-4 text-center font-extrabold text-sm sm:text-base whitespace-nowrap" style="color: var(--old-rose);">
                                        Rp <?= number_format($nominal, 0, ',', '.'); ?>
                                    </td>
                                    <td class="py-4 px-3 sm:px-4 text-center whitespace-nowrap">
                                        <div class="flex items-center justify-center gap-1.5">
                                            <button onclick="inputDenda('<?= $kode_trans; ?>', <?= (int)$nominal; ?>)" 
                                                    class="inline-flex items-center justify-center px-3 py-2 text-red-600 font-extrabold text-xs rounded-xl border border-red-200 bg-red-50 hover:bg-red-100 transition-all uppercase tracking-wider shadow-xs">
                                                <i class="fa-solid fa-triangle-exclamation mr-1"></i> Kembali &amp; Denda
                                            </button>

                                            <a href="<?= $url_wa; ?>" target="_blank" class="inline-flex items-center justify-center px-3 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-bold text-xs shadow-xs transition-all uppercase tracking-wider">
                                                <i class="fa-brands fa-whatsapp text-sm mr-1"></i>
                                                <span>WA</span>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="py-12 text-center text-sm font-semibold italic opacity-60">
                                        Tidak ada anggota yang terlambat mengembalikan buku.
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

    function inputDenda(id, estimasiDenda) {
        Swal.fire({
            title: 'Kembalikan & Catat Denda',
            html: `
                <p class="text-xs text-stone-500 mb-4 text-left">Nominal denda sudah diisi otomatis dari estimasi keterlambatan. Ubah kalau perlu (misalnya ada tambahan denda kerusakan).</p>
                <div class="text-left space-y-3">
                    <div>
                        <label class="text-[10px] font-bold uppercase tracking-wider text-stone-600">Nominal Denda (Rp)</label>
                        <input id="swal-denda" type="number" min="0" value="${estimasiDenda || 0}" class="w-full mt-1 px-4 py-2.5 border rounded-xl text-stone-800 font-bold focus:outline-none focus:ring-2 focus:ring-[#DB7F8E]">
                    </div>
                    <div>
                        <label class="text-[10px] font-bold uppercase tracking-wider text-stone-600">Keterangan / Alasan Denda</label>
                        <textarea id="swal-ket" placeholder="Contoh: Telat 5 hari / Cover buku sobek" class="w-full mt-1 px-4 py-2.5 border rounded-xl text-stone-800 text-sm focus:outline-none focus:ring-2 focus:ring-[#DB7F8E]" rows="3"></textarea>
                    </div>
                </div>
            `,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#DB7F8E',
            cancelButtonColor: '#9DA3A4',
            confirmButtonText: 'Proses Denda',
            cancelButtonText: 'Batal',
            customClass: {
                popup: 'rounded-3xl p-4 sm:p-6'
            },
            preConfirm: () => {
                const denda = document.getElementById('swal-denda').value;
                const ket = document.getElementById('swal-ket').value;
                if (denda === '' || denda < 0) {
                    Swal.showValidationMessage('Harap masukkan nominal denda yang valid!');
                    return false;
                }
                return { denda: denda, keterangan: ket };
            }
        }).then((result) => {
            if (result.isConfirmed) {
                const denda = result.value.denda;
                const ket = encodeURIComponent(result.value.keterangan);
                window.location.href = `proses_kembali.php?id=${id}&denda=${denda}&ket=${ket}`;
            }
        });
    }
    </script>
</body>
</html>