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

// Logika Simpan Transaksi sesuai struktur tabel di phpMyAdmin
if (isset($_POST['pinjam'])) {
    $id_anggota     = $_POST['id_anggota'];
    $id_buku        = $_POST['id_buku'];
    $tanggal_pinjam = date('Y-m-d');
    $tanggal_kembali = date('Y-m-d', strtotime('+7 days')); // Default pinjam 1 minggu
    $denda          = "0"; // Default denda awal

    // Query insert sesuai kolom di tabel peminjaman kamu
    $sql = "INSERT INTO peminjaman (id_anggota, id_buku, tanggal_pinjam, tanggal_kembali, denda) 
            VALUES ('$id_anggota', '$id_buku', '$tanggal_pinjam', '$tanggal_kembali', '$denda')";
    
    if (mysqli_query($conn, $sql)) {
        echo "<script>
                alert('Buku Berhasil Dipinjam!');
                window.location.href='../../index.php';
              </script>";
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Transaksi Pinjam - HARTS</title>
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

        <a href="../../profile.php" class="bg-white/40 border border-white/60 p-5 rounded-[2.5rem] mb-10 flex items-center shadow-sm hover:bg-white/70 transition-all group">
            <div class="w-12 h-12 bg-gradient-to-tr from-blue-600 to-indigo-500 rounded-2xl flex items-center justify-center text-white font-bold text-xl mr-4 shadow-lg group-hover:scale-110 transition-transform"><?= $initial; ?></div>
            <div class="overflow-hidden">
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Admin</p>
                <p class="text-lg font-bold text-slate-800 leading-tight truncate"><?= htmlspecialchars($admin_name); ?></p>
            </div>
        </a>

        <nav class="space-y-4 flex-1">
            <a href="../../index.php" class="flex items-center p-4 text-slate-600 hover:bg-white/50 rounded-2xl transition-all group">🏠 Dashboard</a>
            <div class="flex items-center p-4 bg-blue-600 rounded-2xl text-white font-bold shadow-xl shadow-blue-200 transition-all">📖 Transaksi Pinjam</div>
            <a href="../anggota/index.php" class="flex items-center p-4 text-slate-600 hover:bg-white/50 rounded-2xl transition-all group">👥 Data Anggota</a>
            <a href="../buku/index.php" class="flex items-center p-4 text-slate-600 hover:bg-white/50 rounded-2xl transition-all group">📚 Katalog Buku</a>
        </nav>
    </div>

    <div class="flex-1 h-full flex items-center justify-center p-12 bg-white/5">
        <div class="max-w-xl w-full glass-card p-12 rounded-[3.5rem] shadow-2xl relative">
            
            <div class="mb-10 text-center">
                <h2 class="text-3xl font-black text-slate-800 tracking-tight">Tambah Pinjaman ✍️</h2>
                <p class="text-slate-500 font-medium mt-2">Pastikan data anggota dan buku sudah benar.</p>
            </div>

            <form action="" method="POST" class="space-y-7">
                <div>
                    <label class="block text-[11px] font-extrabold text-slate-400 uppercase tracking-[0.2em] mb-3 ml-1">Pilih Anggota</label>
                    <select name="id_anggota" required class="w-full px-6 py-5 rounded-2xl bg-white/60 border border-white/80 focus:bg-white outline-none transition-all font-semibold text-lg shadow-sm appearance-none">
                        <option value="">-- Pilih Nama Siswa --</option>
                        <?php
                        $anggota = mysqli_query($conn, "SELECT * FROM anggota ORDER BY nama_anggota ASC");
                        while($a = mysqli_fetch_assoc($anggota)) {
                            echo "<option value='".$a['id_anggota']."'>".$a['nama_anggota']."</option>";
                        }
                        ?>
                    </select>
                </div>

                <div>
                    <label class="block text-[11px] font-extrabold text-slate-400 uppercase tracking-[0.2em] mb-3 ml-1">Judul Buku</label>
                    <select name="id_buku" required class="w-full px-6 py-5 rounded-2xl bg-white/60 border border-white/80 focus:bg-white outline-none transition-all font-semibold text-lg shadow-sm appearance-none">
                        <option value="">-- Pilih Buku --</option>
                        <?php
                        $buku = mysqli_query($conn, "SELECT * FROM buku ORDER BY judul_buku ASC");
                        while($b = mysqli_fetch_assoc($buku)) {
                            echo "<option value='".$b['id_buku']."'>".$b['judul_buku']."</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="pt-4">
                    <button type="submit" name="pinjam" class="w-full py-5 rounded-[2rem] font-black text-lg text-white bg-blue-600 shadow-xl shadow-blue-200 hover:scale-[1.02] active:scale-95 transition-all uppercase tracking-widest">
                        Simpan Transaksi
                    </button>
                </div>
            </form>
        </div>
    </div>

</body>
</html>