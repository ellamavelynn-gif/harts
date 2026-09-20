<?php
session_start();
include '../config/koneksi.php';

if (!isset($_SESSION['id_anggota'])) {
    header("Location: login.php");
    exit;
}

$is_login = true;
$id_siswa = $_SESSION['id_anggota'];
$current_page = basename($_SERVER['PHP_SELF']);

$query_total = mysqli_query($conn, "SELECT SUM(nominal) as total FROM kas_denda WHERE id_anggota = '$id_siswa' AND status_bayar = 'Belum Lunas'");
$total_tagihan = mysqli_fetch_assoc($query_total)['total'] ?? 0;

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

$daftar_telat = [];
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
$punya_hutang = ($total_tagihan > 0) || $ada_telat;

$query_denda = mysqli_query($conn, "SELECT * FROM kas_denda WHERE id_anggota = '$id_siswa' ORDER BY status_bayar ASC, tanggal_bayar DESC");
?>

<!DOCTYPE html>
<html lang="id" style="height: auto !important; overflow: auto !important;">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tagihan Denda - HARTS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/warna.css">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        /* Menghilangkan scrollbar vertikal */
        html {
            scrollbar-width: none;
            -ms-overflow-style: none;
        }
        html::-webkit-scrollbar {
            display: none;
        }
    </style>
</head>
<body class="bg-[#FFFDF6]" style="height: auto !important; overflow: auto !important; min-height: 100vh;">

    <?php include 'topnav.php'; ?>

    <!-- Main Content -->
    <div class="flex-1 p-4 sm:p-8 lg:p-12 relative text-[#4C5372]">
        <div class="max-w-5xl mx-auto">
            
            <div class="mb-8 sm:mb-12">
                <h1 class="text-3xl sm:text-5xl font-black tracking-tight">Tagihan Denda 💸</h1>
                <p class="font-medium mt-2 text-base sm:text-xl italic opacity-70">Pantau kewajiban dan status pembayaran denda kamu.</p>
            </div>

            <?php if ($ada_telat): ?>
            <div class="mb-8 p-5 sm:p-6 bg-amber-50 border-2 border-amber-100 rounded-2xl sm:rounded-[2rem] text-amber-900">
                <div class="flex items-start gap-3 mb-4">
                    <i data-lucide="alarm-clock" class="w-6 h-6 shrink-0 text-amber-600"></i>
                    <div>
                        <h4 class="font-black uppercase text-xs tracking-widest">Ada Buku yang Terlambat!</h4>
                        <p class="text-xs sm:text-sm font-medium opacity-80 mt-1">Nominal di bawah ini estimasi (Rp 5.000/hari) dan <b>belum masuk daftar tagihan resmi</b>. Denda resmi baru tercatat setelah buku dikembalikan ke petugas. Kamu juga tidak bisa mengajukan pinjaman baru selama ini belum beres.</p>
                    </div>
                </div>
                <div class="space-y-2">
                    <?php foreach ($daftar_telat as $item): ?>
                    <div class="bg-white/70 rounded-xl px-4 py-3 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1">
                        <div>
                            <span class="font-black uppercase italic text-sm"><?= htmlspecialchars($item['judul_buku'] ?? 'Buku'); ?></span>
                            <span class="text-[10px] font-bold uppercase tracking-wider text-red-600 ml-2">Telat <?= $item['telat_hari']; ?> Hari</span>
                        </div>
                        <span class="font-black text-sm sm:text-base">Rp <?= number_format($item['estimasi_denda'], 0, ',', '.'); ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <div class="bg-white/60 backdrop-blur-md rounded-2xl sm:rounded-[3rem] p-4 sm:p-8 shadow-xl overflow-x-auto">
                <table class="w-full text-left border-separate border-spacing-y-4 min-w-[500px]">
                    <thead>
                        <tr class="text-[#4C5372] text-[10px] uppercase tracking-[0.2em] font-black opacity-40">
                            <th class="px-4 sm:px-8 pb-2">Tanggal</th>
                            <th class="px-4 sm:px-8 pb-2">Keterangan</th>
                            <th class="px-4 sm:px-8 pb-2">Status</th>
                            <th class="px-4 sm:px-8 pb-2 text-right">Nominal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(mysqli_num_rows($query_denda) > 0): ?>
                            <?php while($d = mysqli_fetch_assoc($query_denda)) : 
                                $is_lunas = ($d['status_bayar'] == 'Lunas');
                                $status_class = $is_lunas ? 'bg-emerald-50 text-emerald-600 border-emerald-100' : 'bg-amber-50 text-amber-600 border-amber-100';
                            ?>
                            <tr class="bg-white/80 hover:bg-white transition-all shadow-sm group">
                                <td class="px-4 sm:px-8 py-4 sm:py-6 rounded-l-2xl sm:rounded-l-[2rem] font-bold text-xs sm:text-sm opacity-60">
                                    <?= date('d M Y', strtotime($d['tanggal_bayar'])); ?>
                                </td>
                                <td class="px-4 sm:px-8 py-4 sm:py-6">
                                    <div class="font-black text-base sm:text-lg uppercase italic tracking-tighter"><?= $d['keterangan']; ?></div>
                                    <div class="text-[9px] text-gray-400 font-bold uppercase tracking-widest mt-1">ID: #<?= $d['kode_transaksi']; ?></div>
                                </td>
                                <td class="px-4 sm:px-8 py-4 sm:py-6">
                                    <span class="<?= $status_class; ?> px-3 sm:px-4 py-1.5 rounded-full text-[9px] sm:text-[10px] font-black uppercase tracking-wider border">
                                        <?= $d['status_bayar'] ?? 'Belum Lunas'; ?>
                                    </span>
                                </td>
                                <td class="px-4 sm:px-8 py-4 sm:py-6 rounded-r-2xl sm:rounded-r-[2rem] text-right">
                                    <span class="font-black text-lg sm:text-xl italic">
                                        Rp <?= number_format($d['nominal'], 0, ',', '.'); ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="text-center py-16 sm:py-24">
                                    <div class="flex flex-col items-center opacity-30">
                                        <i data-lucide="check-circle-2" class="w-12 h-12 sm:w-16 sm:h-16 mb-4"></i>
                                        <p class="font-black italic text-lg sm:text-xl uppercase tracking-tighter">Bersih! Tidak ada denda.</p>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="mt-8 mb-12">
                <div class="p-6 sm:p-8 bg-white rounded-2xl sm:rounded-[3rem] text-[#4C5372] shadow-sm flex flex-col sm:flex-row items-start sm:items-center gap-4 sm:gap-6">
                    <div class="w-12 h-12 sm:w-14 sm:h-14 bg-amber-50 rounded-2xl sm:rounded-[1.5rem] flex items-center justify-center text-amber-500 shrink-0">
                        <i data-lucide="info" class="w-6 h-6 sm:w-7 sm:h-7"></i>
                    </div>
                    <div>
                        <h4 class="font-black uppercase text-xs tracking-widest mb-1">Cara Pembayaran</h4>
                        <p class="text-xs sm:text-sm font-medium leading-relaxed opacity-70 italic">Silakan hubungi petugas perpustakaan di meja depan untuk melakukan pembayaran denda agar akses peminjaman kamu segera aktif kembali.</p>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>
