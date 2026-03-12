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

// --- LOGIKA CEK DENDA (Hanya untuk Notifikasi Sidebar) ---
$query_total = mysqli_query($conn, "SELECT SUM(nominal) as total FROM kas_denda WHERE id_anggota = '$id_siswa' AND status_bayar = 'Belum Lunas'");
$total_tagihan = mysqli_fetch_assoc($query_total)['total'] ?? 0;
$punya_hutang = ($total_tagihan > 0);

// Ambil riwayat denda lengkap
$query_denda = mysqli_query($conn, "SELECT * FROM kas_denda WHERE id_anggota = '$id_siswa' ORDER BY status_bayar ASC, tanggal_bayar DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tagihan Denda - HARTS</title>
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
        <div class="max-w-5xl mx-auto">
            
            <div class="mb-12">
                <h1 class="text-5xl font-black tracking-tight">Tagihan Denda 💸</h1>
                <p class="font-medium mt-2 text-xl italic opacity-70">Pantau kewajiban dan status pembayaran denda kamu.</p>
            </div>

            <div class="bg-white/60 backdrop-blur-md rounded-[3rem] p-8 border border-white shadow-xl overflow-hidden">
                <table class="w-full text-left border-separate border-spacing-y-4">
                    <thead>
                        <tr class="text-[#4C5372] text-[10px] uppercase tracking-[0.2em] font-black opacity-40">
                            <th class="px-8 pb-2">Tanggal</th>
                            <th class="px-8 pb-2">Keterangan</th>
                            <th class="px-8 pb-2">Status</th>
                            <th class="px-8 pb-2 text-right">Nominal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(mysqli_num_rows($query_denda) > 0): ?>
                            <?php while($d = mysqli_fetch_assoc($query_denda)) : 
                                $is_lunas = ($d['status_bayar'] == 'Lunas');
                                $status_class = $is_lunas ? 'bg-emerald-50 text-emerald-600 border-emerald-100' : 'bg-amber-50 text-amber-600 border-amber-100';
                            ?>
                            <tr class="bg-white/80 hover:bg-white transition-all shadow-sm group">
                                <td class="px-8 py-6 rounded-l-[2rem] font-bold text-sm opacity-60">
                                    <?= date('d M Y', strtotime($d['tanggal_bayar'])); ?>
                                </td>
                                <td class="px-8 py-6">
                                    <div class="font-black text-lg uppercase italic tracking-tighter"><?= $d['keterangan']; ?></div>
                                    <div class="text-[9px] text-gray-400 font-bold uppercase tracking-widest mt-1">ID: #<?= $d['kode_transaksi']; ?></div>
                                </td>
                                <td class="px-8 py-6">
                                    <span class="<?= $status_class; ?> px-4 py-1.5 rounded-full text-[10px] font-black uppercase tracking-wider border">
                                        <?= $d['status_bayar'] ?? 'Belum Lunas'; ?>
                                    </span>
                                </td>
                                <td class="px-8 py-6 rounded-r-[2rem] text-right">
                                    <span class="font-black text-xl italic">
                                        Rp <?= number_format($d['nominal'], 0, ',', '.'); ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="text-center py-24">
                                    <div class="flex flex-col items-center opacity-30">
                                        <i data-lucide="check-circle-2" class="w-16 h-16 mb-4"></i>
                                        <p class="font-black italic text-xl uppercase tracking-tighter">Bersih! Tidak ada denda.</p>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="mt-8">
                <div class="p-8 bg-white border-2 border-[#4C5372]/5 rounded-[3rem] text-[#4C5372] shadow-sm flex items-center gap-6">
                    <div class="w-14 h-14 bg-amber-50 rounded-[1.5rem] flex items-center justify-center text-amber-500 shrink-0">
                        <i data-lucide="info" class="w-7 h-7"></i>
                    </div>
                    <div>
                        <h4 class="font-black uppercase text-xs tracking-widest mb-1">Cara Pembayaran</h4>
                        <p class="text-sm font-medium leading-relaxed opacity-70 italic">Silakan hubungi petugas perpustakaan di meja depan untuk melakukan pembayaran denda agar akses peminjaman kamu segera aktif kembali.</p>
                    </div>
                </div>
            </div>

        </div>
        
        <div class="blob-lilac absolute top-0 right-0 -z-10"></div>
    </div>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>