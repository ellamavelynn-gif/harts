<?php
session_start();
include '../config/koneksi.php';

// PUBLIK: katalog boleh dilihat siapa saja, login hanya wajib saat mau MEMINJAM.
$is_login   = isset($_SESSION['id_anggota']);
$id_siswa   = $is_login ? $_SESSION['id_anggota'] : null;
$current_page = basename($_SERVER['PHP_SELF']);
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';

function hitungTelatSiswa($tgl_kembali) {
    if (empty($tgl_kembali) || $tgl_kembali == '0000-00-00 00:00:00' || $tgl_kembali == '0000-00-00') {
        return ['telat' => 0, 'denda' => 0];
    }
    $tgl_sekarang = new DateTime();
    $tgl_tenggat  = new DateTime($tgl_kembali);
    if ($tgl_sekarang > $tgl_tenggat) {
        $selisih = $tgl_tenggat->diff($tgl_sekarang);
        $telat_hari = (int)$selisih->days;
        if ($telat_hari > 0) {
            return ['telat' => $telat_hari, 'denda' => $telat_hari * 5000];
        }
    }
    return ['telat' => 0, 'denda' => 0];
}

$punya_hutang_denda = false;
$ada_telat = false;
$daftar_telat = [];
$pesan_akses = '';

// FIX: sama seperti di index.php - akses juga diblokir kalau ada peminjaman aktif yang sudah telat.
// Semua pengecekan ini cuma relevan & cuma dijalankan kalau user sedang login.
if ($is_login) {
    $cek_denda = mysqli_query($conn, "SELECT SUM(nominal) as total FROM kas_denda WHERE id_anggota = '$id_siswa' AND status_bayar = 'Belum Lunas'");
    $data_denda = mysqli_fetch_assoc($cek_denda);
    $punya_hutang_denda = ($data_denda['total'] > 0);

    $q_aktif = mysqli_query($conn, "SELECT peminjaman.*, buku.judul_buku FROM peminjaman LEFT JOIN buku ON peminjaman.id_buku = buku.id_buku WHERE peminjaman.id_anggota = '$id_siswa' AND peminjaman.status = 'Dipinjam'");
    if ($q_aktif) {
        while ($rp = mysqli_fetch_assoc($q_aktif)) {
            $info = hitungTelatSiswa($rp['tanggal_kembali']);
            if ($info['telat'] > 0) {
                $rp['telat_hari']     = $info['telat'];
                $rp['estimasi_denda'] = $info['denda'];
                $daftar_telat[] = $rp;
            }
        }
    }
    $ada_telat = count($daftar_telat) > 0;

    $pesan_akses = $ada_telat
        ? 'Kamu punya buku yang sudah lewat tenggat pengembalian.'
        : 'Kamu memiliki denda yang belum dilunasi.';
}
$punya_hutang = ($punya_hutang_denda || $ada_telat);

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

    <!-- INI CSS PAKSA UTK NGE-OVERRIDE WARNA.CSS -->
    <style>
        html, body {
            overflow-y: auto !important;
            height: auto !important;
            max-height: none !important;
        }
    </style>
</head>
<body class="bg-[#FFFDF6] min-h-screen text-[#4C5372]"> 

    <?php include 'topnav.php'; ?>

    <!-- Main Content -->
    <div class="p-4 sm:p-8 lg:p-12 min-h-screen">
        <div class="max-w-6xl mx-auto">

            <?php if(!$is_login): ?>
            <div class="mb-6 p-4 sm:p-5 bg-[#4C5372]/5 border-2 border-[#4C5372]/10 rounded-2xl flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
                <div class="flex items-center">
                    <span class="text-xl sm:text-2xl mr-3">👀</span>
                    <div>
                        <h4 class="font-black text-[#4C5372] uppercase text-[10px] tracking-widest">Mode Lihat-Lihat</h4>
                        <p class="text-xs text-[#4C5372]/70 font-medium">Kamu bisa jelajahi katalog dengan bebas. Untuk meminjam buku, masuk atau daftar dulu ya.</p>
                    </div>
                </div>
                <div class="flex gap-2 w-full sm:w-auto shrink-0">
                    <a href="login.php" class="flex-1 sm:flex-none text-center px-4 py-2 bg-white border-2 border-[#4C5372]/20 text-[#4C5372] rounded-xl font-black text-[9px] uppercase">Masuk</a>
                    <a href="registrasi.php" class="flex-1 sm:flex-none text-center px-4 py-2 bg-[#4C5372] text-white rounded-xl font-black text-[9px] uppercase">Daftar</a>
                </div>
            </div>
            <?php endif; ?>

            <?php if($punya_hutang): ?>
            <div class="mb-6 p-4 bg-red-50 border-2 border-red-100 rounded-2xl flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 shadow-sm animate-bounce">
                <div class="flex items-center">
                    <span class="text-xl sm:text-2xl mr-3">⚠️</span>
                    <div>
                        <h4 class="font-black text-red-600 uppercase text-[10px] tracking-widest">Akses Terbatas</h4>
                        <p class="text-xs text-red-500/80 font-medium"><?= $pesan_akses; ?></p>
                    </div>
                </div>
                <a href="<?= $ada_telat ? 'pinjamanku.php' : 'denda.php'; ?>" class="w-full sm:w-auto text-center px-4 py-2 bg-red-600 text-white rounded-xl font-black text-[9px] uppercase shrink-0">Cek Detail</a>
            </div>
            <?php endif; ?>

            <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-4 mb-6 sm:mb-8">
                <div>
                    <h1 class="text-2xl sm:text-4xl font-black tracking-tight">E-Katalog 📚</h1>
                    <p class="font-medium mt-1 text-xs sm:text-base italic opacity-70">Temukan referensi belajarmu di sini.</p>
                </div>
                
                <form action="" method="GET" class="relative w-full md:w-auto group">
                    <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Cari Judul atau Mapel..." 
                           class="bg-white/70 backdrop-blur-md border border-gray-200 rounded-xl px-4 py-3 w-full md:w-72 lg:w-80 outline-none focus:border-[#4C5372] focus:ring-4 focus:ring-[#4C5372]/5 transition-all font-bold text-[#4C5372] shadow-sm text-xs sm:text-sm pr-10">
                    <button type="submit" class="absolute right-3 top-3 text-base group-hover:scale-110 transition-transform">🔍</button>
                </form>
            </div>

            <!-- Grid Card Buku -->
            <div class="grid grid-cols-2 md:grid-cols-3 gap-3 sm:gap-6 pb-20">
                <?php if(mysqli_num_rows($query_buku) > 0) : ?>
                    <?php while($buku = mysqli_fetch_assoc($query_buku)) : 
                        $ada_foto = (!empty($buku['foto']) && file_exists('../assets/img/buku/' . $buku['foto']));
                    ?>
                    <div class="glass-card p-3 sm:p-4 rounded-2xl flex flex-col shadow-md border border-white/40 hover:scale-[1.02] transition-all bg-white/60 relative overflow-hidden">
                        
                        <!-- Wrapper Gambar Potret 3:4 -->
                        <div class="w-full aspect-[3/4] bg-slate-900/5 rounded-xl mb-3 overflow-hidden flex items-center justify-center shadow-inner relative">
                            <?php if ($ada_foto) : ?>
                                <img src="../assets/img/buku/<?= $buku['foto']; ?>" alt="<?= htmlspecialchars($buku['judul_buku']); ?>" class="absolute inset-0 w-full h-full object-cover blur-md opacity-40">
                                <img src="../assets/img/buku/<?= $buku['foto']; ?>" alt="<?= htmlspecialchars($buku['judul_buku']); ?>" class="relative z-10 max-w-full max-h-full object-contain p-1">
                            <?php else : ?>
                                <span class="text-3xl sm:text-4xl">📖</span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="flex justify-between items-start mb-2 gap-1">
                            <span class="text-[8px] font-black bg-[#4C5372]/10 text-[#4C5372] px-2 py-0.5 rounded-md uppercase tracking-wider w-fit truncate">
                                <?= htmlspecialchars($buku['kategori']); ?>
                            </span>
                            <div class="bg-[#4C5372] px-2 py-0.5 rounded-md shadow-md flex items-center gap-0.5 shrink-0">
                                <span class="text-[8px]">📍</span>
                                <span class="text-[8px] font-black text-white uppercase"><?= htmlspecialchars($buku['id_rak'] ?? 'A-1'); ?></span>
                            </div>
                        </div>
                        
                        <div class="flex flex-col gap-0.5 mb-3">
                            <h3 class="text-xs sm:text-base font-black leading-snug line-clamp-2 uppercase italic tracking-tighter">
                                <?= htmlspecialchars($buku['judul_buku']); ?>
                            </h3>
                            <p class="opacity-50 text-[9px] sm:text-xs italic font-semibold truncate">By: <?= htmlspecialchars($buku['nama_pengarang']); ?></p>
                        </div>
                        
                        <div class="mt-auto pt-2 sm:pt-3 flex justify-between items-center border-t border-[#4C5372]/10">
                            <div class="flex flex-col">
                                <span class="text-[7px] sm:text-[8px] font-bold opacity-40 uppercase tracking-widest italic">Tersedia</span>
                                <span class="text-[10px] sm:text-xs font-black"><?= $buku['jumlah_buku']; ?> <small class="text-[7px] opacity-60">BUKU</small></span>
                            </div>

                            <?php if(!$is_login): ?>
                                <a href="login.php?redirect=<?= urlencode('ajukan.php?id_buku=' . $buku['id_buku']); ?>">
                                    <button class="px-2.5 py-1.5 bg-[#4C5372] text-white rounded-lg font-black text-[8px] sm:text-[9px] uppercase hover:shadow-md transition-all inline-flex items-center gap-1">
                                        <i data-lucide="log-in" class="w-3 h-3"></i> Masuk
                                    </button>
                                </a>
                            <?php elseif($punya_hutang): ?>
                                <button onclick="alert('<?= $ada_telat ? 'Maaf, kembalikan dulu buku yang sudah telat sebelum meminjam lagi!' : 'Maaf, lunasi denda kamu terlebih dahulu!'; ?>')" class="px-2.5 py-1.5 bg-gray-200 text-gray-400 rounded-lg font-black text-[8px] sm:text-[9px] uppercase cursor-not-allowed">
                                    🔒
                                </button>
                            <?php elseif($buku['jumlah_buku'] > 0) : ?>
                                <a href="ajukan.php?id_buku=<?= $buku['id_buku']; ?>">
                                    <button class="px-3 py-1.5 bg-[#4C5372] text-white rounded-lg font-black text-[8px] sm:text-[9px] uppercase hover:shadow-md transition-all">
                                        PINJAM
                                    </button>
                                </a>
                            <?php else : ?>
                                <span class="px-2 py-1 bg-red-50 text-red-500 rounded-lg font-black text-[8px] uppercase border border-red-100">HABIS</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endwhile; ?>
                <?php else : ?>
                    <div class="col-span-full text-center py-16 bg-white/30 rounded-2xl border-2 border-dashed border-[#4C5372]/10 p-4">
                        <span class="text-4xl mb-3 block">🔎</span>
                        <p class="text-[#4C5372] opacity-50 font-black italic text-base uppercase tracking-tighter">Buku tidak ditemukan...</p>
                        <a href="katalog.php" class="text-[#4C5372] font-bold text-xs underline mt-2 block">Reset Pencarian</a>
                    </div>
                <?php endif; ?>
            </div>

        </div>
    </div>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>
