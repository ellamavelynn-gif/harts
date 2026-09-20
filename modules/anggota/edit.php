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

// 1. Ambil ID
$id = (isset($_GET['id'])) ? mysqli_real_escape_string($conn, $_GET['id']) : 0;

// 2. Query data
$query = mysqli_query($conn, "SELECT * FROM anggota WHERE id_anggota = '$id'");
$data  = mysqli_fetch_assoc($query);

if (!$data) {
    header("Location: index.php?page=daftar");
    exit;
}

// 3. Cek Data (Fallback Logic)
$display_nis   = isset($data['nis']) ? $data['nis'] : '';
$display_nama  = isset($data['nama_anggota']) ? $data['nama_anggota'] : (isset($data['nama']) ? $data['nama'] : '');
$display_kelas = isset($data['kelas']) ? $data['kelas'] : '';
$display_tlp   = isset($data['no_tlp']) ? $data['no_tlp'] : '';

// 4. Proses Update
if (isset($_POST['update'])) {
    $nama   = mysqli_real_escape_string($conn, $_POST['nama_anggota']);
    $nis    = mysqli_real_escape_string($conn, $_POST['nis']);
    $kelas  = mysqli_real_escape_string($conn, $_POST['kelas']);
    $no_tlp = mysqli_real_escape_string($conn, $_POST['no_tlp']);

    $update_query = mysqli_query($conn, "UPDATE anggota SET 
        nama_anggota='$nama', 
        nis='$nis', 
        kelas='$kelas',
        no_tlp='$no_tlp' 
        WHERE id_anggota='$id'");

    if ($update_query) {
        header("Location: index.php?page=daftar&status=success&msg=Data $nama berhasil diperbarui! 🎉");
        exit;
    } else {
        header("Location: index.php?page=daftar&status=error&msg=Gagal memperbarui data ❌");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Anggota - HARTS Admin</title>
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
<body class="flex flex-col lg:flex-row h-screen overflow-hidden">

    <!-- Overlay Sidebar Mobile -->
    <div id="sidebarOverlay" onclick="toggleSidebar()" class="fixed inset-0 bg-stone-900/50 z-40 hidden lg:hidden backdrop-blur-sm transition-opacity"></div>

    <!-- Sidebar Menu Responsive (Off-Canvas) -->
    <aside id="sidebar" class="fixed lg:static inset-y-0 left-0 w-72 h-full sidebar-theme p-6 flex flex-col shadow-lg lg:shadow-none shrink-0 z-50 transform -translate-x-full lg:translate-x-0 transition-transform duration-300 ease-in-out">
        <!-- Logo -->
        <div class="flex items-center justify-between mb-8 px-2">
            <div class="flex items-center">
                <div class="w-10 h-10 rounded-xl mr-3 shadow-sm flex items-center justify-center text-white shrink-0" style="background-color: var(--taupe-grey);">
                    <i class="fa-solid fa-book-bookmark text-lg"></i>
                </div>
                <div>
                    <h1 class="text-xl font-extrabold tracking-tight uppercase text-white leading-none">Harts</h1>
                    <span class="text-[10px] font-semibold tracking-wider text-white/80 uppercase">Library System</span>
                </div>
            </div>
            <!-- Tombol Close Sidebar Mobile -->
            <button onclick="toggleSidebar()" class="lg:hidden text-white/80 hover:text-white p-1">
                <i class="fa-solid fa-xmark text-xl"></i>
            </button>
        </div>
        
        <!-- User Badge -->
        <a href="../../profile.php" class="p-3.5 rounded-2xl mb-6 flex items-center transition-all group border border-white/20 bg-white/10 hover:bg-white/20">
            <div class="w-9 h-9 rounded-xl flex items-center justify-center font-bold text-sm mr-3 text-white transition-transform group-hover:scale-105 shadow-sm shrink-0" style="background-color: var(--old-rose);">
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
    </aside>

    <!-- Main Content Area -->
    <main class="flex-1 h-full overflow-y-auto p-4 sm:p-8 lg:p-10 flex items-center justify-center">
        <!-- Header Bar Mobile -->
        <button onclick="toggleSidebar()" class="lg:hidden fixed top-4 left-4 z-30 p-2.5 rounded-2xl bg-white shadow-sm border border-stone-200 text-stone-700 hover:bg-stone-50">
            <i class="fa-solid fa-bars text-lg"></i>
        </button>

        <div class="max-w-xl w-full card-custom p-6 sm:p-8 lg:p-10 rounded-3xl relative my-auto mt-12 lg:mt-auto">
            <a href="index.php?page=daftar" class="absolute top-5 right-5 sm:top-6 sm:right-6 w-9 h-9 flex items-center justify-center rounded-xl transition-colors hover:bg-stone-100" style="color: var(--taupe-grey);">
                <i class="fa-solid fa-xmark text-lg"></i>
            </a>

            <div class="mb-6 sm:mb-8 text-center">
                <h2 class="text-xl sm:text-2xl lg:text-3xl font-extrabold tracking-tight" style="color: var(--taupe-grey);">Edit Data Anggota <i class="fa-solid fa-user-pen text-base sm:text-lg ml-1" style="color: var(--old-rose);"></i></h2>
                <p class="text-[11px] sm:text-xs lg:text-sm font-medium opacity-80 mt-1 uppercase tracking-wider" style="color: var(--taupe-grey);">ID Anggota: #<?= htmlspecialchars($id); ?></p>
            </div>

            <form action="" method="POST" class="space-y-4">
                <div>
                    <label class="block text-[10px] font-extrabold uppercase tracking-widest mb-1.5 ml-1 opacity-70" style="color: var(--taupe-grey);">Nomor Induk Siswa (NIS)</label>
                    <input type="text" name="nis" value="<?= htmlspecialchars($display_nis); ?>" required placeholder="Masukkan NIS" 
                           class="w-full px-4 py-3 rounded-xl border outline-none font-semibold text-xs sm:text-sm transition-all focus:bg-stone-50" style="border-color: var(--pale-slate); color: var(--taupe-grey);">
                </div>

                <div>
                    <label class="block text-[10px] font-extrabold uppercase tracking-widest mb-1.5 ml-1 opacity-70" style="color: var(--taupe-grey);">Nama Lengkap Siswa</label>
                    <input type="text" name="nama_anggota" value="<?= htmlspecialchars($display_nama); ?>" required placeholder="Nama Siswa" 
                           class="w-full px-4 py-3 rounded-xl border outline-none font-semibold text-xs sm:text-sm transition-all focus:bg-stone-50" style="border-color: var(--pale-slate); color: var(--taupe-grey);">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[10px] font-extrabold uppercase tracking-widest mb-1.5 ml-1 opacity-70" style="color: var(--taupe-grey);">Kelas</label>
                        <input type="text" name="kelas" value="<?= htmlspecialchars($display_kelas); ?>" required placeholder="X RPL" 
                               class="w-full px-4 py-3 rounded-xl border outline-none font-semibold text-xs sm:text-sm transition-all focus:bg-stone-50" style="border-color: var(--pale-slate); color: var(--taupe-grey);">
                    </div>
                    <div>
                        <label class="block text-[10px] font-extrabold uppercase tracking-widest mb-1.5 ml-1 opacity-70" style="color: var(--taupe-grey);">No. Telepon / WA</label>
                        <input type="text" name="no_tlp" value="<?= htmlspecialchars($display_tlp); ?>" placeholder="08xxx" 
                               class="w-full px-4 py-3 rounded-xl border outline-none font-semibold text-xs sm:text-sm transition-all focus:bg-stone-50" style="border-color: var(--pale-slate); color: var(--taupe-grey);">
                    </div>
                </div>

                <div class="pt-4 flex flex-col sm:flex-row gap-3">
                    <button type="submit" name="update" class="flex-1 py-3.5 rounded-2xl font-extrabold text-xs sm:text-sm text-white shadow-sm hover:opacity-90 active:scale-95 transition-all uppercase tracking-wider" style="background-color: var(--old-rose);">
                        <i class="fa-solid fa-arrows-rotate mr-1.5"></i> Perbarui
                    </button>
                    <a href="index.php?page=daftar" class="flex-1 py-3.5 rounded-2xl font-extrabold text-xs sm:text-sm text-center border transition-all uppercase tracking-wider hover:bg-stone-100" style="border-color: var(--pale-slate); color: var(--taupe-grey);">
                        Batal
                    </a>
                </div>
            </form>
        </div>
    </main>

    <script>
    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebarOverlay');
        
        sidebar.classList.toggle('-translate-x-full');
        overlay.classList.toggle('hidden');
    }
    </script>

</body>
</html>