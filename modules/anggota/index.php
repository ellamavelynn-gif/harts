<?php
session_start();
include '../../config/koneksi.php';

// Proteksi halaman admin
if (!isset($_SESSION['admin'])) {
    header("Location: ../../login.php");
    exit;
}

$admin_data = $_SESSION['admin'];
$admin_name = $admin_data['nama_petugas'];
$initial     = strtoupper(substr($admin_name, 0, 1));

// LOGIKA SELESAI (Aktifkan Akun)
if (isset($_GET['action']) && $_GET['action'] == 'selesai') {
    $id = $_GET['id'];
    mysqli_query($conn, "UPDATE anggota SET status_akun = 'aktif' WHERE id_anggota = '$id'");
    header("Location: index.php?page=validasi");
    exit;
}

// Logika Halaman & Query
$page = isset($_GET['page']) ? $_GET['page'] : 'daftar';
$keyword = "";

if ($page == 'validasi') {
    $query_sql = "SELECT * FROM anggota WHERE status_akun = 'baru' ORDER BY id_anggota DESC";
} else {
    $query_sql = "SELECT * FROM anggota WHERE status_akun = 'aktif' ORDER BY nama_anggota ASC";
    if (isset($_POST['cari'])) {
        $keyword = mysqli_real_escape_string($conn, $_POST['keyword']);
        $query_sql = "SELECT * FROM anggota WHERE 
                      (nama_anggota LIKE '%$keyword%' OR nis LIKE '%$keyword%') 
                      AND status_akun = 'aktif' 
                      ORDER BY nama_anggota ASC";
    }
}

$query = mysqli_query($conn, $query_sql);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Data Anggota - HARTS</title>
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

        <nav class="space-y-2 flex-1 overflow-y-auto pr-2 custom-scrollbar">
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em] ml-4 mb-2">Main Menu</p>
            
            <a href="../../index.php" class="flex items-center p-4 rounded-2xl transition-all group text-slate-600 hover:bg-white/50 border border-transparent hover:border-white">
                <span class="mr-4 group-hover:scale-125 transition-transform">🏠</span> Dashboard
            </a>
            
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em] ml-4 mt-6 mb-2">Layanan & Scanner</p>
            
            <a href="../../presensi.php" class="flex items-center p-4 text-slate-600 hover:bg-white/50 rounded-2xl transition-all group border border-transparent hover:border-white">
                <span class="mr-4 group-hover:scale-125 transition-transform">⏱️</span> Scanner Presensi
            </a>
            
            <a href="../../log_kunjungan.php" class="flex items-center p-4 text-slate-600 hover:bg-white/50 rounded-2xl transition-all group border border-transparent hover:border-white">
                <span class="mr-4 group-hover:scale-125 transition-transform">📋</span> Log Kunjungan
            </a>

            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em] ml-4 mt-6 mb-2">Manajemen Keuangan</p>
            
            <a href="../../kas_denda.php" class="flex items-center p-4 text-slate-600 hover:bg-white/50 rounded-2xl transition-all group border border-transparent hover:border-white">
                <span class="mr-4 group-hover:scale-125 transition-transform">💰</span> Kas Denda
            </a>

            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em] ml-4 mt-6 mb-2">Katalog & User</p>
            
            <a href="index.php?page=daftar" class="flex items-center p-4 border-2 <?= $page == 'daftar' ? 'bg-blue-600 text-white font-bold shadow-xl border-blue-600' : 'text-slate-600 hover:bg-white/50 border-transparent hover:border-white' ?> rounded-2xl transition-all group">
                <span class="mr-4 group-hover:scale-125 transition-transform">👥</span> Data Anggota
            </a>
            
            <a href="index.php?page=validasi" class="flex items-center p-4 border-2 <?= $page == 'validasi' ? 'bg-indigo-600 text-white font-bold shadow-xl border-indigo-600' : 'text-slate-600 hover:bg-white/50 border-transparent hover:border-white' ?> rounded-2xl transition-all group">
                <span class="mr-4 group-hover:scale-125 transition-transform">✅</span> Validasi Akun
            </a>
            
            <a href="../buku/index.php" class="flex items-center p-4 text-slate-600 hover:bg-white/50 rounded-2xl transition-all group border border-transparent hover:border-white">
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
        <a href="../../logout.php" class="p-4 text-red-500 font-bold flex items-center hover:bg-red-50/50 rounded-2xl transition-all mt-auto group">🚪 Keluar</a>
    </div>

    <div class="flex-1 h-full overflow-y-auto p-12">
        <div class="max-w-7xl mx-auto">
            
            <?php if ($page == 'validasi') : ?>
                <div class="flex justify-between items-end mb-10">
                    <div>
                        <h2 class="text-4xl font-black text-slate-800 tracking-tight">Validasi Akun ✨</h2>
                        <p class="text-slate-500 font-medium mt-1">Sebutkan <b>Token</b> ke siswa, lalu klik Selesai jika sudah.</p>
                    </div>
                </div>

                <div class="space-y-4">
                    <?php if(mysqli_num_rows($query) > 0) : ?>
                        <?php while($row = mysqli_fetch_assoc($query)) : ?>
                        <div class="glass-card p-6 rounded-[2.5rem] flex items-center justify-between border-white shadow-sm hover:scale-[1.01] transition-all">
                            <div class="flex items-center gap-4 w-1/3">
                                <div class="w-14 h-14 bg-white rounded-2xl flex items-center justify-center font-black text-indigo-500 shadow-sm text-xl">
                                    <?= strtoupper(substr($row['nama_anggota'], 0, 1)); ?>
                                </div>
                                <div>
                                    <p class="font-bold text-slate-800 text-lg leading-tight"><?= htmlspecialchars($row['nama_anggota']); ?></p>
                                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest"><?= $row['nis']; ?> • <?= $row['kelas']; ?></p>
                                </div>
                            </div>

                            <div class="flex-1 px-4">
                                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Kontak & Alamat</p>
                                <p class="text-xs font-bold text-slate-600"><?= $row['no_tlp']; ?></p>
                                <p class="text-[10px] text-slate-400 italic truncate w-48"><?= $row['alamat']; ?></p>
                            </div>
                            
                            <div class="flex items-center gap-8">
                                <div class="text-center bg-indigo-500/10 px-6 py-2 rounded-2xl border border-indigo-500/20">
                                    <p class="text-[9px] font-black text-indigo-400 uppercase tracking-[0.2em] mb-1">Token Siswa</p>
                                    <p class="font-mono font-black text-2xl text-indigo-600 tracking-widest"><?= $row['token_regis']; ?></p>
                                </div>
                                
                                <a href="index.php?page=validasi&action=selesai&id=<?= $row['id_anggota']; ?>" 
                                   class="bg-green-500 text-white px-8 py-4 rounded-2xl font-black text-xs hover:bg-green-600 transition-all shadow-lg shadow-green-100 uppercase">
                                    Selesai
                                </a>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    <?php else : ?>
                        <div class="text-center py-20 glass-card rounded-[3rem] border-dashed border-2 border-white/50">
                            <p class="text-4xl mb-4">📭</p>
                            <p class="text-slate-400 font-bold uppercase tracking-widest text-xs">Semua akun sudah tervalidasi!</p>
                        </div>
                    <?php endif; ?>
                </div>

            <?php else : ?>
                <div class="flex justify-between items-end mb-10">
                    <div>
                        <h2 class="text-4xl font-black text-slate-800 tracking-tight">Data Anggota 👥</h2>
                        <p class="text-slate-500 font-medium mt-1">Daftar siswa yang sudah aktif di sistem.</p>
                    </div>
                </div>

                <form action="" method="POST" class="mb-8 flex gap-4">
                    <input type="text" name="keyword" value="<?= htmlspecialchars($keyword); ?>" placeholder="Cari nama atau NIS..." class="flex-1 px-8 py-4 rounded-[1.5rem] glass-card border-white focus:bg-white/80 outline-none font-semibold text-slate-700 shadow-sm transition-all">
                    <button type="submit" name="cari" class="bg-blue-600 text-white px-10 py-4 rounded-[1.5rem] font-black shadow-lg shadow-blue-200">CARI</button>
                </form>

                <div class="glass-card p-8 rounded-[3rem] shadow-xl overflow-x-auto">
                    <table class="w-full text-left border-separate border-spacing-y-4">
                        <thead>
                            <tr class="text-slate-400 text-xs uppercase tracking-[0.2em]">
                                <th class="px-6 pb-2 font-black">Siswa</th>
                                <th class="px-6 pb-2 font-black">Kelas</th>
                                <th class="px-6 pb-2 font-black">No. Telepon</th>
                                <th class="px-6 pb-2 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(mysqli_num_rows($query) > 0) : ?>
                                <?php while($row = mysqli_fetch_assoc($query)) : ?>
                                <tr class="bg-white/30 hover:bg-white/60 transition-all group">
                                    <td class="p-5 rounded-l-[2rem] flex items-center min-w-[200px]">
                                        <div class="w-12 h-12 bg-gradient-to-tr from-indigo-100 to-blue-100 text-blue-600 rounded-2xl mr-4 flex items-center justify-center font-black text-xl shadow-inner group-hover:scale-110 transition-transform">
                                            <?= strtoupper(substr($row['nama_anggota'], 0, 1)); ?>
                                        </div>
                                        <div>
                                            <p class="font-bold text-slate-800 text-lg leading-tight"><?= htmlspecialchars($row['nama_anggota']); ?></p>
                                            <p class="text-[10px] font-bold text-slate-400 uppercase"><?= $row['nis']; ?></p>
                                        </div>
                                    </td>
                                    <td class="p-5 font-bold text-slate-600"><?= $row['kelas']; ?></td>
                                    <td class="p-5">
                                        <span class="bg-blue-100 text-blue-600 px-3 py-1 rounded-lg text-xs font-bold">
                                            <?= $row['no_tlp']; ?>
                                        </span>
                                    </td>
                                    <td class="p-5 rounded-r-[2rem] text-center">
                                        <div class="flex justify-center gap-2">
                                            <a href="edit.php?id=<?= $row['id_anggota']; ?>" class="w-10 h-10 flex items-center justify-center bg-white rounded-xl text-blue-600 shadow-sm hover:bg-blue-600 hover:text-white transition-all">✏️</a>
                                            <a href="hapus.php?id=<?= $row['id_anggota']; ?>" onclick="return confirm('Hapus?')" class="w-10 h-10 flex items-center justify-center bg-white rounded-xl text-red-600 shadow-sm hover:bg-red-600 hover:text-white transition-all">🗑️</a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else : ?>
                                <tr><td colspan="4" class="text-center py-20 text-slate-400 font-bold uppercase">Kosong 🔍</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>