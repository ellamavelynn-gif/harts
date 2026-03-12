<?php
session_start();
include '../../config/koneksi.php';

if (!isset($_SESSION['admin'])) {
    header("Location: ../../login.php");
    exit;
}

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$id = mysqli_real_escape_string($conn, $_GET['id']);

$admin_data = $_SESSION['admin'];
$admin_name = isset($admin_data['nama_petugas']) ? $admin_data['nama_petugas'] : "Admin";
$initial    = strtoupper(substr($admin_name, 0, 1));

// Query Ambil Data (Tanpa Join Penerbit)
$query = mysqli_query($conn, "SELECT b.*, p.nama_pengarang 
                              FROM buku b 
                              LEFT JOIN pengarang p ON b.id_pengarang = p.id_pengarang 
                              WHERE b.id_buku = '$id'");
$data = mysqli_fetch_assoc($query);

if (!$data) {
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Buku - HARTS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: linear-gradient(135deg, #fbcfe8 0%, #e9d5ff 40%, #c3dafe 100%); height: 100vh; overflow: hidden; }
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
            <div class="w-12 h-12 bg-gradient-to-tr from-blue-600 to-indigo-500 rounded-2xl flex items-center justify-center text-white font-bold text-xl mr-4 shadow-lg group-hover:scale-110 transition-transform">
                <?= $initial; ?>
            </div>
            <div class="overflow-hidden text-ellipsis">
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Admin</p>
                <p class="text-lg font-bold text-slate-800 truncate"><?= htmlspecialchars($admin_name); ?></p>
            </div>
        </a>

        <nav class="space-y-2 flex-1 overflow-y-auto pr-2">
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em] ml-4 mb-2">Main Menu</p>
            <a href="../../index.php" class="flex items-center p-4 text-slate-600 hover:bg-white/50 rounded-2xl transition-all">🏠 Dashboard</a>
            
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em] ml-4 mt-6 mb-2">Layanan</p>
            <a href="../../presensi.php" class="flex items-center p-4 text-slate-600 hover:bg-white/50 rounded-2xl transition-all">⏱️ Presensi Siswa</a>
            
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em] ml-4 mt-6 mb-2">Katalog</p>
            <a href="index.php" class="flex items-center p-4 border-2 bg-blue-600 text-white font-bold shadow-xl border-blue-600 rounded-2xl">📚 Katalog Buku</a>
            <a href="../rak/rak.php" class="flex items-center p-4 text-slate-600 hover:bg-white/50 rounded-2xl transition-all">🗄️ Data Rak</a>

            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em] ml-4 mt-6 mb-2">Laporan</p>
            <a href="../../laporan.php" class="flex items-center p-4 text-slate-600 hover:bg-white/50 rounded-2xl transition-all">📊 Laporan</a>
        </nav>

        <a href="../../logout.php" class="p-4 text-red-500 font-bold flex items-center hover:bg-red-50/50 rounded-2xl transition-all mt-auto">🚪 Keluar</a>
    </div>

    <div class="flex-1 h-full overflow-y-auto p-12 flex items-center justify-center bg-white/5">
        <div class="max-w-3xl w-full glass-card p-12 rounded-[3.5rem] shadow-2xl relative">
            <a href="index.php" class="absolute top-8 right-8 w-10 h-10 flex items-center justify-center bg-white/50 rounded-full text-slate-500 hover:bg-red-500 hover:text-white transition-all shadow-sm">✕</a>

            <div class="mb-10 text-center">
                <h2 class="text-4xl font-black text-slate-800 tracking-tight">Edit Buku 📖</h2>
                <p class="text-slate-500 font-medium mt-2 italic text-sm">ID Koleksi: #<?= $data['id_buku']; ?></p>
            </div>

            <form action="proses_edit.php" method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <input type="hidden" name="id_buku" value="<?= $data['id_buku']; ?>">
                
                <div class="md:col-span-2">
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Judul Buku</label>
                    <input type="text" name="judul_buku" value="<?= htmlspecialchars($data['judul_buku']); ?>" required 
                           class="w-full px-6 py-4 rounded-2xl bg-white/60 border border-white/80 focus:bg-white outline-none transition-all font-bold text-lg shadow-sm focus:ring-4 focus:ring-blue-100">
                </div>

                <div>
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Kategori / Mapel</label>
                    <select name="kategori" required class="w-full px-6 py-4 rounded-2xl bg-white/60 border border-white/80 focus:bg-white outline-none transition-all font-bold shadow-sm">
                        <?php 
                        $mapels = ['MTK', 'IPA', 'IPS', 'B.Indonesia', 'B.Inggris', 'Agama', 'PJOK', 'Seni', 'Fiksi', 'Lainnya'];
                        foreach($mapels as $m): 
                            $sel = ($data['kategori'] == $m) ? 'selected' : '';
                            echo "<option value='$m' $sel>$m</option>";
                        endforeach; 
                        ?>
                    </select>
                </div>

                <div>
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Penulis</label>
                    <input type="text" name="nama_pengarang" value="<?= htmlspecialchars($data['nama_pengarang'] ?? ''); ?>" required 
                           class="w-full px-6 py-4 rounded-2xl bg-white/60 border border-white/80 focus:bg-white outline-none transition-all font-bold shadow-sm">
                </div>

                <div>
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Lokasi Rak 🗄️</label>
                    <select name="id_rak" required class="w-full px-6 py-4 rounded-2xl bg-white/60 border border-white/80 focus:bg-white outline-none transition-all font-bold shadow-sm">
                        <?php 
                        $rak_q = mysqli_query($conn, "SELECT * FROM rak ORDER BY kode_rak ASC");
                        while($r = mysqli_fetch_assoc($rak_q)): 
                            $s = ($r['kode_rak'] == $data['id_rak']) ? 'selected' : '';
                        ?>
                            <option value="<?= $r['kode_rak']; ?>" <?= $s; ?>><?= $r['kode_rak']; ?> - <?= $r['lokasi']; ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Tahun</label>
                        <input type="number" name="tahun_buku" value="<?= $data['tahun_buku']; ?>" required 
                               class="w-full px-4 py-4 rounded-2xl bg-white/60 border border-white/80 text-center font-bold shadow-sm">
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Stok</label>
                        <input type="number" name="jumlah_buku" value="<?= $data['jumlah_buku']; ?>" required 
                               class="w-full px-4 py-4 rounded-2xl bg-white/60 border border-white/80 text-center font-bold shadow-sm">
                    </div>
                </div>

                <div class="md:col-span-2 pt-6">
                    <button type="submit" name="update" class="w-full py-5 rounded-[2rem] font-black text-xl text-white bg-blue-600 shadow-xl shadow-blue-200 hover:scale-[1.02] active:scale-95 transition-all">
                        UPDATE DATA SEKARANG
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>