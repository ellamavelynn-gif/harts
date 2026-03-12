<?php
session_start();
include '../../config/koneksi.php';

if (!isset($_SESSION['admin'])) {
    header("Location: ../../login.php");
    exit;
}

$admin_data = $_SESSION['admin'];
$admin_name = $admin_data['nama_petugas'];
$initial    = strtoupper(substr($admin_name, 0, 1));
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Data Rak - HARTS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            background: linear-gradient(135deg, #fbcfe8 0%, #e9d5ff 40%, #c3dafe 100%);
            height: 100vh; overflow: hidden;
        }
        .glass-sidebar { background: rgba(255, 255, 255, 0.3); backdrop-filter: blur(20px); border-right: 1px solid rgba(255, 255, 255, 0.5); }
        .glass-card { background: rgba(255, 255, 255, 0.45); backdrop-filter: blur(20px); border: 1px solid rgba(255, 255, 255, 0.7); }
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

        <a href="../../profile.php" class="bg-white/40 border border-white/60 p-5 rounded-[2.5rem] mb-10 flex items-center shadow-sm hover:bg-white/70 transition-all group">
            <div class="w-12 h-12 bg-gradient-to-tr from-blue-600 to-indigo-500 rounded-2xl flex items-center justify-center text-white font-bold text-xl mr-4 shadow-lg group-hover:scale-110 transition-transform"><?= $initial; ?></div>
            <div class="overflow-hidden">
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Admin</p>
                <p class="text-lg font-bold text-slate-800 leading-tight truncate"><?= htmlspecialchars($admin_name); ?></p>
            </div>
        </a>

<nav class="space-y-2 flex-1 overflow-y-auto pr-2">
    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em] ml-4 mb-2">Main Menu</p>
    <a href="../../index.php" class="flex items-center p-4 text-slate-600 hover:bg-white/50 rounded-2xl transition-all border border-transparent hover:border-white group">
        <span class="w-8 flex justify-center mr-4 group-hover:scale-125 transition-transform text-lg">🏠</span> 
        <span class="font-semibold text-slate-700">Dashboard</span>
    </a>
    
    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em] ml-4 mt-6 mb-2">Layanan</p>
    <a href="../../presensi.php" class="flex items-center p-4 text-slate-600 hover:bg-white/50 rounded-2xl transition-all border border-transparent hover:border-white group">
        <span class="w-8 flex justify-center mr-4 group-hover:scale-125 transition-transform text-lg">⏱️</span> 
        <span class="font-semibold text-slate-700">Presensi Siswa</span>
    </a>
    
    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em] ml-4 mt-6 mb-2">Manajemen User</p>
    <a href="../anggota/index.php" class="flex items-center p-4 text-slate-600 hover:bg-white/50 rounded-2xl transition-all border border-transparent hover:border-white group">
        <span class="w-8 flex justify-center mr-4 group-hover:scale-125 transition-transform text-lg">👥</span> 
        <span class="font-semibold text-slate-700">Data Anggota</span>
    </a>
    
    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em] ml-4 mt-6 mb-2">Katalog</p>
    <a href="../buku/index.php" class="flex items-center p-4 text-slate-600 hover:bg-white/50 rounded-2xl transition-all border border-transparent hover:border-white group">
        <span class="w-8 flex justify-center mr-4 group-hover:scale-125 transition-transform text-lg">📚</span> 
        <span class="font-semibold text-slate-700">Katalog Buku</span>
    </a>
    <div class="flex items-center p-4 border-2 bg-blue-600 text-white font-bold shadow-xl shadow-blue-200 border-blue-600 rounded-2xl transition-all">
        <span class="w-8 flex justify-center mr-4 text-lg">🗄️</span> 
        <span>Data Rak</span>
    </div>

    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em] ml-4 mt-6 mb-2">Laporan</p>
    <a href="../../laporan.php" class="flex items-center p-4 text-slate-600 hover:bg-white/50 rounded-2xl transition-all border border-transparent hover:border-white group">
        <span class="w-8 flex justify-center mr-4 group-hover:scale-125 transition-transform text-lg">📊</span> 
        <span class="font-semibold text-slate-700">Laporan Peminjaman</span>
    </a>
</nav>
<div class="mt-auto pt-5">
    <a href="../../logout.php" class="flex items-center p-4 border-2 border-transparent text-red-500 font-bold hover:bg-red-50/50 rounded-2xl transition-all group hover:border-red-100">
        <span class="w-8 flex justify-center mr-4 group-hover:rotate-12 transition-transform text-xl">🚪</span> 
        <span>Keluar</span>
    </a>
</div>
    </div>

    <div class="flex-1 h-full overflow-y-auto p-12">
        <div class="max-w-7xl mx-auto">
            <div class="flex justify-between items-end mb-10">
                <div>
                    <h2 class="text-4xl font-black text-slate-800 tracking-tight">Data Rak Buku 🗄️</h2>
                    <p class="text-slate-500 font-medium mt-1">Kelola lokasi penyimpanan buku.</p>
                </div>
                <a href="tambah_rak.php" class="bg-slate-800 text-white px-8 py-4 rounded-[1.5rem] font-bold hover:bg-black transition-all shadow-lg">+ Tambah Rak</a>
            </div>

            <div class="glass-card p-8 rounded-[3rem] shadow-xl overflow-x-auto">
                <table class="w-full text-left border-separate border-spacing-y-4">
                    <thead>
                        <tr class="text-slate-400 text-xs uppercase tracking-[0.2em]">
                            <th class="px-6 pb-2 font-black">No</th>
                            <th class="px-6 pb-2 font-black">Kode Rak</th>
                            <th class="px-6 pb-2 font-black">Lokasi / Lantai</th>
                            <th class="px-6 pb-2 font-black text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $no = 1;
                        $query = mysqli_query($conn, "SELECT * FROM rak ORDER BY id DESC");
                        while($row = mysqli_fetch_array($query)) :
                        ?>
                        <tr class="bg-white/30 hover:bg-white/60 transition-all group">
                            <td class="p-5 rounded-l-[2rem] font-bold text-slate-500"><?= $no++; ?></td>
                            <td class="p-5">
                                <div class="flex items-center">
                                    <div class="w-10 h-10 bg-blue-100 text-blue-600 rounded-xl mr-3 flex items-center justify-center font-black">#</div>
                                    <span class="font-bold text-slate-800 text-lg"><?= htmlspecialchars($row['kode_rak']); ?></span>
                                </div>
                            </td>
                            <td class="p-5 font-semibold text-slate-600"><?= htmlspecialchars($row['lokasi']); ?></td>
                            <td class="p-5 rounded-r-[2rem] text-center">
                                <div class="flex justify-center gap-2">
                                    <a href="edit_rak.php?id=<?= $row['id']; ?>" class="w-10 h-10 flex items-center justify-center bg-white rounded-xl text-blue-600 shadow-sm hover:bg-blue-600 hover:text-white transition-all">✏️</a>
                                    <a href="hapus_rak.php?id=<?= $row['id']; ?>" onclick="return confirm('Hapus?')" class="w-10 h-10 flex items-center justify-center bg-white rounded-xl text-red-600 shadow-sm hover:bg-red-600 hover:text-white transition-all">🗑️</a>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>