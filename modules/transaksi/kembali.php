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

// Query hanya ambil yang statusnya 'Dipinjam'
$query_pinjam = mysqli_query($conn, "SELECT peminjaman.*, anggota.nama_anggota, buku.judul_buku 
    FROM peminjaman 
    JOIN anggota ON peminjaman.id_anggota = anggota.id_anggota 
    JOIN buku ON peminjaman.id_buku = buku.id_buku 
    WHERE peminjaman.status = 'Dipinjam'
    ORDER BY peminjaman.tanggal_pinjam ASC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Pengembalian Buku - HARTS</title>
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
    </style>
</head>
<body class="flex">

    <div class="w-80 h-full glass-sidebar p-8 flex flex-col shadow-2xl relative z-50">
        <div class="flex items-center mb-10">
            <div class="bg-blue-600 p-2 rounded-xl mr-3 shadow-lg"><span class="text-white font-black text-xl">H</span></div>
            <h1 class="text-2xl font-black text-slate-800 tracking-tighter uppercase">Harts</h1>
        </div>

        <a href="../../profile.php" class="bg-white/40 border border-white/60 p-5 rounded-[2.5rem] mb-10 flex items-center shadow-sm">
            <div class="w-12 h-12 bg-gradient-to-tr from-blue-600 to-indigo-500 rounded-2xl flex items-center justify-center text-white font-bold text-xl mr-4 shadow-lg"><?= $initial; ?></div>
            <div class="overflow-hidden">
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Admin</p>
                <p class="text-lg font-bold text-slate-800 leading-tight truncate"><?= htmlspecialchars($admin_name); ?></p>
            </div>
        </a>

        <nav class="space-y-4 flex-1">
            <a href="../../index.php" class="flex items-center p-4 text-slate-600 hover:bg-white/50 rounded-2xl transition-all group border border-transparent hover:border-white">
                <span class="mr-3">🏠</span> Dashboard
            </a>
            <a href="pinjam.php" class="flex items-center p-4 text-slate-600 hover:bg-white/50 rounded-2xl transition-all group">
                <span class="mr-3">📖</span> Pinjam Buku
            </a>
            <div class="flex items-center p-4 bg-blue-600 rounded-2xl text-white font-bold shadow-xl shadow-blue-200 transition-all">
                <span class="mr-3 text-lg">🔄</span> Pengembalian
            </div>
        </nav>
    </div>

    <div class="flex-1 h-full overflow-y-auto p-12">
        <div class="max-w-6xl mx-auto">
            <div class="mb-10">
                <h2 class="text-4xl font-black text-slate-800 tracking-tight">Pengembalian Buku 🔄</h2>
                <p class="text-slate-500 font-medium mt-1">Daftar buku yang sedang dipinjam oleh siswa.</p>
            </div>

            <div class="glass-card p-8 rounded-[3rem] shadow-xl">
                <table class="w-full text-left border-separate border-spacing-y-4">
                    <thead>
                        <tr class="text-slate-400 text-xs uppercase tracking-[0.2em]">
                            <th class="px-6 pb-2 font-black">Peminjam</th>
                            <th class="px-6 pb-2 font-black">Judul Buku</th>
                            <th class="px-6 pb-2 font-black">Jatuh Tempo</th>
                            <th class="px-6 pb-2 font-black text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = mysqli_fetch_assoc($query_pinjam)) : ?>
                        <tr class="bg-white/30 hover:bg-white/60 transition-all group">
                            <td class="p-5 rounded-l-[2rem] font-bold text-slate-800">
                                <?= htmlspecialchars($row['nama_anggota']); ?>
                            </td>
                            <td class="p-5 font-medium text-slate-600 italic">
                                "<?= htmlspecialchars($row['judul_buku']); ?>"
                            </td>
                            <td class="p-5 font-bold text-rose-500">
                                <?= date('d M Y', strtotime($row['tanggal_kembali'])); ?>
                            </td>
                            <td class="p-5 rounded-r-[2rem] text-center">
                                <a href="proses_kembali.php?id=<?= $row['kode_transaksi']; ?>" class="bg-blue-600 text-white px-6 py-3 rounded-2xl font-black text-xs hover:bg-blue-700 shadow-md transition-all inline-block uppercase tracking-widest">
                                    Kembalikan
                                </a>
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