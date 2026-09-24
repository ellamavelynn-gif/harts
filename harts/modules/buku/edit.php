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

$current_page = 'buku';
$admin_data   = $_SESSION['admin'];
$admin_name   = isset($admin_data['nama_petugas']) ? $admin_data['nama_petugas'] : "Admin";
$initial      = strtoupper(substr($admin_name, 0, 1));

// Query Ambil Data
$query = mysqli_query($conn, "SELECT b.*, p.nama_pengarang 
                              FROM buku b 
                              LEFT JOIN pengarang p ON b.id_pengarang = p.id_pengarang 
                              WHERE b.id_buku = '$id'");
$data = mysqli_fetch_assoc($query);

if (!$data) {
    header("Location: index.php");
    exit;
}

// Menentukan file path foto
$foto_buku = (!empty($data['foto']) && file_exists('../../assets/img/buku/' . $data['foto'])) 
             ? '../../assets/img/buku/' . $data['foto'] 
             : '../../assets/img/buku/default_buku.png';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Buku - HARTS Admin</title>
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
<body class="min-h-screen flex flex-col lg:flex-row overflow-x-hidden">

    <!-- Topbar Mobile Header -->
    <div class="lg:hidden flex items-center justify-between p-4 sidebar-theme text-white sticky top-0 z-40 shadow-md">
        <div class="flex items-center space-x-3">
            <div class="w-8 h-8 rounded-lg flex items-center justify-center text-white" style="background-color: var(--taupe-grey);">
                <i class="fa-solid fa-book-bookmark text-sm"></i>
            </div>
            <span class="font-extrabold tracking-tight uppercase">Harts Library</span>
        </div>
        <button id="toggleSidebar" class="p-2 rounded-lg bg-white/10 hover:bg-white/20 focus:outline-none">
            <i class="fa-solid fa-bars text-xl"></i>
        </button>
    </div>

    <!-- Overlay Mobile Sidebar -->
    <div id="sidebarOverlay" class="fixed inset-0 bg-black/50 z-40 hidden lg:hidden transition-opacity"></div>

    <!-- Sidebar Menu -->
    <aside id="sidebar" class="fixed lg:static top-0 left-0 w-72 h-screen sidebar-theme p-6 flex flex-col shadow-sm z-50 transform -translate-x-full lg:translate-x-0 transition-transform duration-300 ease-in-out">
        <!-- Close Button for Mobile -->
        <button id="closeSidebar" class="lg:hidden absolute top-4 right-4 text-white/80 hover:text-white p-2">
            <i class="fa-solid fa-xmark text-xl"></i>
        </button>

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
        <a href="../../logout.php" class="px-4 py-3 text-white/90 hover:text-white font-bold flex items-center hover:bg-white/10 rounded-xl mt-[auto] transition-colors text-sm">
            <i class="fa-solid fa-arrow-right-from-bracket mr-2.5 text-center w-6"></i> Keluar
        </a>
    </aside>

    <!-- Main Content Area -->
    <main class="flex-1 w-full min-h-screen p-4 sm:p-6 lg:p-10 flex items-center justify-center">
        <div class="max-w-4xl w-full card-custom p-6 sm:p-8 lg:p-10 rounded-3xl relative my-auto">
            <a href="index.php" class="absolute top-4 right-4 sm:top-6 sm:right-6 w-9 h-9 flex items-center justify-center rounded-xl transition-colors hover:bg-stone-100" style="color: var(--taupe-grey);">
                <i class="fa-solid fa-xmark text-lg"></i>
            </a>

            <div class="mb-6 sm:mb-8 text-center pr-6 sm:pr-0">
                <h2 class="text-xl sm:text-2xl lg:text-3xl font-extrabold tracking-tight" style="color: var(--taupe-grey);">Edit Koleksi Buku <i class="fa-solid fa-pen-to-square text-base sm:text-lg ml-1" style="color: var(--old-rose);"></i></h2>
                <p class="text-xs sm:text-sm font-medium opacity-80 mt-1 uppercase tracking-wider" style="color: var(--taupe-grey);">ID Buku: #<?= $data['id_buku']; ?></p>
            </div>

            <form action="proses_edit.php" method="POST" enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <input type="hidden" name="id_buku" value="<?= $data['id_buku']; ?>">
                
                <!-- Kolom Kiri: Preview & Pilih File Sampul -->
                <div class="flex flex-col items-center justify-center p-4 sm:p-6 rounded-2xl border" style="background-color: var(--soft-blush); border-color: var(--pale-slate);">
                    <label class="block text-[10px] font-extrabold uppercase tracking-widest mb-3 opacity-70" style="color: var(--taupe-grey);">Foto Sampul</label>
                    <div class="relative w-32 sm:w-36 h-44 sm:h-48 rounded-2xl overflow-hidden shadow-sm mb-4 border flex items-center justify-center bg-white" style="border-color: var(--pale-slate);">
                        <img id="preview" src="<?= $foto_buku; ?>" class="w-full h-full object-cover">
                    </div>
                    <label class="cursor-pointer text-white font-bold py-2.5 px-4 rounded-xl shadow-sm transition-all active:scale-95 text-xs text-center w-full uppercase tracking-wider block" style="background-color: var(--old-rose);">
                        <i class="fa-solid fa-arrows-rotate mr-1"></i> Ganti Foto
                        <input type="file" name="foto" id="foto_input" class="hidden" accept="image/*" onchange="previewImage(this)">
                    </label>
                    <p class="text-[10px] opacity-60 italic mt-2 text-center" style="color: var(--taupe-grey);">Kosongkan jika tidak ingin mengubah foto</p>
                </div>

                <!-- Kolom Kanan: Detail Input Form -->
                <div class="md:col-span-2 grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="sm:col-span-2">
                        <label class="block text-[10px] font-extrabold uppercase tracking-widest mb-1.5 ml-1 opacity-70" style="color: var(--taupe-grey);">Judul Buku</label>
                        <input type="text" name="judul_buku" value="<?= htmlspecialchars($data['judul_buku']); ?>" required 
                               class="w-full px-4 py-3 rounded-xl border outline-none font-semibold text-sm transition-all focus:bg-stone-50" style="border-color: var(--pale-slate); color: var(--taupe-grey);">
                    </div>

                    <div>
                        <label class="block text-[10px] font-extrabold uppercase tracking-widest mb-1.5 ml-1 opacity-70" style="color: var(--taupe-grey);">Kategori / Mapel</label>
                        <select name="kategori" required class="w-full px-4 py-3 rounded-xl border outline-none font-semibold text-sm cursor-pointer" style="border-color: var(--pale-slate); color: var(--taupe-grey);">
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
                        <label class="block text-[10px] font-extrabold uppercase tracking-widest mb-1.5 ml-1 opacity-70" style="color: var(--taupe-grey);">Nama Pengarang</label>
                        <input type="text" name="nama_pengarang" value="<?= htmlspecialchars($data['nama_pengarang'] ?? ''); ?>" required 
                               class="w-full px-4 py-3 rounded-xl border outline-none font-semibold text-sm transition-all focus:bg-stone-50" style="border-color: var(--pale-slate); color: var(--taupe-grey);">
                    </div>

                    <div>
                        <label class="block text-[10px] font-extrabold uppercase tracking-widest mb-1.5 ml-1 opacity-70" style="color: var(--taupe-grey);">Lokasi Rak</label>
                        <select name="id_rak" required class="w-full px-4 py-3 rounded-xl border outline-none font-semibold text-sm cursor-pointer" style="border-color: var(--pale-slate); color: var(--taupe-grey);">
                            <?php 
                            $rak_q = mysqli_query($conn, "SELECT * FROM rak ORDER BY kode_rak ASC");
                            while($r = mysqli_fetch_assoc($rak_q)): 
                                $s = ($r['kode_rak'] == $data['id_rak']) ? 'selected' : '';
                            ?>
                                <option value="<?= $r['kode_rak']; ?>" <?= $s; ?>><?= $r['kode_rak']; ?> - <?= $r['lokasi']; ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[10px] font-extrabold uppercase tracking-widest mb-1.5 ml-1 opacity-70" style="color: var(--taupe-grey);">Tahun</label>
                            <input type="number" name="tahun_buku" value="<?= $data['tahun_buku']; ?>" required 
                                   class="w-full px-3 py-3 rounded-xl border text-center font-bold text-sm outline-none" style="border-color: var(--pale-slate); color: var(--taupe-grey);">
                        </div>
                        <div>
                            <label class="block text-[10px] font-extrabold uppercase tracking-widest mb-1.5 ml-1 opacity-70" style="color: var(--taupe-grey);">Stok</label>
                            <input type="number" name="jumlah_buku" value="<?= $data['jumlah_buku']; ?>" required 
                                   class="w-full px-3 py-3 rounded-xl border text-center font-bold text-sm outline-none" style="border-color: var(--pale-slate); color: var(--taupe-grey);">
                        </div>
                    </div>

                    <div class="sm:col-span-2 pt-2">
                        <button type="submit" name="update" class="w-full py-3.5 rounded-2xl font-extrabold text-sm text-white shadow-sm hover:opacity-90 active:scale-95 transition-all uppercase tracking-wider" style="background-color: var(--old-rose);">
                            <i class="fa-solid fa-arrows-rotate mr-1.5"></i> Perbarui Data Buku
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </main>

    <!-- Preview Foto & Sidebar Toggle Script -->
    <script>
        // Preview Gambar
        function previewImage(input) {
            const preview = document.getElementById('preview');
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                }
                reader.readAsDataURL(input.files[0]);
            } else {
                preview.src = "<?= $foto_buku; ?>";
            }
        }

        // Toggle Sidebar Mobile
        const sidebar = document.getElementById('sidebar');
        const toggleSidebar = document.getElementById('toggleSidebar');
        const closeSidebar = document.getElementById('closeSidebar');
        const sidebarOverlay = document.getElementById('sidebarOverlay');

        function openNav() {
            sidebar.classList.remove('-translate-x-full');
            sidebarOverlay.classList.remove('hidden');
        }

        function closeNav() {
            sidebar.classList.add('-translate-x-full');
            sidebarOverlay.classList.add('hidden');
        }

        toggleSidebar.addEventListener('click', openNav);
        closeSidebar.addEventListener('click', closeNav);
        sidebarOverlay.addEventListener('click', closeNav);
    </script>
</body>
</html>