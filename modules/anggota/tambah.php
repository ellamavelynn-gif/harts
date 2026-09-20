<?php
session_start();
include '../../config/koneksi.php';

// Proteksi Halaman
if (!isset($_SESSION['admin'])) {
    header("Location: ../../login.php");
    exit;
}

$current_page = 'anggota_daftar';
$admin_data   = $_SESSION['admin'];
$admin_name   = isset($admin_data['nama_petugas']) ? $admin_data['nama_petugas'] : "Admin";
$initial      = strtoupper(substr($admin_name, 0, 1));

// Proses Simpan Data Anggota
if (isset($_POST['simpan'])) {
    if (!$conn) {
        die("Koneksi gagal: " . mysqli_connect_error());
    }

    $nis    = trim(mysqli_real_escape_string($conn, $_POST['nis']));
    $nama   = trim(mysqli_real_escape_string($conn, $_POST['nama_anggota']));
    $kelas  = trim(mysqli_real_escape_string($conn, $_POST['kelas']));
    $no_tlp = trim(mysqli_real_escape_string($conn, $_POST['no_tlp']));

    if (empty($nis) || empty($nama) || empty($kelas)) {
        header("Location: index.php?page=daftar&status=error&msg=Gagal! Semua kolom wajib diisi ❌");
        exit;
    }

    $cek_data = mysqli_query($conn, "SELECT nis FROM anggota WHERE nis = '$nis'");
    if (mysqli_num_rows($cek_data) > 0) {
        header("Location: index.php?page=daftar&status=error&msg=Gagal! NIS $nis sudah terdaftar ⚠️");
        exit;
    }

    $sql = "INSERT INTO anggota (nis, nama_anggota, kelas, no_tlp) VALUES ('$nis', '$nama', '$kelas', '$no_tlp')";

    if (mysqli_query($conn, $sql)) {
        header("Location: index.php?page=daftar&status=success&msg=Data $nama berhasil disimpan! 🎉");
        exit;
    } else {
        header("Location: index.php?page=daftar&status=error&msg=Gagal simpan ke database ❌");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Anggota - HARTS Admin</title>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --soft-blush: #FFDBDA;
            --old-rose: #DB7F8E;
            --pale-slate: #D5C5C8;
            --cool-steel: #9DA3A4;
            --taupe-grey: #604D53;
        }

        body { 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            background-color: var(--soft-blush); 
            color: var(--taupe-grey);
            height: 100vh; 
            overflow: hidden; 
        }

        .sidebar-theme { 
            background-color: var(--cool-steel); 
            border-right: 1px solid rgba(96, 77, 83, 0.12); 
        }

        .nav-item {
            color: #ffffff;
            transition: all 0.2s ease-in-out;
        }

        .nav-item:hover {
            background-color: rgba(255, 255, 255, 0.2);
            color: #ffffff;
        }

        .nav-active { 
            background-color: #ffffff !important; 
            color: var(--taupe-grey) !important; 
            box-shadow: 0 4px 14px rgba(96, 77, 83, 0.12); 
            font-weight: 700;
        }

        .card-custom { 
            background-color: #ffffff; 
            border: 1px solid var(--pale-slate); 
            box-shadow: 0 10px 25px -5px rgba(96, 77, 83, 0.04);
        }

        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-thumb { background: var(--old-rose); border-radius: 10px; }
    </style>
</head>
<body class="flex">

    <!-- Sidebar Menu -->
    <div class="w-72 h-full sidebar-theme p-6 flex flex-col shadow-sm relative z-50">
        <!-- Logo -->
        <div class="flex items-center mb-8 px-2">
            <div class="w-10 h-10 rounded-xl mr-3 shadow-sm flex items-center justify-center text-white" style="background-color: var(--taupe-grey);">
                <i class="fa-solid fa-book-bookmark text-lg"></i>
            </div>
            <div>
                <h1 class="text-xl font-extrabold tracking-tight uppercase text-white leading-none">Harts</h1>
                <span class="text-[10px] font-semibold tracking-wider text-white/80 uppercase">Library System</span>
            </div>
        </div>
        
        <!-- User Badge -->
        <a href="../../profile.php" class="p-3.5 rounded-2xl mb-6 flex items-center transition-all group border border-white/20 bg-white/10 hover:bg-white/20">
            <div class="w-9 h-9 rounded-xl flex items-center justify-center font-bold text-sm mr-3 text-white transition-transform group-hover:scale-105 shadow-sm" style="background-color: var(--old-rose);">
                <?= $initial; ?>
            </div>
            <div class="overflow-hidden">
                <p class="text-[10px] font-bold uppercase tracking-widest text-white/70">Petugas</p>
                <p class="text-sm font-bold leading-tight text-white truncate"><?= htmlspecialchars($admin_name); ?></p>
            </div>
        </a>

        <!-- Navigation Links -->
        <nav class="space-y-1 flex-1 overflow-y-auto pr-1">
            <p class="text-[10px] font-extrabold uppercase tracking-widest ml-3 mb-2 text-white/60">Utama</p>
            
            <a href="../../index.php" class="nav-item flex items-center px-4 py-2.5 rounded-xl text-sm font-medium">
                <i class="fa-solid fa-house w-6 text-center text-sm mr-2.5"></i> Dashboard
            </a>
            
            <p class="text-[10px] font-extrabold uppercase tracking-widest ml-3 mt-6 mb-2 text-white/60">Layanan & Presensi</p>
            
            <a href="../../presensi.php" class="nav-item flex items-center px-4 py-2.5 rounded-xl text-sm font-medium">
                <i class="fa-solid fa-qrcode w-6 text-center text-sm mr-2.5"></i> Scanner Presensi
            </a>
            
            <a href="../../log_kunjungan.php" class="nav-item flex items-center px-4 py-2.5 rounded-xl text-sm font-medium">
                <i class="fa-solid fa-clipboard-user w-6 text-center text-sm mr-2.5"></i> Log Kunjungan
            </a>

            <p class="text-[10px] font-extrabold uppercase tracking-widest ml-3 mt-6 mb-2 text-white/60">Keuangan</p>
            
            <a href="../../kas_denda.php" class="nav-item flex items-center px-4 py-2.5 rounded-xl text-sm font-medium">
                <i class="fa-solid fa-wallet w-6 text-center text-sm mr-2.5"></i> Kas Denda
            </a>

            <p class="text-[10px] font-extrabold uppercase tracking-widest ml-3 mt-6 mb-2 text-white/60">Katalog & Anggota</p>
            
            <a href="index.php?page=daftar" class="nav-item flex items-center px-4 py-2.5 rounded-xl text-sm font-medium nav-active">
                <i class="fa-solid fa-users w-6 text-center text-sm mr-2.5"></i> Data Anggota
            </a>

            <a href="index.php?page=validasi" class="nav-item flex items-center px-4 py-2.5 rounded-xl text-sm font-medium">
                <i class="fa-solid fa-user-check w-6 text-center text-sm mr-2.5"></i> Validasi Akun
            </a>
            
            <a href="../buku/index.php" class="nav-item flex items-center px-4 py-2.5 rounded-xl text-sm font-medium">
                <i class="fa-solid fa-book w-6 text-center text-sm mr-2.5"></i> Katalog Buku
            </a>

            <a href="../rak/rak.php" class="nav-item flex items-center px-4 py-2.5 rounded-xl text-sm font-medium">
                <i class="fa-solid fa-cubes w-6 text-center text-sm mr-2.5"></i> Data Rak
            </a>

            <p class="text-[10px] font-extrabold uppercase tracking-widest ml-3 mt-6 mb-2 text-white/60">Laporan</p>
            
            <a href="../../laporan.php" class="nav-item flex items-center px-4 py-2.5 rounded-xl text-sm font-medium">
                <i class="fa-solid fa-chart-pie w-6 text-center text-sm mr-2.5"></i> Laporan Utama
            </a>
        </nav>
        
        <!-- Logout -->
        <a href="../../logout.php" class="px-4 py-3 text-white/90 hover:text-white font-bold flex items-center hover:bg-white/10 rounded-xl mt-auto transition-colors text-sm">
            <i class="fa-solid fa-arrow-right-from-bracket mr-2.5 text-center w-6"></i> Keluar
        </a>
    </div>

    <!-- Main Content Area -->
    <div class="flex-1 h-full overflow-y-auto p-8 lg:p-10 flex items-center justify-center">
        <div class="max-w-xl w-full card-custom p-8 lg:p-10 rounded-3xl relative my-auto">
            <a href="index.php?page=daftar" class="absolute top-6 right-6 w-9 h-9 flex items-center justify-center rounded-xl transition-colors hover:bg-stone-100" style="color: var(--taupe-grey);">
                <i class="fa-solid fa-xmark text-lg"></i>
            </a>

            <div class="mb-8 text-center">
                <h2 class="text-2xl lg:text-3xl font-extrabold tracking-tight" style="color: var(--taupe-grey);">Tambah Anggota <i class="fa-solid fa-user-plus text-lg ml-1" style="color: var(--old-rose);"></i></h2>
                <p class="text-xs lg:text-sm font-medium opacity-80 mt-1" style="color: var(--taupe-grey);">Daftarkan siswa baru ke dalam sistem perpustakaan.</p>
            </div>

            <form action="" method="POST" class="space-y-4">
                <div>
                    <label class="block text-[10px] font-extrabold uppercase tracking-widest mb-1.5 ml-1 opacity-70" style="color: var(--taupe-grey);">Nomor Induk Siswa (NIS)</label>
                    <input type="text" name="nis" required placeholder="Contoh: 2024001" 
                           class="w-full px-4 py-3 rounded-xl border outline-none font-semibold text-sm transition-all focus:bg-stone-50" style="border-color: var(--pale-slate); color: var(--taupe-grey);">
                </div>

                <div>
                    <label class="block text-[10px] font-extrabold uppercase tracking-widest mb-1.5 ml-1 opacity-70" style="color: var(--taupe-grey);">Nama Lengkap Siswa</label>
                    <input type="text" name="nama_anggota" required placeholder="Masukkan nama lengkap" 
                           class="w-full px-4 py-3 rounded-xl border outline-none font-semibold text-sm transition-all focus:bg-stone-50" style="border-color: var(--pale-slate); color: var(--taupe-grey);">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[10px] font-extrabold uppercase tracking-widest mb-1.5 ml-1 opacity-70" style="color: var(--taupe-grey);">Kelas & Jurusan</label>
                        <input type="text" name="kelas" required placeholder="Contoh: XII RPL 1" 
                               class="w-full px-4 py-3 rounded-xl border outline-none font-semibold text-sm transition-all focus:bg-stone-50" style="border-color: var(--pale-slate); color: var(--taupe-grey);">
                    </div>
                    <div>
                        <label class="block text-[10px] font-extrabold uppercase tracking-widest mb-1.5 ml-1 opacity-70" style="color: var(--taupe-grey);">No. Telepon / WA</label>
                        <input type="text" name="no_tlp" placeholder="08xxxxxxxxxx" 
                               class="w-full px-4 py-3 rounded-xl border outline-none font-semibold text-sm transition-all focus:bg-stone-50" style="border-color: var(--pale-slate); color: var(--taupe-grey);">
                    </div>
                </div>

                <div class="pt-4">
                    <button type="submit" name="simpan" class="w-full py-3.5 rounded-2xl font-extrabold text-sm text-white shadow-sm hover:opacity-90 active:scale-95 transition-all uppercase tracking-wider" style="background-color: var(--old-rose);">
                        <i class="fa-solid fa-floppy-disk mr-1.5"></i> Simpan Anggota
                    </button>
                </div>
            </form>
        </div>
    </div>

</body>
</html>