<?php
session_start();
include '../config/koneksi.php';

// Proteksi Halaman
if (!isset($_SESSION['id_anggota'])) {
    header("Location: login.php");
    exit;
}

$id_siswa = $_SESSION['id_anggota'];
$current_page = basename($_SERVER['PHP_SELF']);
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';

// --- LOGIKA CEK DENDA (Dampak buat User) ---
$cek_denda = mysqli_query($conn, "SELECT SUM(nominal) as total FROM kas_denda WHERE id_anggota = '$id_siswa' AND status_bayar = 'Belum Lunas'");
$data_denda = mysqli_fetch_assoc($cek_denda);
$punya_hutang = ($data_denda['total'] > 0);
// -------------------------------------------

// Query Katalog dengan Fitur Search
$sql = "SELECT b.*, p.nama_pengarang 
        FROM buku b
        LEFT JOIN pengarang p ON b.id_pengarang = p.id_pengarang 
        WHERE b.judul_buku LIKE '%$search%' 
        OR b.kategori LIKE '%$search%'
        OR p.nama_pengarang LIKE '%$search%'
        ORDER BY b.id_buku DESC";
$query_buku = mysqli_query($conn, $sql) or die(mysqli_error($conn));
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-Katalog - HARTS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/warna.css">
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="flex bg-[#FFFDF6]"> 

    <div id="accordian" class="w-80 h-screen glass-sidebar p-8 flex flex-col shadow-2xl fixed inset-y-0 left-0 z-50">
        <div class="flex items-center mb-10">
            <div class="bg-white p-2 rounded-xl mr-3 shadow-lg text-[#4C5372] font-black text-xl">H</div>
            <h1 class="text-2xl font-black tracking-tighter uppercase text-[#4C5372]">Harts</h1>
        </div>

        <nav class="space-y-3 flex-1">
            <a href="index.php" class="flex items-center p-4 rounded-2xl transition-all group <?= ($current_page == 'index.php') ? 'nav-item-active' : 'nav-link'; ?>">
                <i data-lucide="layout-dashboard" class="mr-4 w-5 h-5 transition-transform group-hover:scale-110"></i> 
                <span class="font-bold">Beranda</span>
            </a>

            <a href="katalog.php" class="flex items-center p-4 rounded-2xl transition-all group <?= ($current_page == 'katalog.php') ? 'nav-item-active' : 'nav-link'; ?>">
                <i data-lucide="book-open" class="mr-4 w-5 h-5 transition-transform group-hover:scale-110"></i> 
                <span class="font-bold">E-Katalog</span>
            </a>

            <a href="pinjamanku.php" class="flex items-center p-4 rounded-2xl transition-all group <?= ($current_page == 'pinjamanku.php') ? 'nav-item-active' : 'nav-link'; ?>">
                <i data-lucide="timer" class="mr-4 w-5 h-5 transition-transform group-hover:scale-110"></i> 
                <span class="font-bold">Pinjamanku</span>
            </a>

            <a href="denda.php" class="flex items-center p-4 rounded-2xl transition-all group <?= ($current_page == 'denda.php') ? 'nav-item-active' : 'nav-link'; ?>">
                <i data-lucide="wallet" class="mr-4 w-5 h-5 transition-transform group-hover:scale-110"></i> 
                <span class="font-bold">Tagihan Denda</span>
                <?php if ($punya_hutang) : ?>
                    <span class="ml-auto w-2 h-2 bg-red-500 rounded-full animate-pulse"></span>
                <?php endif; ?>
            </a>

            <a href="kartu_digital.php" class="flex items-center p-4 rounded-2xl transition-all group <?= ($current_page == 'kartu_digital.php') ? 'nav-item-active' : 'nav-link'; ?>">
                <i data-lucide="vibrate" class="mr-4 w-5 h-5 transition-transform group-hover:scale-110"></i> 
                <span class="font-bold">Kartu Digital</span>
            </a>
        </nav>

        <a href="logout.php" class="p-4 font-bold flex items-center mt-auto nav-link text-[#4C5372]">
            <i data-lucide="log-out" class="mr-2 w-5 h-5"></i> Keluar
        </a>
    </div>

    <div class="ml-80 flex-1 p-12 relative min-h-screen overflow-y-auto text-[#4C5372]">
        <div class="max-w-6xl mx-auto">
            
            <?php if($punya_hutang): ?>
            <div class="mb-10 p-6 bg-red-50 border-2 border-red-100 rounded-[2.5rem] flex items-center justify-between shadow-sm">
                <div class="flex items-center">
                    <span class="text-3xl mr-4">⚠️</span>
                    <div>
                        <h4 class="font-black text-red-600 uppercase text-xs tracking-widest">Akses Terbatas</h4>
                        <p class="text-red-500/80 font-medium">Kamu memiliki denda yang belum dilunasi. Segera hubungi petugas!</p>
                    </div>
                </div>
                <a href="denda.php" class="px-6 py-3 bg-red-600 text-white rounded-2xl font-black text-[10px] uppercase">Cek Detail</a>
            </div>
            <?php endif; ?>

            <div class="flex justify-between items-center mb-12">
                <div>
                    <h1 class="text-5xl font-black tracking-tight">E-Katalog 📚</h1>
                    <p class="font-medium mt-2 text-xl italic opacity-70">Temukan referensi belajarmu di sini.</p>
                </div>
                
                <form action="" method="GET" class="relative group">
                    <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Cari Judul atau Mapel..." 
                           class="bg-white/70 backdrop-blur-md border border-gray-200 rounded-[2rem] px-8 py-5 w-96 outline-none focus:border-[#4C5372] focus:ring-4 focus:ring-[#4C5372]/5 transition-all font-bold text-[#4C5372] shadow-sm">
                    <button type="submit" class="absolute right-6 top-5 text-xl group-hover:scale-110 transition-transform">🔍</button>
                </form>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 pb-12">
                <?php if(mysqli_num_rows($query_buku) > 0) : ?>
                    <?php while($buku = mysqli_fetch_assoc($query_buku)) : ?>
                    <div class="glass-card p-6 rounded-[2.5rem] flex flex-col shadow-lg border border-white/20 hover:scale-[1.03] transition-all bg-white/50 relative overflow-hidden">
                        
                        <div class="aspect-[4/5] bg-white/40 rounded-[2rem] mb-6 flex items-center justify-center text-5xl shadow-inner italic">📖</div>
                        
                        <div class="flex justify-between items-start mb-3">
                            <span class="text-[9px] font-black bg-[#4C5372]/10 text-[#4C5372] px-3 py-1 rounded-lg uppercase tracking-widest w-fit">
                                <?= htmlspecialchars($buku['kategori']); ?>
                            </span>
                            <div class="bg-[#4C5372] px-3 py-1 rounded-xl shadow-md flex items-center gap-1 shrink-0">
                                <span class="text-[10px]">📍</span>
                                <span class="text-[10px] font-black text-white uppercase"><?= htmlspecialchars($buku['id_rak'] ?? 'A-1'); ?></span>
                            </div>
                        </div>
                        
                        <div class="flex flex-col gap-2 mb-6">
                            <h3 class="text-xl font-black leading-tight flex-1 line-clamp-2 uppercase italic tracking-tighter">
                                <?= htmlspecialchars($buku['judul_buku']); ?>
                            </h3>
                            <p class="opacity-50 text-xs italic font-semibold">By: <?= htmlspecialchars($buku['nama_pengarang']); ?></p>
                        </div>
                        
                        <div class="mt-auto pt-6 flex justify-between items-center border-t border-[#4C5372]/10">
                            <div class="flex flex-col">
                                <span class="text-[9px] font-bold opacity-40 uppercase tracking-widest italic">Tersedia</span>
                                <span class="text-sm font-black"><?= $buku['jumlah_buku']; ?> <small class="text-[10px] opacity-60">BUKU</small></span>
                            </div>
                            
                            <?php if($punya_hutang): ?>
                                <button onclick="alert('Maaf, lunasi denda kamu terlebih dahulu untuk meminjam buku!')" class="px-7 py-3 bg-gray-200 text-gray-400 rounded-2xl font-black text-[10px] uppercase cursor-not-allowed">
                                    LOCKED 🔒
                                </button>
                            <?php elseif($buku['jumlah_buku'] > 0) : ?>
                                <a href="ajukan.php?id_buku=<?= $buku['id_buku']; ?>">
                                    <button class="px-7 py-3 bg-[#4C5372] text-white rounded-2xl font-black text-[10px] uppercase hover:shadow-xl transition-all shadow-lg shadow-[#4C5372]/20">
                                        PINJAM
                                    </button>
                                </a>
                            <?php else : ?>
                                <span class="px-5 py-3 bg-red-50 text-red-500 rounded-2xl font-black text-[10px] uppercase border border-red-100">HABIS</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endwhile; ?>
                <?php else : ?>
                    <div class="col-span-3 text-center py-32 bg-white/30 rounded-[3rem] border-2 border-dashed border-[#4C5372]/10">
                        <span class="text-6xl mb-4 block">🔎</span>
                        <p class="text-[#4C5372] opacity-50 font-black italic text-xl uppercase tracking-tighter">Buku tidak ditemukan...</p>
                        <a href="katalog.php" class="text-[#4C5372] font-bold text-sm underline mt-2 block">Reset Pencarian</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="blob-lilac absolute top-0 right-0 -z-10"></div>
    </div>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>