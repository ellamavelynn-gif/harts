<?php
session_start();
include '../config/koneksi.php';

// Proteksi: Cek login siswa
if (!isset($_SESSION['id_anggota'])) {
    header("Location: login.php");
    exit;
}

$current_page = basename($_SERVER['PHP_SELF']);
$id_siswa = $_SESSION['id_anggota'];

// --- LOGIKA CEK DENDA (Untuk Notifikasi Sidebar) ---
$cek_denda = mysqli_query($conn, "SELECT SUM(nominal) as total FROM kas_denda WHERE id_anggota = '$id_siswa' AND status_bayar = 'Belum Lunas'");
$data_denda = mysqli_fetch_assoc($cek_denda);
$punya_hutang = ($data_denda['total'] > 0);
// -------------------------------------------

// QUERY DATA BUKU (Hanya yang sedang diproses atau dipinjam)
$query = mysqli_query($conn, "SELECT peminjaman.*, buku.judul_buku, pengarang.nama_pengarang 
                              FROM peminjaman 
                              JOIN buku ON peminjaman.id_buku = buku.id_buku 
                              JOIN pengarang ON buku.id_pengarang = pengarang.id_pengarang
                              WHERE peminjaman.id_anggota = '$id_siswa' 
                              AND (peminjaman.status = 'Menunggu' 
                                   OR peminjaman.status = 'Dipinjam' 
                                   OR peminjaman.status IS NULL 
                                   OR peminjaman.status = '')
                              ORDER BY peminjaman.kode_transaksi DESC") 
         or die(mysqli_error($conn));
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pinjamanku - HARTS</title>
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
            
            <div class="mb-12">
                <h1 class="text-5xl font-black tracking-tight">Pinjamanku ⏳</h1>
                <p class="font-medium mt-2 text-xl italic opacity-70">Pantau status buku dan tenggat waktu pengembalian.</p>
            </div>

            <div class="glass-card rounded-[2.5rem] overflow-hidden shadow-xl border border-white/20 bg-white/50">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-[#4C5372]/5">
                        <tr>
                            <th class="p-8 font-black uppercase text-[10px] tracking-[0.2em] opacity-50">Info Buku</th>
                            <th class="p-8 font-black uppercase text-[10px] tracking-[0.2em] opacity-50 text-center">Tgl Pinjam</th>
                            <th class="p-8 font-black uppercase text-[10px] tracking-[0.2em] opacity-50 text-center">Tenggat</th>
                            <th class="p-8 font-black uppercase text-[10px] tracking-[0.2em] opacity-50 text-center">Jumlah</th>
                            <th class="p-8 font-black uppercase text-[10px] tracking-[0.2em] opacity-50 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#4C5372]/5">
                        <?php if(mysqli_num_rows($query) > 0) : ?>
                            <?php while($data = mysqli_fetch_assoc($query)) : ?>
                            <tr class="hover:bg-white/40 transition-all group">
                                <td class="p-8">
                                    <div class="font-black text-xl leading-tight group-hover:text-[#4C5372] transition-colors uppercase italic tracking-tighter">
                                        <?= htmlspecialchars($data['judul_buku']); ?>
                                    </div>
                                    <div class="text-xs font-semibold opacity-40 mt-1 uppercase italic">By: <?= htmlspecialchars($data['nama_pengarang']); ?></div>
                                </td>
                                
                                <td class="p-8 text-center">
                                    <span class="font-bold text-sm">
                                        <?= (!empty($data['tanggal_pinjam']) && $data['tanggal_pinjam'] != '0000-00-00 00:00:00') 
                                            ? date('d M Y', strtotime($data['tanggal_pinjam'])) 
                                            : '<span class="opacity-30 italic text-xs tracking-widest uppercase">PROSES</span>'; ?>
                                    </span>
                                </td>

                                <td class="p-8 text-center">
                                    <?php if(!empty($data['tanggal_kembali']) && $data['tanggal_kembali'] != '0000-00-00 00:00:00') : ?>
                                        <span class="bg-orange-50 text-orange-600 px-4 py-2 rounded-xl text-[10px] font-black border border-orange-100 uppercase tracking-tighter">
                                            <?= date('d M Y', strtotime($data['tanggal_kembali'])); ?>
                                        </span>
                                    <?php else : ?>
                                        <span class="opacity-30 italic text-[10px] font-black tracking-widest uppercase">WAITING</span>
                                    <?php endif; ?>
                                </td>

                                <td class="p-8 text-center">
                                    <span class="bg-white px-4 py-2 rounded-2xl border border-[#4C5372]/10 font-black text-sm shadow-sm inline-flex items-center">
                                        <?= $data['jumlah'] ?? '1'; ?> <small class="ml-2 text-[9px] opacity-40 uppercase tracking-tighter">PCS</small>
                                    </span>
                                </td>

                                <td class="p-8 text-center">
                                    <?php 
                                        $status = !empty($data['status']) ? strtoupper($data['status']) : 'MENUNGGU';
                                        $badgeClass = ($status == 'DIPINJAM') 
                                            ? 'bg-[#4C5372] text-white shadow-[#4C5372]/20' 
                                            : 'bg-white text-[#4C5372] border border-[#4C5372]/20 shadow-sm';
                                    ?>
                                    <span class="<?= $badgeClass; ?> px-5 py-2 rounded-full text-[10px] font-black tracking-widest shadow-md inline-block">
                                        <?= $status; ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else : ?>
                            <tr>
                                <td colspan="5" class="p-32 text-center">
                                    <div class="flex flex-col items-center opacity-30">
                                        <i data-lucide="ghost" class="w-16 h-16 mb-4"></i>
                                        <p class="font-black italic text-xl uppercase tracking-tighter">Belum ada buku yang dipinjam...</p>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <div class="blob-lilac absolute top-0 right-0 -z-10"></div>
    </div>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>