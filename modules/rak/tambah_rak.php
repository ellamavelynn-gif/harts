<?php
session_start();
include '../../config/koneksi.php';

// Proteksi Halaman
if (!isset($_SESSION['admin'])) {
    header("Location: ../../login.php");
    exit;
}

// Data Admin untuk Sidebar
$admin_data = $_SESSION['admin'];
$admin_name = isset($admin_data['nama_petugas']) ? $admin_data['nama_petugas'] : "Admin";
$initial    = strtoupper(substr($admin_name, 0, 1));

// Proses Simpan Data
if (isset($_POST['simpan'])) {
    $kode_rak = trim(mysqli_real_escape_string($conn, $_POST['kode_rak']));
    $lokasi   = trim(mysqli_real_escape_string($conn, $_POST['lokasi']));

    if (empty($kode_rak) || empty($lokasi)) {
        header("Location: rak.php?status=error&msg=Gagal! Semua kolom wajib diisi ❌");
        exit;
    }

    $cek_rak = mysqli_query($conn, "SELECT kode_rak FROM rak WHERE kode_rak = '$kode_rak'");
    if (mysqli_num_rows($cek_rak) > 0) {
        header("Location: rak.php?status=error&msg=Gagal! Kode Rak $kode_rak sudah terdaftar ⚠️");
        exit;
    }

    $query = "INSERT INTO rak (kode_rak, lokasi) VALUES ('$kode_rak', '$lokasi')";
    
    if (mysqli_query($conn, $query)) {
        header("Location: rak.php?status=success&msg=Rak $kode_rak Berhasil Ditambahkan! 🎉");
        exit;
    } else {
        header("Location: rak.php?status=error&msg=Terjadi kesalahan database ❌");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Rak - HARTS</title>
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
        .glass-card { background: rgba(255, 255, 255, 0.45); backdrop-filter: blur(20px); border: 1px solid rgba(255, 255, 255, 0.7); }
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

        <nav class="space-y-2 flex-1 overflow-y-auto pr-2">
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em] ml-4 mb-2">Main Menu</p>
            <a href="../../index.php" class="flex items-center p-4 text-slate-600 hover:bg-white/50 rounded-2xl transition-all border border-transparent hover:border-white group">
                <span class="mr-4 group-hover:scale-125 transition-transform">🏠</span> Dashboard
            </a>
            
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em] ml-4 mt-6 mb-2">Layanan</p>
            <a href="../../presensi.php" class="flex items-center p-4 text-slate-600 hover:bg-white/50 rounded-2xl transition-all border border-transparent hover:border-white group">
                <span class="mr-4 group-hover:scale-125 transition-transform">⏱️</span> Presensi Siswa
            </a>
            
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em] ml-4 mt-6 mb-2">Manajemen User</p>
            <a href="../anggota/index.php?page=daftar" class="flex items-center p-4 text-slate-600 hover:bg-white/50 border border-transparent hover:border-white rounded-2xl transition-all group">
                <span class="mr-4 group-hover:scale-125 transition-transform">👥</span> Data Anggota
            </a>
            <a href="../anggota/index.php?page=validasi" class="flex items-center p-4 text-slate-600 hover:bg-white/50 border border-transparent hover:border-white rounded-2xl transition-all group">
                <span class="mr-4 group-hover:scale-125 transition-transform">✅</span> Validasi Akun
            </a>
            
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em] ml-4 mt-6 mb-2">Katalog</p>
            <a href="../buku/index.php" class="flex items-center p-4 text-slate-600 hover:bg-white/50 rounded-2xl transition-all group border border-transparent hover:border-white">
                <span class="mr-4 group-hover:scale-125 transition-transform">📚</span> Katalog Buku
            </a>
            <a href="rak.php" class="flex items-center p-4 border-2 bg-blue-600 text-white font-bold shadow-xl shadow-blue-200 border-blue-600 rounded-2xl transition-all group">
                <span class="mr-4 group-hover:scale-125 transition-transform">🗄️</span> Data Rak
            </a>

            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em] ml-4 mt-6 mb-2">Laporan</p>
            <a href="../../laporan.php" class="flex items-center p-4 text-slate-600 hover:bg-white/50 rounded-2xl transition-all group border border-transparent hover:border-white">
                <span class="mr-4 group-hover:scale-125 transition-transform">📊</span> Laporan Peminjaman
            </a>
        </nav>

        <a href="../../logout.php" class="p-4 text-red-500 font-bold flex items-center hover:bg-red-50/50 rounded-2xl transition-all mt-auto group">
            <span class="mr-4 group-hover:rotate-12 transition-transform">🚪</span> Keluar
        </a>
    </div>

    <div class="flex-1 h-full flex items-center justify-center p-12 bg-white/5 overflow-y-auto">
        
        <div class="max-w-xl w-full glass-card p-12 rounded-[3.5rem] shadow-2xl relative my-auto">
            
            <a href="rak.php" class="absolute top-8 right-8 w-10 h-10 flex items-center justify-center bg-white/50 rounded-full text-slate-500 hover:bg-red-500 hover:text-white transition-all shadow-sm">✕</a>

            <div class="mb-10">
                <h2 class="text-3xl font-black text-slate-800 tracking-tight">Tambah Rak 📦</h2>
                <p class="text-slate-500 font-medium mt-2">Masukkan detail lokasi rak dengan lengkap.</p>
            </div>

            <form method="POST" class="space-y-8">
                <div>
                    <label class="block text-[11px] font-extrabold text-slate-400 uppercase tracking-[0.2em] mb-3 ml-1">Kode Rak</label>
                    <input type="text" name="kode_rak" required placeholder="Contoh: RAK-01" 
                           class="w-full px-6 py-5 rounded-2xl bg-white/60 border border-white/80 focus:bg-white outline-none transition-all font-semibold text-lg shadow-sm focus:ring-4 focus:ring-blue-100">
                </div>

                <div>
                    <label class="block text-[11px] font-extrabold text-slate-400 uppercase tracking-[0.2em] mb-3 ml-1">Lokasi / Keterangan Lantai</label>
                    <input type="text" name="lokasi" required placeholder="Contoh: Lantai 2 Gedung Perpustakaan" 
                           class="w-full px-6 py-5 rounded-2xl bg-white/60 border border-white/80 focus:bg-white outline-none transition-all font-semibold text-lg shadow-sm focus:ring-4 focus:ring-blue-100">
                </div>

                <div class="pt-4">
                    <button type="submit" name="simpan" class="w-full py-5 rounded-[2rem] font-black text-lg text-white bg-blue-600 shadow-xl shadow-blue-200 hover:scale-[1.02] active:scale-95 transition-all">
                        SIMPAN DATA RAK
                    </button>
                </div>
            </form>
        </div>
    </div>

</body>
</html>