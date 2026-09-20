<?php
session_start();
include '../config/koneksi.php';

if (!isset($_SESSION['id_anggota'])) {
    header("Location: login.php");
    exit;
}

$is_login = true;
$current_page = basename($_SERVER['PHP_SELF']);
$id_siswa = $_SESSION['id_anggota'];

$pesan_sukses = "";
$pesan_gagal = "";

// PROSES PERPANJANG TENGGAT WAKTU (TAMBAH 1 HARI & MAKSIMAL 1 KALI)
if (isset($_POST['request_pengadaan'])) {
    $id_peminjaman = (int)$_POST['id_peminjaman'];
    $sisa_hari     = (int)$_POST['sisa_hari'];
    $telat_hari    = (int)$_POST['telat_hari'];

    // Cek dulu ke database apakah transaksi ini sudah pernah diperpanjang
    $cek_status = mysqli_query($conn, "SELECT kode_verifikasi FROM peminjaman WHERE kode_transaksi = '$id_peminjaman' AND id_anggota = '$id_siswa'");
    $data_status = mysqli_fetch_assoc($cek_status);

    if ($data_status && $data_status['kode_verifikasi'] === 'EXTENDED') {
        $pesan_gagal = "Kesempatan memperpanjang buku ini sudah habis (maksimal 1 kali).";
    } else {
        // Validasi backend: Hanya boleh jika H-1 menuju tenggat
        if ($sisa_hari == 1 && $telat_hari == 0) {
            // Update tanggal_kembali (+1 hari) dan tandai kode_verifikasi = 'EXTENDED'
            $update_tenggat = mysqli_query($conn, "UPDATE peminjaman 
                                                   SET tanggal_kembali = DATE_ADD(tanggal_kembali, INTERVAL 1 DAY),
                                                       kode_verifikasi = 'EXTENDED' 
                                                   WHERE kode_transaksi = '$id_peminjaman' 
                                                   AND id_anggota = '$id_siswa'");

            if ($update_tenggat && mysqli_affected_rows($conn) > 0) {
                $pesan_sukses = "Tenggat waktu berhasil diperpanjang 1 hari!";
            } else {
                $pesan_gagal = "Gagal memperpanjang tenggat: " . mysqli_error($conn);
            }
        } else {
            $pesan_gagal = "Perpanjangan hanya dapat dilakukan saat 1 hari sebelum tenggat waktu.";
        }
    }
}

$cek_denda = mysqli_query($conn, "SELECT SUM(nominal) as total FROM kas_denda WHERE id_anggota = '$id_siswa' AND status_bayar = 'Belum Lunas'");
$data_denda = mysqli_fetch_assoc($cek_denda);
$punya_hutang = ($data_denda['total'] > 0);

// FUNGSI DETEKSI TELAT ATAU SISA HARI
function hitungStatusWaktu($tgl_kembali) {
    if (empty($tgl_kembali) || $tgl_kembali == '0000-00-00 00:00:00' || $tgl_kembali == '0000-00-00') {
        return ['telat' => 0, 'denda' => 0, 'sisa_hari' => -1];
    }
    
    $tgl_sekarang = new DateTime(date('Y-m-d'));
    $tgl_tenggat  = new DateTime(date('Y-m-d', strtotime($tgl_kembali)));
    
    if ($tgl_sekarang > $tgl_tenggat) {
        $selisih = $tgl_tenggat->diff($tgl_sekarang);
        $telat_hari = (int)$selisih->days;
        return ['telat' => $telat_hari, 'denda' => $telat_hari * 5000, 'sisa_hari' => 0];
    } else {
        $selisih = $tgl_sekarang->diff($tgl_tenggat);
        $sisa_hari = (int)$selisih->days;
        return ['telat' => 0, 'denda' => 0, 'sisa_hari' => $sisa_hari];
    }
}

$ada_telat = false;
$q_cek_telat = mysqli_query($conn, "SELECT tanggal_kembali FROM peminjaman WHERE id_anggota = '$id_siswa' AND status = 'Dipinjam'");
if ($q_cek_telat) {
    while ($rt = mysqli_fetch_assoc($q_cek_telat)) {
        if (hitungStatusWaktu($rt['tanggal_kembali'])['telat'] > 0) { $ada_telat = true; break; }
    }
}
$punya_hutang = ($punya_hutang || $ada_telat);

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
    <style>
        html, body {
            overflow-y: auto !important;
            height: auto !important;
            max-height: none !important;
        }
    </style>
</head>
<body class="bg-[#FFFDF6] min-h-screen"> 

    <?php include 'topnav.php'; ?>

    <!-- Main Content -->
    <div class="p-4 sm:p-8 lg:p-12 relative min-h-screen text-[#4C5372]">
        <div class="max-w-6xl mx-auto">
            
            <div class="mb-8 sm:mb-12">
                <h1 class="text-3xl sm:text-5xl font-black tracking-tight">Pinjamanku ⏳</h1>
                <p class="font-medium mt-2 text-base sm:text-xl italic opacity-70">Pantau status buku dan tenggat waktu pengembalian.</p>
            </div>

            <!-- Notifikasi Pesan -->
            <?php if (!empty($pesan_sukses)): ?>
                <div class="mb-6 p-4 bg-emerald-100 border border-emerald-300 text-emerald-800 rounded-2xl font-bold text-sm flex items-center gap-2">
                    <i data-lucide="check-circle" class="w-5 h-5"></i> <?= $pesan_sukses; ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($pesan_gagal)): ?>
                <div class="mb-6 p-4 bg-red-100 border border-red-300 text-red-800 rounded-2xl font-bold text-sm flex items-center gap-2">
                    <i data-lucide="alert-circle" class="w-5 h-5"></i> <?= $pesan_gagal; ?>
                </div>
            <?php endif; ?>

            <div class="glass-card rounded-2xl sm:rounded-[2.5rem] overflow-x-auto shadow-xl border border-white/20 bg-white/50">
                <table class="w-full text-left border-collapse min-w-[700px]">
                    <thead class="bg-[#4C5372]/5">
                        <tr>
                            <th class="p-4 sm:p-8 font-black uppercase text-[10px] tracking-[0.2em] opacity-50">Info Buku</th>
                            <th class="p-4 sm:p-8 font-black uppercase text-[10px] tracking-[0.2em] opacity-50 text-center">Tgl Pinjam</th>
                            <th class="p-4 sm:p-8 font-black uppercase text-[10px] tracking-[0.2em] opacity-50 text-center">Tenggat</th>
                            <th class="p-4 sm:p-8 font-black uppercase text-[10px] tracking-[0.2em] opacity-50 text-center">Jumlah</th>
                            <th class="p-4 sm:p-8 font-black uppercase text-[10px] tracking-[0.2em] opacity-50 text-center">Status</th>
                            <th class="p-4 sm:p-8 font-black uppercase text-[10px] tracking-[0.2em] opacity-50 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#4C5372]/5">
                        <?php if(mysqli_num_rows($query) > 0) : ?>
                            <?php while($data = mysqli_fetch_assoc($query)) : ?>
                            <?php 
                                $id_pem = $data['kode_transaksi'];
                                $sudah_diperpanjang = ($data['kode_verifikasi'] === 'EXTENDED');
                                
                                $status = !empty($data['status']) ? strtoupper($data['status']) : 'MENUNGGU';
                                $info_waktu = ($status == 'DIPINJAM') ? hitungStatusWaktu($data['tanggal_kembali']) : ['telat' => 0, 'denda' => 0, 'sisa_hari' => -1];

                                if ($info_waktu['telat'] > 0) {
                                    $status_label = 'TERLAMBAT ' . $info_waktu['telat'] . ' HARI';
                                    $badgeClass = 'bg-red-600 text-white shadow-red-600/20';
                                } elseif ($status == 'DIPINJAM') {
                                    $status_label = $status;
                                    $badgeClass = 'bg-[#4C5372] text-white shadow-[#4C5372]/20';
                                } else {
                                    $status_label = $status;
                                    $badgeClass = 'bg-white text-[#4C5372] border border-[#4C5372]/20 shadow-sm';
                                }
                            ?>
                            <tr class="hover:bg-white/40 transition-all group">
                                <td class="p-4 sm:p-8">
                                    <div class="font-black text-base sm:text-xl leading-tight group-hover:text-[#4C5372] transition-colors uppercase italic tracking-tighter">
                                        <?= htmlspecialchars($data['judul_buku']); ?>
                                    </div>
                                    <div class="text-xs font-semibold opacity-40 mt-1 uppercase italic">By: <?= htmlspecialchars($data['nama_pengarang']); ?></div>
                                </td>
                                
                                <td class="p-4 sm:p-8 text-center">
                                    <span class="font-bold text-xs sm:text-sm">
                                        <?= (!empty($data['tanggal_pinjam']) && $data['tanggal_pinjam'] != '0000-00-00 00:00:00') 
                                            ? date('d M Y', strtotime($data['tanggal_pinjam'])) 
                                            : '<span class="opacity-30 italic text-xs tracking-widest uppercase">PROSES</span>'; ?>
                                    </span>
                                </td>

                                <td class="p-4 sm:p-8 text-center">
                                    <?php if(!empty($data['tanggal_kembali']) && $data['tanggal_kembali'] != '0000-00-00 00:00:00') : ?>
                                        <span class="bg-orange-50 text-orange-600 px-3 sm:px-4 py-1.5 sm:py-2 rounded-xl text-[9px] sm:text-[10px] font-black border border-orange-100 uppercase tracking-tighter inline-block">
                                            <?= date('d M Y', strtotime($data['tanggal_kembali'])); ?>
                                        </span>
                                    <?php else : ?>
                                        <span class="opacity-30 italic text-[10px] font-black tracking-widest uppercase">WAITING</span>
                                    <?php endif; ?>
                                </td>

                                <td class="p-4 sm:p-8 text-center">
                                    <span class="bg-white px-3 sm:px-4 py-1.5 sm:py-2 rounded-xl sm:rounded-2xl border border-[#4C5372]/10 font-black text-xs sm:text-sm shadow-sm inline-flex items-center">
                                        <?= $data['jumlah'] ?? '1'; ?> <small class="ml-1.5 sm:ml-2 text-[9px] opacity-40 uppercase tracking-tighter">PCS</small>
                                    </span>
                                </td>

                                <td class="p-4 sm:p-8 text-center">
                                    <span class="<?= $badgeClass; ?> px-4 sm:px-5 py-1.5 sm:py-2 rounded-full text-[9px] sm:text-[10px] font-black tracking-widest shadow-md inline-block">
                                        <?= $status_label; ?>
                                    </span>
                                    <?php if ($info_waktu['telat'] > 0): ?>
                                        <div class="text-[9px] font-bold text-red-500 mt-1.5">Estimasi denda: Rp <?= number_format($info_waktu['denda'], 0, ',', '.'); ?></div>
                                    <?php endif; ?>
                                </td>

                                <!-- AKSI PERPANJANG -->
                                <td class="p-4 sm:p-8 text-center">
                                    <form method="POST" action="">
                                        <input type="hidden" name="id_peminjaman" value="<?= $id_pem; ?>">
                                        <input type="hidden" name="sisa_hari" value="<?= $info_waktu['sisa_hari']; ?>">
                                        <input type="hidden" name="telat_hari" value="<?= $info_waktu['telat']; ?>">

                                        <?php if ($sudah_diperpanjang) : ?>
                                            <button type="button" disabled title="Buku ini sudah pernah diperpanjang 1 kali" class="bg-emerald-50 text-emerald-600 cursor-not-allowed px-3 py-2 rounded-xl text-[10px] font-black uppercase tracking-wider border border-emerald-200">
                                                Sudah Diperpanjang
                                            </button>
                                        <?php elseif ($info_waktu['sisa_hari'] == 1 && $info_waktu['telat'] == 0) : ?>
                                            <button type="submit" name="request_pengadaan" onclick="return confirm('Perpanjang tenggat waktu peminjaman buku ini selama 1 hari? (Kesempatan hanya 1 kali)')" class="bg-indigo-600 hover:bg-indigo-700 text-white px-3 py-2 rounded-xl text-[10px] font-black uppercase tracking-wider shadow-md hover:shadow-lg transition-all inline-flex items-center gap-1">
                                                <i data-lucide="clock" class="w-3.5 h-3.5"></i>
                                                Perpanjang 1 Hari
                                            </button>
                                        <?php else : ?>
                                            <button type="button" disabled title="Tombol hanya aktif 1 hari sebelum tenggat waktu" class="bg-gray-100 text-gray-400 cursor-not-allowed px-3 py-2 rounded-xl text-[10px] font-black uppercase tracking-wider border border-gray-200">
                                                <?= ($info_waktu['telat'] > 0) ? 'Tidak Dapat Diajukan' : 'Belum H-1'; ?>
                                            </button>
                                        <?php endif; ?>
                                    </form>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else : ?>
                            <tr>
                                <td colspan="6" class="p-16 sm:p-32 text-center">
                                    <div class="flex flex-col items-center opacity-30">
                                        <i data-lucide="ghost" class="w-12 h-12 sm:w-16 sm:h-16 mb-4"></i>
                                        <p class="font-black italic text-lg sm:text-xl uppercase tracking-tighter">Belum ada buku yang dipinjam...</p>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>
