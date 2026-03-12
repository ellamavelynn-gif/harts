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

// Proses Simpan Data Buku
if (isset($_POST['submit'])) {
    $judul     = trim(mysqli_real_escape_string($conn, $_POST['judul_buku']));
    $kategori  = mysqli_real_escape_string($conn, $_POST['kategori']); 
    $pgr_input = trim(mysqli_real_escape_string($conn, $_POST['nama_pengarang']));
    $tahun     = (int) $_POST['tahun_buku'];
    $jumlah    = (int) $_POST['jumlah_buku'];
    $id_rak    = mysqli_real_escape_string($conn, $_POST['id_rak']);
    $tgl_today = date('Y-m-d');

    if (empty($judul) || empty($pgr_input) || empty($tahun) || empty($id_rak) || empty($kategori)) {
        header("Location: index.php?status=error&msg=Gagal! Semua kolom (kecuali penerbit) wajib diisi ❌");
        exit;
    }

    // Auto Insert Pengarang
    $res_pgr = mysqli_query($conn, "SELECT id_pengarang FROM pengarang WHERE nama_pengarang = '$pgr_input'");
    if (mysqli_num_rows($res_pgr) > 0) {
        $id_pgr = mysqli_fetch_assoc($res_pgr)['id_pengarang'];
    } else {
        mysqli_query($conn, "INSERT INTO pengarang (nama_pengarang) VALUES ('$pgr_input')");
        $id_pgr = mysqli_insert_id($conn);
    }

    // Insert Buku (id_penerbit dikasih NULL atau 0 karena lo gak mau pake)
    $sql = "INSERT INTO buku (judul_buku, kategori, id_pengarang, id_rak, tahun_buku, jumlah_buku, tanggal_pengadaan) 
            VALUES ('$judul', '$kategori', '$id_pgr', '$id_rak', '$tahun', '$jumlah', '$tgl_today')";
    
    if (mysqli_query($conn, $sql)) {
        header("Location: index.php?status=success&msg=Buku '$judul' berhasil masuk katalog! 🎉");
        exit;
    } else {
        header("Location: index.php?status=error&msg=Database Error: " . mysqli_error($conn));
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Buku - HARTS</title>
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
            <div class="overflow-hidden">
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Admin</p>
                <p class="text-lg font-bold text-slate-800 truncate"><?= htmlspecialchars($admin_name); ?></p>
            </div>
        </a>

        <nav class="space-y-2 flex-1 overflow-y-auto pr-2">
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em] ml-4 mb-2">Main Menu</p>
            <a href="../../index.php" class="flex items-center p-4 text-slate-600 hover:bg-white/50 rounded-2xl transition-all border border-transparent hover:border-white">🏠 Dashboard</a>
            
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em] ml-4 mt-6 mb-2">Layanan</p>
            <a href="../../presensi.php" class="flex items-center p-4 text-slate-600 hover:bg-white/50 rounded-2xl transition-all border border-transparent hover:border-white">⏱️ Presensi Siswa</a>
            
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em] ml-4 mt-6 mb-2">Manajemen User</p>
            <a href="../anggota/index.php?page=daftar" class="flex items-center p-4 text-slate-600 hover:bg-white/50 rounded-2xl transition-all border border-transparent hover:border-white">👥 Data Anggota</a>
            <a href="../anggota/index.php?page=validasi" class="flex items-center p-4 text-slate-600 hover:bg-white/50 rounded-2xl transition-all border border-transparent hover:border-white">✅ Validasi Akun</a>
            
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em] ml-4 mt-6 mb-2">Katalog</p>
            <a href="index.php" class="flex items-center p-4 border-2 bg-blue-600 text-white font-bold shadow-xl border-blue-600 rounded-2xl">📚 Katalog Buku</a>
            <a href="../rak/rak.php" class="flex items-center p-4 text-slate-600 hover:bg-white/50 rounded-2xl transition-all border border-transparent hover:border-white">🗄️ Data Rak</a>

            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em] ml-4 mt-6 mb-2">Laporan</p>
            <a href="../../laporan.php" class="flex items-center p-4 text-slate-600 hover:bg-white/50 rounded-2xl transition-all border border-transparent hover:border-white">📊 Laporan Peminjaman</a>
        </nav>

        <a href="../../logout.php" class="p-4 text-red-500 font-bold flex items-center hover:bg-red-50/50 rounded-2xl transition-all mt-auto group">🚪 Keluar</a>
    </div>

    <div class="flex-1 h-full flex items-center justify-center p-12 bg-white/5 overflow-y-auto">
        <div class="max-w-3xl w-full glass-card p-12 rounded-[3.5rem] shadow-2xl relative">
            <a href="index.php" class="absolute top-8 right-8 w-10 h-10 flex items-center justify-center bg-white/50 rounded-full text-slate-500 hover:bg-red-500 hover:text-white transition-all shadow-sm">✕</a>

            <div class="mb-10 text-center">
                <h2 class="text-3xl font-black text-slate-800 tracking-tight">Input Koleksi Baru ✍️</h2>
                <p class="text-slate-500 font-medium mt-2">Detail Mapel & Lokasi Rak Buku Sekolah.</p>
            </div>

            <form action="" method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="md:col-span-2">
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Judul Buku</label>
                    <input type="text" name="judul_buku" required placeholder="Contoh: Matematika Kelas X" 
                           class="w-full px-6 py-4 rounded-2xl bg-white/60 border border-white/80 focus:bg-white outline-none transition-all font-bold text-lg shadow-sm focus:ring-4 focus:ring-blue-100">
                </div>

                <div>
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Kategori / Mapel</label>
                    <select name="kategori" required class="w-full px-6 py-4 rounded-2xl bg-white/60 border border-white/80 focus:bg-white outline-none transition-all font-bold shadow-sm">
                        <option value="MTK">🧮 Matematika</option>
                        <option value="IPA">🧪 IPA </option>
                        <option value="IPS">🌍 IPS </option>
                        <option value="B.Indonesia"> Bahasa Indonesia</option>
                        <option value="B.Inggris"> Bahasa Inggris</option>
                        <option value="Agama">🕌 Pendidikan Agama</option>
                        <option value="PJOK">⚽ Olahraga</option>
                        <option value="Seni">🎨 Seni Budaya</option>
                        <option value="Fiksi">📖 Novel / Fiksi</option>
                        <option value="Lainnya">🧩 Lain-lain</option>
                    </select>
                </div>

                <div>
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Nama Pengarang</label>
                    <input type="text" name="nama_pengarang" required placeholder="Nama Penulis" 
                           class="w-full px-6 py-4 rounded-2xl bg-white/60 border border-white/80 focus:bg-white outline-none transition-all font-bold shadow-sm">
                </div>

                <div>
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Lokasi Rak 🗄️</label>
                    <select name="id_rak" required class="w-full px-6 py-4 rounded-2xl bg-white/60 border border-white/80 focus:bg-white outline-none transition-all font-bold shadow-sm">
                        <option value="">-- Pilih Rak --</option>
                        <?php 
                        $rak = mysqli_query($conn, "SELECT * FROM rak ORDER BY kode_rak ASC");
                        while($r = mysqli_fetch_assoc($rak)) {
                            echo "<option value='{$r['kode_rak']}'>{$r['kode_rak']} - {$r['lokasi']}</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Tahun</label>
                        <input type="number" name="tahun_buku" required value="<?= date('Y'); ?>" 
                               class="w-full px-4 py-4 rounded-2xl bg-white/60 border border-white/80 text-center font-bold shadow-sm">
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Stok</label>
                        <input type="number" name="jumlah_buku" required placeholder="0" min="1" 
                               class="w-full px-4 py-4 rounded-2xl bg-white/60 border border-white/80 text-center font-bold shadow-sm">
                    </div>
                </div>

                <div class="md:col-span-2 pt-4">
                    <button type="submit" name="submit" class="w-full py-5 rounded-3xl font-black text-lg text-white bg-blue-600 shadow-xl shadow-blue-200 hover:scale-[1.02] active:scale-95 transition-all">
                        SIMPAN DATA BUKU
                    </button>
                </div>
            </form>
        </div>
    </div>

</body>
</html>