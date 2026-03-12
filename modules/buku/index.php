<?php
session_start();
include '../../config/koneksi.php';

// Proteksi Halaman
if (!isset($_SESSION['admin'])) {
    header("Location: ../../login.php");
    exit;
}

// Data Admin
$admin_data = $_SESSION['admin'];
$admin_name = isset($admin_data['nama_petugas']) ? $admin_data['nama_petugas'] : "Admin";
$initial    = strtoupper(substr($admin_name, 0, 1));

// --- LOGIKA SEARCH & FILTER ---
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$kategori_filter = isset($_GET['kategori']) ? mysqli_real_escape_string($conn, $_GET['kategori']) : '';

// Query: Hapus join penerbit karena tidak digunakan
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Katalog Buku - HARTS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: linear-gradient(135deg, #fbcfe8 0%, #e9d5ff 40%, #c3dafe 100%); height: 100vh; overflow: hidden; }
        .glass-sidebar { background: rgba(255, 255, 255, 0.3); backdrop-filter: blur(20px); border-right: 1px solid rgba(255, 255, 255, 0.5); }
        .glass-card { background: rgba(255, 255, 255, 0.4); backdrop-filter: blur(20px); border: 1px solid rgba(255, 255, 255, 0.6); }
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-thumb { background: rgba(0,0,0,0.1); border-radius: 10px; }
    </style>
</head>
<body class="flex">

    <div class="w-80 h-full glass-sidebar p-8 flex flex-col shadow-2xl relative z-50">
        <div class="flex items-center mb-10">
            <div class="bg-blue-600 p-2 rounded-xl mr-3 shadow-lg">
                <span class="text-white font-black text-xl">H</span>
            </div>
            <h1 class="text-2xl font-black text-slate-800 tracking-tighter uppercase">Harts</h1>
        </div>

        <a href="../../profile.php" class="bg-white/40 border border-white/60 p-5 rounded-[2.5rem] mb-10 flex items-center shadow-sm hover:bg-white/70 transition-all group">
            <div class="w-12 h-12 bg-gradient-to-tr from-blue-600 to-indigo-500 rounded-2xl flex items-center justify-center text-white font-bold text-xl mr-4 shadow-lg group-hover:scale-110 transition-transform">
                <?= $initial; ?>
            </div>
            <div class="overflow-hidden">
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Admin</p>
                <p class="text-lg font-bold text-slate-800 truncate"><?= htmlspecialchars($admin_name); ?></p>
            </div>
        </a>

  <nav class="space-y-2 flex-1 overflow-y-auto pr-2 custom-scrollbar">
    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em] ml-4 mb-2">Main Menu</p>
    
    <a href="../../index.php" class="flex items-center p-4 rounded-2xl transition-all group text-slate-600 hover:bg-white/50 border border-transparent hover:border-white">
        <span class="mr-4 group-hover:scale-125 transition-transform">🏠</span> Dashboard
    </a>
    
    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em] ml-4 mt-6 mb-2">Layanan & Scanner</p>
    
    <a href="../../presensi.php" class="flex items-center p-4 text-slate-600 hover:bg-white/50 rounded-2xl transition-all group border border-transparent hover:border-white">
        <span class="mr-4 group-hover:scale-125 transition-transform">⏱️</span> Presensi Siswa
    </a>
    
    <a href="../../log_kunjungan.php" class="flex items-center p-4 text-slate-600 hover:bg-white/50 rounded-2xl transition-all group border border-transparent hover:border-white">
        <span class="mr-4 group-hover:scale-125 transition-transform">📋</span> Log Kunjungan
    </a>

    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em] ml-4 mt-6 mb-2">Manajemen Keuangan</p>
    
    <a href="../../kas_denda.php" class="flex items-center p-4 text-slate-600 hover:bg-white/50 rounded-2xl transition-all group border border-transparent hover:border-white">
        <span class="mr-4 group-hover:scale-125 transition-transform">💰</span> Kas Denda
    </a>

    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em] ml-4 mt-6 mb-2">Katalog & User</p>
    
    <a href="../anggota/index.php?page=daftar" class="flex items-center p-4 text-slate-600 hover:bg-white/50 border border-transparent hover:border-white rounded-2xl transition-all group">
        <span class="mr-4 group-hover:scale-125 transition-transform">👥</span> Data Anggota
    </a>

    <a href="../anggota/index.php?page=validasi" class="flex items-center p-4 text-slate-600 hover:bg-white/50 border border-transparent hover:border-white rounded-2xl transition-all group">
        <span class="mr-4 group-hover:scale-125 transition-transform">✅</span> Validasi Akun
    </a>
    
    <a href="index.php" class="flex items-center p-4 border-2 bg-blue-600 text-white font-bold shadow-xl border-blue-600 rounded-2xl transition-all group">
        <span class="mr-4 group-hover:scale-125 transition-transform">📚</span> Katalog Buku
    </a>
    
    <a href="../rak/rak.php" class="flex items-center p-4 text-slate-600 hover:bg-white/50 rounded-2xl transition-all group border border-transparent hover:border-white">
        <span class="mr-4 group-hover:scale-125 transition-transform">🗄️</span> Data Rak
    </a>

    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em] ml-4 mt-6 mb-2">Laporan</p>
    
    <a href="../../laporan.php" class="flex items-center p-4 text-slate-600 hover:bg-white/50 rounded-2xl transition-all group border border-transparent hover:border-white">
        <span class="mr-4 group-hover:scale-125 transition-transform">📊</span> Laporan Utama
    </a>
</nav>
        <a href="../../logout.php" class="p-4 text-red-500 font-bold flex items-center hover:bg-red-50/50 rounded-2xl transition-all mt-auto group">
            <span class="mr-4 group-hover:rotate-12 transition-transform">🚪</span> Keluar
        </a>
    </div>

    <div class="flex-1 h-full overflow-y-auto p-12">
        <div class="max-w-7xl mx-auto">
            <div class="flex justify-between items-end mb-10">
                <div>
                    <h2 class="text-5xl font-black text-slate-800 tracking-tight">Katalog Buku 📚</h2>
                    <p class="text-slate-500 font-medium mt-2 text-xl">Daftar koleksi mata pelajaran sekolah.</p>
                </div>
                <a href="tambah.php" class="bg-blue-600 text-white px-8 py-4 rounded-[1.5rem] font-black shadow-xl hover:scale-105 transition-all">
                    + BUKU BARU
                </a>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
                <div class="md:col-span-2 relative">
                    <form action="" method="GET">
                        <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Cari judul atau pengarang..." 
                               class="w-full pl-12 pr-6 py-4 rounded-2xl bg-white/50 border border-white/80 focus:bg-white outline-none transition-all font-semibold shadow-sm focus:ring-4 focus:ring-blue-100">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2">🔍</span>
                    </form>
                </div>
                <div>
                    <form action="" method="GET">
                        <input type="hidden" name="search" value="<?= htmlspecialchars($search) ?>">
                        <select name="kategori" onchange="this.form.submit()" 
                                class="w-full px-6 py-4 rounded-2xl bg-white/50 border border-white/80 focus:bg-white outline-none transition-all font-semibold shadow-sm cursor-pointer">
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
                <a href="index.php" class="bg-white/50 text-slate-600 py-4 rounded-2xl font-bold text-center border border-white/80 hover:bg-white transition-all shadow-sm">Reset</a>
            </div>

            <div class="glass-card rounded-[2.5rem] overflow-hidden shadow-2xl border border-white/50">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-white/30 text-slate-400 text-[11px] uppercase tracking-[0.2em] font-black">
                            <th class="px-8 py-6">Info Buku</th>
                            <th class="px-8 py-6">Pengarang</th>
                            <th class="px-8 py-6">Lokasi Rak</th> 
                            <th class="px-8 py-6 text-center">Stok</th>
                            <th class="px-8 py-6 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/20">
                        <?php if($query && mysqli_num_rows($query) > 0): ?>
                            <?php while($b = mysqli_fetch_assoc($query)): ?>
                            <tr class="hover:bg-white/40 transition-colors text-slate-700">
                                <td class="px-8 py-6">
                                    <span class="bg-blue-100 text-blue-600 px-3 py-1 rounded-lg text-[9px] font-black uppercase tracking-wider mb-2 inline-block">
                                        <?= htmlspecialchars($b['kategori'] ?? 'Lainnya'); ?>
                                    </span>
                                    <p class="text-lg font-bold text-slate-800 leading-tight"><?= htmlspecialchars($b['judul_buku']); ?></p>
                                    <p class="text-[10px] font-black text-slate-400 uppercase mt-1">ID #<?= $b['id_buku']; ?> • <?= $b['tahun_buku']; ?></p>
                                </td>
                                <td class="px-8 py-6 font-semibold"><?= htmlspecialchars($b['nama_pengarang'] ?? '-'); ?></td>
                                <td class="px-8 py-6 font-bold text-blue-600"><?= htmlspecialchars($b['kode_rak'] ?? '-'); ?></td>
                                <td class="px-8 py-6 text-center font-bold text-lg"><?= htmlspecialchars($b['jumlah_buku']); ?></td>
                                <td class="px-8 py-6 flex justify-center gap-2">
                                    <a href="edit.php?id=<?= $b['id_buku']; ?>" class="w-10 h-10 bg-white shadow-sm flex items-center justify-center rounded-xl hover:bg-blue-600 hover:text-white transition-all">✏️</a>
                                    <a href="hapus.php?id=<?= $b['id_buku']; ?>" onclick="return confirm('Hapus?')" class="w-10 h-10 bg-white shadow-sm flex items-center justify-center rounded-xl hover:bg-red-500 hover:text-white transition-all">🗑️</a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="5" class="px-8 py-20 text-center font-bold text-slate-400 italic">Data tidak ditemukan.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>