<?php
session_start();
include '../../config/koneksi.php';

// Proteksi Halaman
if (!isset($_SESSION['admin'])) {
    header("Location: ../../login.php");
    exit;
}

// Ambil data admin dari session
$admin_data = $_SESSION['admin'];
$admin_name = isset($admin_data['nama_petugas']) ? $admin_data['nama_petugas'] : "Admin";
$initial    = strtoupper(substr($admin_name, 0, 1));

// FIX QUERY: Gue ganti r.id jadi r.kode_rak sesuai error lo
$sql = "SELECT r.*, COUNT(b.id_buku) as total_buku 
        FROM rak r 
        LEFT JOIN buku b ON r.kode_rak = b.id_rak 
        GROUP BY r.kode_rak"; 
$query_rak = mysqli_query($conn, $sql);

// Cek jika query gagal
if (!$query_rak) {
    die("Error pada database: " . mysqli_error($conn));
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Rak - HARTS</title>
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

        <a href="../../profile.php" class="bg-white/40 border border-white/60 p-5 rounded-[2.5rem] mb-10 flex items-center shadow-sm hover:bg-white/70 transition-all cursor-pointer group">
            <div class="w-12 h-12 bg-gradient-to-tr from-blue-600 to-indigo-500 rounded-2xl flex items-center justify-center text-white font-bold text-xl mr-4 shadow-lg group-hover:scale-110 transition-transform">
                <?= $initial; ?>
            </div>
            <div class="overflow-hidden">
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Admin</p>
                <p class="text-lg font-bold text-slate-800 leading-tight truncate"><?= htmlspecialchars($admin_name); ?></p>
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
    
    <a href="../buku/index.php" class="flex items-center p-4 text-slate-600 hover:bg-white/50 rounded-2xl transition-all group border border-transparent hover:border-white">
        <span class="mr-4 group-hover:scale-125 transition-transform">📚</span> Katalog Buku
    </a>
    
    <a href="rak.php" class="flex items-center p-4 border-2 bg-blue-600 text-white font-bold shadow-xl border-blue-600 rounded-2xl transition-all group">
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

    <div class="flex-1 h-full overflow-y-auto p-12 bg-white/5">
        <div class="max-w-7xl mx-auto">
            
            <div class="flex justify-between items-end mb-10">
                <div>
                    <h2 class="text-5xl font-black text-slate-800 tracking-tight">Data Rak Buku 🗄️</h2>
                    <p class="text-slate-500 font-medium mt-2 text-xl">Kelola lokasi penyimpanan buku.</p>
                </div>
                <a href="tambah_rak.php" class="bg-blue-600 text-white px-8 py-4 rounded-[1.5rem] font-black shadow-xl hover:scale-105 transition-all active:scale-95">
                    + RAK BARU
                </a>
            </div>

            <div class="glass-card rounded-[2.5rem] overflow-hidden shadow-2xl border border-white/50">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-white/30 text-slate-400 text-[11px] uppercase tracking-[0.2em] font-black">
                            <th class="px-8 py-6">No</th>
                            <th class="px-8 py-6">Kode Rak</th>
                            <th class="px-8 py-6">Lokasi / Lantai</th>
                            <th class="px-8 py-6 text-center">Isi Koleksi</th>
                            <th class="px-8 py-6 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/20">
                        <?php 
                        if($query_rak && mysqli_num_rows($query_rak) > 0): 
                            $no = 1;
                            while($row = mysqli_fetch_assoc($query_rak)): ?>
                            <tr class="hover:bg-white/40 transition-colors text-slate-700">
                                <td class="px-8 py-6 text-slate-400 font-bold"><?= $no++; ?></td>
                                <td class="px-8 py-6">
                                    <span class="bg-white/50 px-4 py-2 rounded-xl font-black text-slate-800 border border-white/60">
                                        <?= htmlspecialchars($row['kode_rak']); ?>
                                    </span>
                                </td>
                                <td class="px-8 py-6 font-semibold text-slate-600"><?= htmlspecialchars($row['lokasi']); ?></td>
                                
                                <td class="px-8 py-6 text-center">
                                    <span class="text-blue-600 font-black"><?= $row['total_buku']; ?></span>
                                    <span class="text-[10px] text-slate-400 font-bold uppercase ml-1">Judul</span>
                                </td>

                                <td class="px-8 py-6">
                                    <div class="flex justify-center gap-2">
                                        <a href="edit_rak.php?id=<?= $row['kode_rak']; ?>" class="w-9 h-9 bg-white shadow-sm flex items-center justify-center rounded-lg hover:bg-blue-600 hover:text-white transition-all transform active:scale-90" title="Edit">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                            </svg>
                                        </a>
                                        <a href="hapus_rak.php?id=<?= $row['kode_rak']; ?>" onclick="return confirm('Hapus rak ini?')" class="w-9 h-9 bg-white shadow-sm flex items-center justify-center rounded-lg hover:bg-red-500 hover:text-white transition-all transform active:scale-90" title="Hapus">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-4v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="px-8 py-20 text-center font-bold text-slate-400 italic">
                                    Belum ada data rak.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div> 
        </div>
    </div>

</body>
</html>