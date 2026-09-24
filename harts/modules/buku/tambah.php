<?php
session_start();
include '../../config/koneksi.php';

// Proteksi Halaman
if (!isset($_SESSION['admin'])) {
    header("Location: ../../login.php");
    exit;
}

$current_page = 'buku';
$admin_data   = $_SESSION['admin'];
$admin_name   = isset($admin_data['nama_petugas']) ? $admin_data['nama_petugas'] : "Admin";
$initial      = strtoupper(substr($admin_name, 0, 1));

// Proses Simpan Data Buku Baru
if (isset($_POST['simpan'])) {
    $judul_buku     = mysqli_real_escape_string($conn, $_POST['judul_buku']);
    $kategori       = mysqli_real_escape_string($conn, $_POST['kategori']); 
    $nama_pengarang = mysqli_real_escape_string($conn, $_POST['nama_pengarang']);
    $tahun_buku     = mysqli_real_escape_string($conn, $_POST['tahun_buku']);
    $jumlah_buku    = mysqli_real_escape_string($conn, $_POST['jumlah_buku']);
    $id_rak         = mysqli_real_escape_string($conn, $_POST['id_rak']);

    // Cek atau Tambah Nama Pengarang ke tabel pengarang
    $cek_pengarang = mysqli_query($conn, "SELECT id_pengarang FROM pengarang WHERE nama_pengarang = '$nama_pengarang'");
    if (mysqli_num_rows($cek_pengarang) > 0) {
        $pgr = mysqli_fetch_assoc($cek_pengarang);
        $id_pgr = $pgr['id_pengarang'];
    } else {
        mysqli_query($conn, "INSERT INTO pengarang (nama_pengarang) VALUES ('$nama_pengarang')");
        $id_pgr = mysqli_insert_id($conn);
    }

    // Upload Sampul Foto Buku
    $foto_db = 'default_buku.png';
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === 0) {
        $nama_file   = $_FILES['foto']['name'];
        $tmp_name    = $_FILES['foto']['tmp_name'];
        $ekstensi    = strtolower(pathinfo($nama_file, PATHINFO_EXTENSION));
        
        if (in_array($ekstensi, ['jpg', 'jpeg', 'png'])) {
            $nama_file_baru = uniqid() . '.' . $ekstensi;
            $folder_tujuan  = '../../assets/img/buku/';
            if (!is_dir($folder_tujuan)) {
                mkdir($folder_tujuan, 0777, true);
            }
            if (move_uploaded_file($tmp_name, $folder_tujuan . $nama_file_baru)) {
                $foto_db = $nama_file_baru;
            }
        }
    }

    // Tanggal Pengadaan Otomatis Hari Ini
    $tanggal_pengadaan = date('Y-m-d');

    // Simpan ke tabel buku
    $sql = "INSERT INTO buku (judul_buku, kategori, id_pengarang, tahun_buku, jumlah_buku, id_rak, foto, tanggal_pengadaan) 
            VALUES ('$judul_buku', '$kategori', '$id_pgr', '$tahun_buku', '$jumlah_buku', '$id_rak', '$foto_db', '$tanggal_pengadaan')";

    if (mysqli_query($conn, $sql)) {
        header("Location: index.php?status=success&msg=Data buku berhasil ditambahkan!");
        exit;
    } else {
        // Menampilkan error pasti dari MySQL
        $error_msg = urlencode("Gagal simpan: " . mysqli_error($conn));
        header("Location: index.php?status=error&msg=" . $error_msg);
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Buku - HARTS Admin</title>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
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
            
            <a href="../anggota/index.php?page=daftar" class="nav-item flex items-center px-4 py-2.5 rounded-xl text-sm font-medium">
                <i class="fa-solid fa-users w-6 text-center text-sm mr-2.5"></i> Data Anggota
            </a>

            <a href="../anggota/index.php?page=validasi" class="nav-item flex items-center px-4 py-2.5 rounded-xl text-sm font-medium">
                <i class="fa-solid fa-user-check w-6 text-center text-sm mr-2.5"></i> Validasi Akun
            </a>
            
            <a href="index.php" class="nav-item flex items-center px-4 py-2.5 rounded-xl text-sm font-medium nav-active">
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

    <!-- Main Content Form -->
    <main class="flex-1 h-full overflow-y-auto p-4 sm:p-8 lg:p-10 flex items-center justify-center">
        <!-- Hamburger Mobile -->
        <button onclick="toggleSidebar()" class="lg:hidden fixed top-4 left-4 z-30 p-2.5 rounded-2xl bg-white shadow-sm border border-stone-200 text-stone-700 hover:bg-stone-50">
            <i class="fa-solid fa-bars text-lg"></i>
        </button>

        <div class="max-w-4xl w-full card-custom p-6 sm:p-8 lg:p-10 rounded-3xl relative my-auto mt-12 lg:mt-auto">
            <a href="index.php" class="absolute top-5 right-5 sm:top-6 sm:right-6 w-9 h-9 flex items-center justify-center rounded-xl transition-colors hover:bg-stone-100" style="color: var(--taupe-grey);">
                <i class="fa-solid fa-xmark text-lg"></i>
            </a>

            <div class="mb-6 sm:mb-8 text-center">
                <h2 class="text-xl sm:text-2xl lg:text-3xl font-extrabold tracking-tight" style="color: var(--taupe-grey);">Tambah Koleksi Buku <i class="fa-solid fa-book-medical text-base sm:text-lg ml-1" style="color: var(--old-rose);"></i></h2>
                <p class="text-[11px] sm:text-xs lg:text-sm font-medium opacity-80 mt-1 uppercase tracking-wider" style="color: var(--taupe-grey);">Tambahkan buku baru ke sistem perpustakaan.</p>
            </div>

            <form action="" method="POST" enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Sampul -->
                <div class="flex flex-col items-center justify-center p-4 sm:p-6 rounded-2xl border" style="background-color: var(--soft-blush); border-color: var(--pale-slate);">
                    <label class="block text-[10px] font-extrabold uppercase tracking-widest mb-3 opacity-70" style="color: var(--taupe-grey);">Foto Sampul (3:4)</label>
                    
                    <!-- Container Preview dengan aspect-ratio 3:4 -->
                    <div class="relative w-36 aspect-[3/4] rounded-2xl overflow-hidden shadow-sm mb-4 border flex items-center justify-center bg-white" style="border-color: var(--pale-slate);">
                        <img id="preview" src="../../assets/img/buku/default_buku.png" class="w-full h-full object-cover">
                    </div>

                    <label class="cursor-pointer text-white font-bold py-2.5 px-4 rounded-xl shadow-sm text-xs text-center w-full uppercase tracking-wider block" style="background-color: var(--old-rose);">
                        <i class="fa-solid fa-upload mr-1"></i> Pilih Foto
                        <input type="file" name="foto" id="foto_input" class="hidden" accept="image/*" onchange="previewImage(this)">
                    </label>
                </div>

                <!-- Detail Input -->
                <div class="md:col-span-2 grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="sm:col-span-2">
                        <label class="block text-[10px] font-extrabold uppercase tracking-widest mb-1.5 ml-1 opacity-70" style="color: var(--taupe-grey);">Judul Buku</label>
                        <input type="text" name="judul_buku" required placeholder="Masukkan judul buku" 
                               class="w-full px-4 py-3 rounded-xl border outline-none font-semibold text-xs sm:text-sm transition-all focus:bg-stone-50" style="border-color: var(--pale-slate); color: var(--taupe-grey);">
                    </div>

                    <div>
                        <label class="block text-[10px] font-extrabold uppercase tracking-widest mb-1.5 ml-1 opacity-70" style="color: var(--taupe-grey);">Kategori / Mapel</label>
                        <select name="kategori" required class="w-full px-4 py-3 rounded-xl border outline-none font-semibold text-xs sm:text-sm cursor-pointer" style="border-color: var(--pale-slate); color: var(--taupe-grey);">
                            <?php 
                            $mapels = ['MTK', 'IPA', 'IPS', 'B.Indonesia', 'B.Inggris', 'Agama', 'PJOK', 'Seni', 'Fiksi', 'Lainnya'];
                            foreach($mapels as $m): 
                                echo "<option value='$m'>$m</option>";
                            endforeach; 
                            ?>
                        </select>
                    </div>

                    <div>
                        <label class="block text-[10px] font-extrabold uppercase tracking-widest mb-1.5 ml-1 opacity-70" style="color: var(--taupe-grey);">Nama Pengarang</label>
                        <input type="text" name="nama_pengarang" required placeholder="Masukkan nama pengarang" 
                               class="w-full px-4 py-3 rounded-xl border outline-none font-semibold text-xs sm:text-sm transition-all focus:bg-stone-50" style="border-color: var(--pale-slate); color: var(--taupe-grey);">
                    </div>

                    <div>
                        <label class="block text-[10px] font-extrabold uppercase tracking-widest mb-1.5 ml-1 opacity-70" style="color: var(--taupe-grey);">Lokasi Rak</label>
                        <select name="id_rak" required class="w-full px-4 py-3 rounded-xl border outline-none font-semibold text-xs sm:text-sm cursor-pointer" style="border-color: var(--pale-slate); color: var(--taupe-grey);">
                            <?php 
                            $rak_q = mysqli_query($conn, "SELECT * FROM rak ORDER BY kode_rak ASC");
                            while($r = mysqli_fetch_assoc($rak_q)): 
                            ?>
                                <option value="<?= $r['id_rak']; ?>"><?= $r['kode_rak']; ?> - <?= $r['lokasi']; ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[10px] font-extrabold uppercase tracking-widest mb-1.5 ml-1 opacity-70" style="color: var(--taupe-grey);">Tahun Buku</label>
                            <input type="number" name="tahun_buku" required placeholder="2024" 
                                   class="w-full px-3 py-3 rounded-xl border text-center font-bold text-xs sm:text-sm outline-none" style="border-color: var(--pale-slate); color: var(--taupe-grey);">
                        </div>
                        <div>
                            <label class="block text-[10px] font-extrabold uppercase tracking-widest mb-1.5 ml-1 opacity-70" style="color: var(--taupe-grey);">Jumlah Stok</label>
                            <input type="number" name="jumlah_buku" required placeholder="10" 
                                   class="w-full px-3 py-3 rounded-xl border text-center font-bold text-xs sm:text-sm outline-none" style="border-color: var(--pale-slate); color: var(--taupe-grey);">
                        </div>
                    </div>

                    <div class="sm:col-span-2 pt-2">
                        <button type="submit" name="simpan" class="w-full py-3.5 rounded-2xl font-extrabold text-xs sm:text-sm text-white shadow-sm hover:opacity-90 active:scale-95 transition-all uppercase tracking-wider" style="background-color: var(--old-rose);">
                            <i class="fa-solid fa-floppy-disk mr-1.5"></i> Simpan Data Buku
                        </button>
                    </div>
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

        function previewImage(input) {
            const preview = document.getElementById('preview');
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) { preview.src = e.target.result; }
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
</body>
</html>