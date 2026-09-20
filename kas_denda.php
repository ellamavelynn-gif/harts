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

// --- LOGIKA TOTAL SALDO & STATISTIK ---
$query_total = mysqli_query($conn, "
    SELECT SUM(
        CASE 
            WHEN status_bayar = 'Keluar' THEN -ABS(nominal) 
            ELSE ABS(nominal) 
        END
    ) as total 
    FROM kas_denda 
    WHERE status_bayar IN ('Lunas', 'Keluar')
");
$data_kas = mysqli_fetch_assoc($query_total);
$total_kas = $data_kas['total'] ?? 0;

$q_masuk = mysqli_query($conn, "SELECT SUM(nominal) as total FROM kas_denda WHERE status_bayar = 'Lunas'");
$total_pemasukan = mysqli_fetch_assoc($q_masuk)['total'] ?? 0;

$q_keluar = mysqli_query($conn, "SELECT SUM(nominal) as total FROM kas_denda WHERE status_bayar = 'Keluar'");
$total_pengeluaran = mysqli_fetch_assoc($q_keluar)['total'] ?? 0;

// --- LOGIKA 1: BAYAR DENDA ---
if (isset($_POST['bayar_denda'])) {
    $id_bayar = intval($_POST['id_denda']); 
    
    $stmt = $conn->prepare("UPDATE kas_denda SET status_bayar = 'Lunas', tanggal_bayar = NOW() WHERE id_kas = ?");
    $stmt->bind_param("i", $id_bayar);
    
    if ($stmt->execute()) {
        $stmt->close();
        header("Location: kas_denda.php?status=sukses&cetak_id=" . $id_bayar);
        exit;
    } else {
        die("Gagal memproses pembayaran: " . $conn->error);
    }
}

// --- LOGIKA 2: TARIK SALDO ---
if (isset($_POST['tarik_saldo'])) {
    $nominal = floatval($_POST['nominal']);
    $keterangan = trim($_POST['keterangan']);
    
    if ($nominal <= 0) {
        echo "<script>alert('Nominal penarikan tidak valid!'); window.location='kas_denda.php';</script>";
        exit;
    }

    if ($nominal > $total_kas) {
        echo "<script>alert('Gagal! Nominal penarikan melebihi total kas saat ini.'); window.location='kas_denda.php';</script>";
        exit;
    }
    
    $stmt = $conn->prepare("INSERT INTO kas_denda (id_anggota, nominal, tanggal_bayar, keterangan, status_bayar) VALUES (NULL, ?, NOW(), ?, 'Keluar')");
    $stmt->bind_param("ds", $nominal, $keterangan);
    
    if ($stmt->execute()) {
        $stmt->close();
        header("Location: kas_denda.php?status=tarik_sukses");
        exit;
    } else {
        die("Gagal Tarik Saldo: " . $conn->error);
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kas Denda - HARTS Admin</title>
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

        .card-custom { 
            background-color: #ffffff; 
            border: 1px solid var(--pale-slate); 
            box-shadow: 0 10px 25px -5px rgba(96, 77, 83, 0.04);
        }

        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-thumb { background: var(--old-rose); border-radius: 10px; }

        @media print {
            body * { visibility: hidden; }
            #areaStruk, #areaStruk * { visibility: visible; }
            #areaStruk { position: absolute; left: 0; top: 0; width: 100%; }
        }
    </style>
</head>
<body class="flex flex-col lg:flex-row min-h-screen bg-stone-100 overflow-x-hidden">

    <!-- Overlay Sidebar Mobile -->
    <div id="sidebarOverlay" onclick="toggleSidebar()" class="fixed inset-0 bg-stone-900/50 z-40 hidden lg:hidden backdrop-blur-sm transition-opacity"></div>

    <!-- Sidebar Menu -->
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
            <button onclick="toggleSidebar()" class="lg:hidden text-white/80 hover:text-white p-1">
                <i class="fa-solid fa-xmark text-xl"></i>
            </button>
        </div>
        
        <a href="profile.php" class="p-3.5 rounded-2xl mb-6 flex items-center transition-all group border border-white/20 bg-white/10 hover:bg-white/20">
            <div class="w-9 h-9 rounded-xl flex items-center justify-center font-bold text-sm mr-3 text-white shadow-sm shrink-0" style="background-color: var(--old-rose);">
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
            <a href="kas_denda.php" class="nav-item flex items-center px-4 py-2.5 rounded-xl text-sm font-medium nav-active">
                <i class="fa-solid fa-wallet w-6 text-center text-sm mr-2.5"></i> Kas Denda
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
    <main class="flex-1 w-full min-w-0 p-3 sm:p-6 lg:p-10" style="background-color: var(--soft-blush);">
        <div class="max-w-6xl mx-auto">
            
            <!-- Header & Action Button -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
                <div>
                    <h2 class="text-xl sm:text-2xl lg:text-3xl font-extrabold tracking-tight" style="color: var(--taupe-grey);">Kas Denda</h2>
                    <p class="mt-1 text-xs sm:text-sm font-medium opacity-80" style="color: var(--taupe-grey);">Manajemen dan rekapitulasi keuangan denda keterlambatan.</p>
                </div>
                <div class="flex items-center gap-2 w-full sm:w-auto">
                    <button onclick="toggleSidebar()" class="lg:hidden p-2.5 rounded-2xl bg-white shadow-sm border border-stone-200 text-stone-700 shrink-0">
                        <i class="fa-solid fa-bars text-lg"></i>
                    </button>
                    <button onclick="openModal('modalTarik')" class="flex-1 sm:flex-none px-4 py-2.5 text-white font-bold text-xs sm:text-sm rounded-2xl shadow-sm hover:opacity-90 transition-all flex items-center justify-center gap-2" style="background-color: var(--old-rose);">
                        <i class="fa-solid fa-money-bill-transfer"></i> Tarik Saldo
                    </button>
                </div>
            </div>

            <!-- Grid Statistik (2 Kolom di HP, 4 Kolom di Laptop - Dijamin Rapi) -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-2.5 sm:gap-4 mb-6">
                <!-- Saldo Kas Riil -->
                <div class="p-3 sm:p-5 rounded-2xl sm:rounded-3xl text-white shadow-sm flex flex-col justify-between gap-2 overflow-hidden" style="background-color: var(--taupe-grey);">
                    <div class="flex items-center justify-between">
                        <p class="text-[9px] sm:text-[10px] font-extrabold uppercase tracking-widest opacity-70 truncate">Kas Riil</p>
                        <div class="w-6 h-6 sm:w-10 sm:h-10 rounded-lg sm:rounded-xl flex items-center justify-center text-[10px] sm:text-lg text-white/40 border border-white/10 shrink-0" style="background-color: rgba(255,255,255,0.08);">
                            <i class="fa-solid fa-vault"></i>
                        </div>
                    </div>
                    <h3 class="text-xs sm:text-lg lg:text-xl font-black truncate">Rp <?= number_format($total_kas, 0, ',', '.'); ?></h3>
                </div>

                <!-- Total Pemasukan -->
                <div class="p-3 sm:p-5 rounded-2xl sm:rounded-3xl bg-white shadow-sm border flex flex-col justify-between gap-2 overflow-hidden" style="border-color: var(--pale-slate);">
                    <div class="flex items-center justify-between">
                        <p class="text-[9px] sm:text-[10px] font-extrabold uppercase tracking-widest opacity-60 truncate" style="color: var(--taupe-grey);">Pemasukan</p>
                        <div class="w-6 h-6 sm:w-10 sm:h-10 rounded-lg sm:rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-[10px] sm:text-lg border border-emerald-100 shrink-0">
                            <i class="fa-solid fa-arrow-down-left"></i>
                        </div>
                    </div>
                    <h3 class="text-xs sm:text-lg lg:text-xl font-black text-emerald-600 truncate">+Rp <?= number_format($total_pemasukan, 0, ',', '.'); ?></h3>
                </div>

                <!-- Total Ditarik -->
                <div class="p-3 sm:p-5 rounded-2xl sm:rounded-3xl bg-white shadow-sm border flex flex-col justify-between gap-2 overflow-hidden" style="border-color: var(--pale-slate);">
                    <div class="flex items-center justify-between">
                        <p class="text-[9px] sm:text-[10px] font-extrabold uppercase tracking-widest opacity-60 truncate" style="color: var(--taupe-grey);">Ditarik</p>
                        <div class="w-6 h-6 sm:w-10 sm:h-10 rounded-lg sm:rounded-xl flex items-center justify-center text-[10px] sm:text-lg border shrink-0" style="background-color: #FFF0F0; color: var(--old-rose); border-color: var(--soft-blush);">
                            <i class="fa-solid fa-arrow-up-right"></i>
                        </div>
                    </div>
                    <h3 class="text-xs sm:text-lg lg:text-xl font-black truncate" style="color: var(--old-rose);">-Rp <?= number_format($total_pengeluaran, 0, ',', '.'); ?></h3>
                </div>

                <!-- Laporan Keuangan (Tombol PDF & Excel) -->
                <div class="p-3 sm:p-5 rounded-2xl sm:rounded-3xl bg-white shadow-sm border flex flex-col justify-between gap-2 overflow-hidden" style="border-color: var(--pale-slate);">
                    <p class="text-[9px] sm:text-[10px] font-extrabold uppercase tracking-widest opacity-60 truncate" style="color: var(--taupe-grey);">Laporan</p>
                    <div class="grid grid-cols-2 gap-1">
                        <button onclick="window.print()" class="py-1.5 px-1 rounded-lg sm:rounded-xl font-bold text-[10px] sm:text-xs bg-stone-100 hover:bg-stone-200 border border-stone-300 transition-all flex items-center justify-center gap-1" style="color: var(--taupe-grey);">
                            <i class="fa-solid fa-file-pdf text-red-500"></i> PDF
                        </button>
                        <button onclick="exportToExcel()" class="py-1.5 px-1 rounded-lg sm:rounded-xl font-bold text-[10px] sm:text-xs bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200 transition-all flex items-center justify-center gap-1">
                            <i class="fa-solid fa-file-excel text-emerald-600"></i> Excel
                        </button>
                    </div>
                </div>
            </div>

            <!-- Bagian Tabel / Daftar Transaksi -->
            <div class="card-custom p-4 sm:p-6 rounded-3xl mb-8">
                <div class="flex items-center mb-4 sm:mb-6">
                    <div class="w-1.5 h-5 rounded-full mr-3 shrink-0" style="background-color: var(--old-rose);"></div>
                    <h3 class="text-base sm:text-lg font-bold tracking-tight" style="color: var(--taupe-grey);">Riwayat Pemasukan & Pengeluaran</h3>
                </div>

                <?php
                $query = mysqli_query($conn, "SELECT kd.*, a.nama_anggota FROM kas_denda kd LEFT JOIN anggota a ON kd.id_anggota = a.id_anggota ORDER BY kd.tanggal_bayar DESC");
                if(mysqli_num_rows($query) > 0) :
                ?>
                
                <!-- TAMPILAN MOBILE: Model Card List Vertikal -->
                <div class="block md:hidden space-y-3">
                    <?php 
                    mysqli_data_seek($query, 0);
                    while($row = mysqli_fetch_assoc($query)) :
                        $nom = $row['nominal'] ?? 0;
                        $status = $row['status_bayar'] ?? 'Belum Lunas';
                        $is_lunas = ($status == 'Lunas');
                        $is_keluar = ($status == 'Keluar' || trim($status) == ''); 
                        $js_id = $row['id_kas'] ?? 0; 
                        $js_nama = htmlspecialchars(addslashes(trim($row['nama_anggota'] ?? $row['keterangan'] ?? 'ADMIN')));
                        $tgl_fmt = date('d M Y, H:i', strtotime($row['tanggal_bayar']));
                    ?>
                    <div class="p-4 rounded-2xl bg-stone-50 border border-stone-200 flex flex-col gap-3">
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <h4 class="font-bold text-sm text-stone-800"><?= htmlspecialchars($row['nama_anggota'] ?? $row['keterangan'] ?? 'TANPA NAMA'); ?></h4>
                                <p class="text-[11px] text-stone-500 mt-0.5"><?= $tgl_fmt; ?> WIB</p>
                            </div>
                            <div>
                                <?php if($is_lunas): ?>
                                    <span class="inline-block px-2.5 py-1 rounded-xl text-[9px] font-extrabold uppercase bg-emerald-50 text-emerald-700 border border-emerald-200">Lunas</span>
                                <?php elseif($is_keluar): ?>
                                    <span class="inline-block px-2.5 py-1 rounded-xl text-[9px] font-extrabold uppercase text-white" style="background-color: var(--old-rose);">Keluar</span>
                                <?php else: ?>
                                    <span class="inline-block px-2.5 py-1 rounded-xl text-[9px] font-extrabold uppercase bg-amber-50 text-amber-700 border border-amber-200">Belum Lunas</span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="flex items-center justify-between pt-2 border-t border-stone-200/60">
                            <div>
                                <span class="text-[10px] uppercase font-bold text-stone-400 block">Nominal</span>
                                <span class="font-black text-sm" style="color: <?= $is_keluar ? 'var(--old-rose)' : 'var(--taupe-grey)'; ?>">
                                    <?= ($is_keluar ? '-' : '+') ?> Rp <?= number_format(abs($nom), 0, ',', '.'); ?>
                                </span>
                            </div>
                            <div>
                                <?php if ($status == 'Belum Lunas') : ?>
                                    <button onclick="openModalBayar('<?= $js_id ?>', '<?= $js_nama ?>', <?= abs($nom) ?>)" class="text-white px-3 py-1.5 rounded-xl text-xs font-bold shadow-sm" style="background-color: var(--taupe-grey);">
                                        Bayar
                                    </button>
                                <?php elseif ($is_lunas) : ?>
                                    <button onclick="cetakStruk('<?= $js_id ?>', '<?= $js_nama ?>', <?= abs($nom) ?>, '<?= $tgl_fmt ?>', 'Denda Keterlambatan Buku')" class="px-3 py-1.5 rounded-xl text-xs font-bold bg-white text-stone-700 border border-stone-300 shadow-sm inline-flex items-center gap-1">
                                        <i class="fa-solid fa-receipt text-xs"></i> Struk
                                    </button>
                                <?php else : ?>
                                    <span class="text-xs italic font-semibold text-stone-400">Penarikan</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>

                <!-- TAMPILAN DESKTOP & TABLET: Tabel Standar -->
                <div class="hidden md:block w-full overflow-x-auto">
                    <table class="w-full text-left border-collapse min-w-[650px]" id="tabelKas">
                        <thead>
                            <tr class="border-b text-[11px] uppercase tracking-wider opacity-60" style="border-color: var(--pale-slate); color: var(--taupe-grey);">
                                <th class="pb-3.5 font-extrabold">Siswa / Keterangan</th>
                                <th class="pb-3.5 font-extrabold text-center">Status</th>
                                <th class="pb-3.5 font-extrabold text-center">Nominal</th>
                                <th class="pb-3.5 font-extrabold text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y" style="border-color: var(--pale-slate);">
                            <?php 
                            mysqli_data_seek($query, 0);
                            while($row = mysqli_fetch_assoc($query)) :
                                $nom = $row['nominal'] ?? 0;
                                $status = $row['status_bayar'] ?? 'Belum Lunas';
                                $is_lunas = ($status == 'Lunas');
                                $is_keluar = ($status == 'Keluar' || trim($status) == ''); 
                                
                                $js_id = $row['id_kas'] ?? 0; 
                                $js_nama = htmlspecialchars(addslashes(trim($row['nama_anggota'] ?? $row['keterangan'] ?? 'ADMIN')));
                                $tgl_fmt = date('d M Y, H:i', strtotime($row['tanggal_bayar']));
                            ?>
                            <tr class="hover:bg-stone-50/50 transition-colors">
                                <td class="py-3.5">
                                    <div class="font-bold text-sm" style="color: var(--taupe-grey);"><?= htmlspecialchars($row['nama_anggota'] ?? $row['keterangan'] ?? 'TANPA NAMA'); ?></div>
                                    <div class="text-[11px] font-semibold opacity-60 mt-0.5" style="color: var(--taupe-grey);"><?= $tgl_fmt; ?> WIB</div>
                                </td>
                                <td class="py-3.5 text-center">
                                    <?php if($is_lunas): ?>
                                        <span class="inline-block px-2.5 py-1 rounded-xl text-[10px] font-extrabold uppercase bg-emerald-50 text-emerald-700 border border-emerald-200">Lunas</span>
                                    <?php elseif($is_keluar): ?>
                                        <span class="inline-block px-2.5 py-1 rounded-xl text-[10px] font-extrabold uppercase text-white" style="background-color: var(--old-rose);">Keluar</span>
                                    <?php else: ?>
                                        <span class="inline-block px-2.5 py-1 rounded-xl text-[10px] font-extrabold uppercase bg-amber-50 text-amber-700 border border-amber-200">Belum Lunas</span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3.5 text-center font-extrabold text-sm whitespace-nowrap">
                                    <span style="color: <?= $is_keluar ? 'var(--old-rose)' : 'var(--taupe-grey)'; ?>">
                                        <?= ($is_keluar ? '-' : '+') ?> Rp <?= number_format(abs($nom), 0, ',', '.'); ?>
                                    </span>
                                </td>
                                <td class="py-3.5 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <?php if ($status == 'Belum Lunas') : ?>
                                            <button onclick="openModalBayar('<?= $js_id ?>', '<?= $js_nama ?>', <?= abs($nom) ?>)" class="text-white px-3 py-1.5 rounded-xl text-xs font-bold shadow-sm transition-opacity hover:opacity-90 whitespace-nowrap" style="background-color: var(--taupe-grey);">
                                                Bayar
                                            </button>
                                        <?php elseif ($is_lunas) : ?>
                                            <button onclick="cetakStruk('<?= $js_id ?>', '<?= $js_nama ?>', <?= abs($nom) ?>, '<?= $tgl_fmt ?>', 'Denda Keterlambatan Buku')" class="px-2.5 py-1.5 rounded-xl text-xs font-bold bg-stone-100 hover:bg-stone-200 text-stone-700 border border-stone-300 transition-all inline-flex items-center gap-1 whitespace-nowrap">
                                                <i class="fa-solid fa-receipt text-xs"></i> Struk
                                            </button>
                                        <?php else : ?>
                                            <span class="opacity-60 italic text-xs font-semibold whitespace-nowrap" style="color: var(--taupe-grey);">Penarikan</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>

                <?php else: ?>
                    <div class="text-center py-12 text-xs lg:text-sm italic opacity-60" style="color: var(--taupe-grey);">
                        <i class="fa-solid fa-wallet text-2xl mb-2 block" style="color: var(--old-rose);"></i> Belum ada riwayat transaksi kas denda.
                    </div>
                <?php endif; ?>
            </div>

        </div>
    </main>

    <!-- Modal Pelunasan -->
    <div id="modalBayar" class="hidden fixed inset-0 bg-stone-900/40 backdrop-blur-sm z-[100] items-center justify-center p-4 overflow-y-auto">
        <div class="bg-white p-6 rounded-3xl w-full max-w-md shadow-xl border my-auto" style="border-color: var(--pale-slate);">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-base sm:text-lg font-bold" style="color: var(--taupe-grey);">Pelunasan Denda</h3>
                <button onclick="closeModal('modalBayar')" class="text-stone-400 hover:text-stone-600 p-1"><i class="fa-solid fa-xmark text-lg"></i></button>
            </div>
            <p class="text-xs font-medium opacity-70 mb-4" style="color: var(--taupe-grey);">Konfirmasi pembayaran denda atas nama:</p>
            <div class="p-3.5 rounded-2xl mb-6 font-extrabold text-sm text-center truncate" style="background-color: var(--soft-blush); color: var(--taupe-grey);" id="labelSiswa">Nama Siswa</div>
            
            <form action="" method="POST" class="space-y-4">
                <input type="hidden" name="id_denda" id="input_id_denda">
                <div>
                    <label class="block text-[11px] font-bold uppercase tracking-wider mb-2 opacity-70" style="color: var(--taupe-grey);">Nominal Denda (Rp)</label>
                    <input type="number" name="nominal_bayar" id="input_nominal" required readonly class="w-full p-3.5 rounded-2xl bg-stone-50 border font-extrabold text-xl text-center outline-none" style="border-color: var(--pale-slate); color: var(--taupe-grey);">
                </div>
                <div class="flex gap-3 pt-2">
                    <button type="button" onclick="closeModal('modalBayar')" class="flex-1 py-3 font-bold text-xs rounded-xl border hover:bg-stone-50 transition-colors" style="border-color: var(--pale-slate); color: var(--taupe-grey);">Batal</button>
                    <button type="submit" name="bayar_denda" class="flex-1 text-white py-3 rounded-xl font-bold text-xs shadow-sm transition-opacity hover:opacity-90" style="background-color: var(--taupe-grey);">Konfirmasi Lunas</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Tarik Saldo -->
    <div id="modalTarik" class="hidden fixed inset-0 bg-stone-900/40 backdrop-blur-sm z-[100] items-center justify-center p-4 overflow-y-auto">
        <div class="bg-white p-6 rounded-3xl w-full max-w-md shadow-xl border my-auto" style="border-color: var(--pale-slate);">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-base sm:text-lg font-bold" style="color: var(--taupe-grey);">Tarik Saldo Kas</h3>
                <button onclick="closeModal('modalTarik')" class="text-stone-400 hover:text-stone-600 p-1"><i class="fa-solid fa-xmark text-lg"></i></button>
            </div>
            
            <form action="" method="POST" class="space-y-4">
                <div>
                    <label class="block text-[11px] font-bold uppercase tracking-wider mb-2 opacity-70" style="color: var(--taupe-grey);">Nominal Penarikan (Rp)</label>
                    <input type="number" name="nominal" max="<?= $total_kas ?>" required placeholder="0" class="w-full p-3.5 rounded-2xl bg-stone-50 border font-bold text-sm outline-none focus:bg-white transition-all" style="border-color: var(--pale-slate); color: var(--taupe-grey);">
                    <p class="text-[10px] text-stone-500 mt-1 font-semibold">*Maksimal penarikan: Rp <?= number_format($total_kas,0,',','.') ?></p>
                </div>
                <div>
                    <label class="block text-[11px] font-bold uppercase tracking-wider mb-2 opacity-70" style="color: var(--taupe-grey);">Keterangan Penggunaan</label>
                    <textarea name="keterangan" required placeholder="Alasan/Tujuan penarikan..." class="w-full p-3.5 rounded-2xl bg-stone-50 border font-medium text-sm h-28 outline-none focus:bg-white transition-all resize-none" style="border-color: var(--pale-slate); color: var(--taupe-grey);"></textarea>
                </div>
                <div class="flex gap-3 pt-2">
                    <button type="button" onclick="closeModal('modalTarik')" class="flex-1 py-3 font-bold text-xs rounded-xl border hover:bg-stone-50 transition-colors" style="border-color: var(--pale-slate); color: var(--taupe-grey);">Batal</button>
                    <button type="submit" name="tarik_saldo" class="flex-1 text-white py-3 rounded-xl font-bold text-xs shadow-sm transition-opacity hover:opacity-90" style="background-color: var(--old-rose);">Proses Penarikan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Struk -->
    <div id="modalStruk" class="hidden fixed inset-0 bg-stone-900/50 backdrop-blur-sm z-[100] items-center justify-center p-4 overflow-y-auto">
        <div class="bg-white p-6 rounded-3xl w-full max-w-sm shadow-2xl border border-stone-200 my-auto">
            <div id="areaStruk" class="p-4 bg-stone-50 border border-dashed border-stone-300 rounded-2xl font-mono text-xs text-stone-800">
                <div class="text-center pb-3 mb-3 border-b border-stone-300">
                    <h4 class="font-extrabold text-sm uppercase">HARTS LIBRARY</h4>
                    <p class="text-[10px] text-stone-500">Bukti Pembayaran Denda</p>
                </div>
                <div class="space-y-1.5">
                    <div class="flex justify-between">
                        <span class="text-stone-500">No. Transaksi:</span>
                        <span class="font-bold" id="strukNo">#000</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-stone-500">Tanggal:</span>
                        <span id="strukTgl">-</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-stone-500">Siswa:</span>
                        <span class="font-bold" id="strukNama">-</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-stone-500">Ket:</span>
                        <span id="strukKet">Denda Keterlambatan</span>
                    </div>
                    <div class="border-t border-stone-300 my-2 pt-2 flex justify-between font-bold text-sm">
                        <span>TOTAL LUNAS:</span>
                        <span id="strukTotal">Rp 0</span>
                    </div>
                </div>
                <div class="text-center mt-4 pt-2 border-t border-stone-300 text-[10px] text-stone-400 italic">
                    -- Terima Kasih --<br>Simpan struk ini sebagai bukti sah.
                </div>
            </div>

            <div class="flex gap-2 mt-5">
                <button onclick="closeModal('modalStruk')" class="flex-1 py-2.5 font-bold text-xs rounded-xl border border-stone-300 hover:bg-stone-50">Tutup</button>
                <button onclick="window.print()" class="flex-1 text-white py-2.5 rounded-xl font-bold text-xs shadow-sm flex items-center justify-center gap-1.5" style="background-color: var(--taupe-grey);">
                    <i class="fa-solid fa-print"></i> Cetak Struk
                </button>
            </div>
        </div>
    </div>

    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            sidebar.classList.toggle('-translate-x-full');
            overlay.classList.toggle('hidden');
        }

        function openModal(id) {
            const el = document.getElementById(id);
            el.classList.remove('hidden');
            el.classList.add('flex');
            document.body.style.overflow = 'hidden';
        }

        function closeModal(id) {
            const el = document.getElementById(id);
            el.classList.add('hidden');
            el.classList.remove('flex');
            document.body.style.overflow = 'auto';
        }

        function openModalBayar(id, nama, nominal) {
            document.getElementById('input_id_denda').value = id;
            document.getElementById('input_nominal').value = nominal;
            document.getElementById('labelSiswa').innerText = nama;
            openModal('modalBayar');
        }

        function cetakStruk(id, nama, nominal, tgl, ket) {
            document.getElementById('strukNo').innerText = '#KAS-' + String(id).padStart(4, '0');
            document.getElementById('strukNama').innerText = nama;
            document.getElementById('strukTgl').innerText = tgl;
            document.getElementById('strukKet').innerText = ket;
            document.getElementById('strukTotal').innerText = 'Rp ' + Number(nominal).toLocaleString('id-ID');
            openModal('modalStruk');
        }

        function exportToExcel() {
            let table = document.getElementById("tabelKas");
            if (!table) {
                alert("Tabel excel khusus tampilan desktop/tablet.");
                return;
            }
            let html = table.outerHTML;
            let url = 'data:application/vnd.ms-excel,' + encodeURIComponent(html);
            let a = document.createElement('a');
            a.href = url;
            a.download = 'Laporan_Kas_Denda_' + new Date().toISOString().slice(0, 10) + '.xls';
            a.click();
        }

        <?php if (isset($_GET['cetak_id'])): 
            $cid = intval($_GET['cetak_id']);
            $q_c = mysqli_query($conn, "SELECT kd.*, a.nama_anggota FROM kas_denda kd LEFT JOIN anggota a ON kd.id_anggota = a.id_anggota WHERE kd.id_kas = $cid");
            if ($row_c = mysqli_fetch_assoc($q_c)):
                $c_nama = htmlspecialchars(addslashes(trim($row_c['nama_anggota'] ?? 'Siswa')));
                $c_nom  = abs($row_c['nominal']);
                $c_tgl  = date('d M Y, H:i', strtotime($row_c['tanggal_bayar'] ?? 'now'));
        ?>
            window.onload = function() {
                cetakStruk('<?= $cid ?>', '<?= $c_nama ?>', <?= $c_nom ?>, '<?= $c_tgl ?>', 'Denda Keterlambatan Buku');
            };
        <?php endif; endif; ?>
    </script>
</body>
</html>