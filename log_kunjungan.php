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
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Log Kunjungan - HARTS Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        body { 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            background: linear-gradient(135deg, #fbcfe8 0%, #e9d5ff 40%, #c3dafe 100%); 
            height: 100vh; 
            overflow: hidden; 
        }
        .glass-sidebar { background: rgba(255, 255, 255, 0.3); backdrop-filter: blur(20px); border-right: 1px solid rgba(255, 255, 255, 0.5); }
        .glass-card { background: rgba(255, 255, 255, 0.4); backdrop-filter: blur(15px); border: 1px solid rgba(255, 255, 255, 0.6); }
        .nav-active { background-color: #2563eb; color: white; box-shadow: 0 10px 15px -3px rgba(59, 130, 246, 0.3); }
        
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-thumb { background: rgba(0,0,0,0.1); border-radius: 10px; }
    </style>
</head>
<body class="flex">

    <div class="w-80 h-full glass-sidebar p-8 flex flex-col shadow-2xl relative z-50">
        <div class="flex items-center mb-10">
            <div class="bg-blue-600 p-2 rounded-xl mr-3 shadow-lg"><span class="text-white font-black text-xl">H</span></div>
            <h1 class="text-2xl font-black text-slate-800 tracking-tighter uppercase">Harts</h1>
        </div>
        
        <a href="profile.php" class="bg-white/40 border border-white/60 p-5 rounded-[2.5rem] mb-10 flex items-center shadow-sm hover:bg-white/70 transition-all group">
            <div class="w-12 h-12 bg-gradient-to-tr from-blue-600 to-indigo-500 rounded-2xl flex items-center justify-center text-white font-bold text-xl mr-4 group-hover:scale-110 transition-transform"><?= $initial; ?></div>
            <div class="overflow-hidden">
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Admin</p>
                <p class="text-lg font-bold text-slate-800 leading-tight truncate"><?= $admin_name; ?></p>
            </div>
        </a>

<nav class="space-y-2 flex-1 overflow-y-auto pr-2 custom-scrollbar">
    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em] ml-4 mb-2">Main Menu</p>
    
    <a href="index.php" class="flex items-center p-4 rounded-2xl transition-all group <?= ($current_page == 'index.php') ? 'nav-active font-bold' : 'text-slate-600 hover:bg-white/50'; ?>">
        <span class="mr-4 group-hover:scale-125 transition-transform">🏠</span> Dashboard
    </a>
    
    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em] ml-4 mt-6 mb-2">Layanan & Scanner</p>
    
    <a href="presensi.php" class="flex items-center p-4 rounded-2xl transition-all group <?= ($current_page == 'presensi.php') ? 'nav-active font-bold' : 'text-slate-600 hover:bg-white/50'; ?>">
        <span class="mr-4 group-hover:scale-125 transition-transform">⏱️</span> Scanner Presensi
    </a>
    
    <a href="log_kunjungan.php" class="flex items-center p-4 rounded-2xl transition-all group <?= ($current_page == 'log_kunjungan.php') ? 'nav-active font-bold' : 'text-slate-600 hover:bg-white/50'; ?>">
        <span class="mr-4 group-hover:scale-125 transition-transform">📋</span> Log Kunjungan
    </a>

    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em] ml-4 mt-6 mb-2">Manajemen Keuangan</p>
    
    <a href="kas_denda.php" class="flex items-center p-4 rounded-2xl transition-all group <?= ($current_page == 'kas_denda.php') ? 'nav-active font-bold' : 'text-slate-600 hover:bg-white/50'; ?>">
        <span class="mr-4 group-hover:scale-125 transition-transform">💰</span> Kas Denda
    </a>

    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em] ml-4 mt-6 mb-2">Katalog & User</p>
    
    <a href="modules/anggota/index.php?page=daftar" class="flex items-center p-4 rounded-2xl transition-all group <?= (isset($_GET['page']) && $_GET['page'] == 'daftar') ? 'nav-active font-bold' : 'text-slate-600 hover:bg-white/50'; ?>">
        <span class="mr-4 group-hover:scale-125 transition-transform">👥</span> Data Anggota
    </a>

    <a href="modules/anggota/index.php?page=validasi" class="flex items-center p-4 rounded-2xl transition-all group <?= (isset($_GET['page']) && $_GET['page'] == 'validasi') ? 'nav-active font-bold' : 'text-slate-600 hover:bg-white/50'; ?>">
        <span class="mr-4 group-hover:scale-125 transition-transform">✅</span> Validasi Akun
    </a>
    
    <a href="modules/buku/index.php" class="flex items-center p-4 rounded-2xl transition-all group <?= (strpos($_SERVER['PHP_SELF'], 'buku') !== false) ? 'nav-active font-bold' : 'text-slate-600 hover:bg-white/50'; ?>">
        <span class="mr-4 group-hover:scale-125 transition-transform">📚</span> Katalog Buku
    </a>

    <a href="modules/rak/rak.php" class="flex items-center p-4 rounded-2xl transition-all group <?= (strpos($_SERVER['PHP_SELF'], 'rak') !== false) ? 'nav-active font-bold' : 'text-slate-600 hover:bg-white/50'; ?>">
        <span class="mr-4 group-hover:scale-125 transition-transform">🗄️</span> Data Rak
    </a>

    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em] ml-4 mt-6 mb-2">Laporan</p>
    
    <a href="laporan.php" class="flex items-center p-4 rounded-2xl transition-all group <?= ($current_page == 'laporan.php') ? 'nav-active font-bold' : 'text-slate-600 hover:bg-white/50'; ?>">
        <span class="mr-4 group-hover:scale-125 transition-transform">📊</span> Laporan Utama
    </a>
</nav>
        <a href="logout.php" class="p-4 text-red-500 font-bold flex items-center hover:bg-red-50/50 rounded-2xl transition-all mt-auto group">🚪 Keluar</a>
    </div>

    <div class="flex-1 h-full overflow-y-auto p-12 ml-0"> 
    <div class="max-w-6xl mx-auto">
        <header class="flex justify-between items-start mb-12">
            <div>
                <h2 class="text-5xl font-black text-slate-800 tracking-tight">Log Kunjungan ✨</h2>
                <p class="text-slate-500 font-medium mt-2 text-xl italic">Pantau arus masuk keluar siswa.</p>
            </div>
            
            <div class="flex items-center gap-3">
                <form method="GET" class="bg-white/60 px-6 py-3 rounded-[2rem] border border-white shadow-sm flex items-center">
                    <span class="mr-3 text-xl">📅</span>
                    <input type="date" name="filter_tgl" 
                           value="<?= isset($_GET['filter_tgl']) ? $_GET['filter_tgl'] : date('Y-m-d'); ?>" 
                           onchange="this.form.submit()"
                           class="bg-transparent border-none outline-none font-bold text-slate-600 cursor-pointer">
                </form>
                <?php if(isset($_GET['filter_tgl'])): ?>
                    <a href="log_kunjungan.php" class="bg-white/60 p-3 rounded-full border border-white shadow-sm hover:bg-white transition-all" title="Reset Filter">🔄</a>
                <?php endif; ?>
            </div>
        </header>

        <div class="grid grid-cols-3 gap-8 mb-12">
            <?php 
            // Ambil tanggal dari filter, jika tidak ada pakai hari ini
            $tanggal_filter = isset($_GET['filter_tgl']) ? $_GET['filter_tgl'] : date('Y-m-d');
            $hari_ini = mysqli_real_escape_string($conn, $tanggal_filter);

            // 1. MENGHITUNG TOTAL SISWA UNIK
            $res_total = mysqli_query($conn, "SELECT COUNT(DISTINCT id_anggota) as total FROM kunjungan WHERE DATE(waktu_masuk) = '$hari_ini'");
            $row_total = mysqli_fetch_assoc($res_total);
            $total_masuk = $row_total['total'] ?? 0;

            // 2. MENGHITUNG YANG SEDANG DI DALAM (Hanya berlaku jika filter = hari ini)
            $res_dalam = mysqli_query($conn, "SELECT COUNT(id_kunjungan) as total FROM kunjungan WHERE waktu_keluar IS NULL AND DATE(waktu_masuk) = '$hari_ini'");
            $row_dalam = mysqli_fetch_assoc($res_dalam);
            $sedang_diperpus = $row_dalam['total'] ?? 0;
            ?>

            <div class="bg-blue-600 p-8 rounded-[3rem] text-white shadow-2xl">
                <p class="text-sm font-bold opacity-80 uppercase tracking-widest">Total Kunjungan</p>
                <h3 class="text-5xl font-black mt-2"><?= $total_masuk; ?> <span class="text-xl font-normal opacity-60 uppercase">Siswa</span></h3>
            </div>
            
            <div class="bg-emerald-500 p-8 rounded-[3rem] text-white shadow-2xl">
                <p class="text-sm font-bold opacity-80 uppercase tracking-widest">Sedang Di Dalam</p>
                <h3 class="text-5xl font-black mt-2"><?= $sedang_diperpus; ?> <span class="text-xl font-normal opacity-60 uppercase">Siswa</span></h3>
            </div>
        </div>            <div class="glass-card p-10 rounded-[3.5rem] shadow-xl">
                <table class="w-full text-left border-separate border-spacing-y-4">
                    <thead>
                        <tr class="text-slate-400 text-[10px] uppercase tracking-[0.2em]">
                            <th class="px-8 font-black">Nama Siswa</th>
                            <th class="px-8 font-black text-center">Masuk</th>
                            <th class="px-8 font-black text-center">Keluar</th>
                            <th class="px-8 font-black text-center">Durasi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sql = "SELECT kunjungan.*, anggota.nama_anggota 
                                FROM kunjungan 
                                JOIN anggota ON kunjungan.id_anggota = anggota.id_anggota 
                                WHERE DATE(waktu_masuk) = '$hari_ini'
                                ORDER BY waktu_masuk DESC";
                        $query = mysqli_query($conn, $sql);

                        if(mysqli_num_rows($query) > 0) :
                            while($row = mysqli_fetch_assoc($query)) :
                        ?>
                        <tr class="bg-white/30 hover:bg-white/60 transition-all">
                            <td class="p-6 rounded-l-[2rem] font-bold text-slate-800 text-lg italic"><?= $row['nama_anggota']; ?></td>
                            <td class="p-6 text-center">
                                <span class="px-4 py-2 bg-blue-100 text-blue-600 rounded-xl font-black text-sm"><?= date('H:i', strtotime($row['waktu_masuk'])); ?></span>
                            </td>
                            <td class="p-6 text-center font-bold">
                                <?php if($row['waktu_keluar']) : ?>
                                    <span class="px-4 py-2 bg-orange-100 text-orange-600 rounded-xl text-sm"><?= date('H:i', strtotime($row['waktu_keluar'])); ?></span>
                                <?php else : ?>
                                    <span class="text-emerald-500 animate-pulse font-black uppercase text-[10px] tracking-widest italic flex items-center justify-center">
                                        <span class="w-2 h-2 bg-emerald-500 rounded-full mr-2"></span> Di Dalam
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="p-6 rounded-r-[2rem] text-center text-slate-500 font-medium">
                                <?php 
                                if($row['waktu_keluar']) {
                                    $awal = strtotime($row['waktu_masuk']);
                                    $akhir = strtotime($row['waktu_keluar']);
                                    $diff = $akhir - $awal;
                                    echo floor($diff / 60) . " mnt";
                                } else { echo "-"; }
                                ?>
                            </td>
                        </tr>
                        <?php endwhile; else: ?>
                        <tr>
                            <td colspan="4" class="p-20 text-center italic text-slate-400 font-bold tracking-widest">Belum ada kunjungan hari ini.</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>