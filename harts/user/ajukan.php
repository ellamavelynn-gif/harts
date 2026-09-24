<?php
session_start();
include '../config/koneksi.php';

if (!isset($_SESSION['id_anggota'])) {
    $tujuan = 'ajukan.php' . (!empty($_SERVER['QUERY_STRING']) ? ('?' . $_SERVER['QUERY_STRING']) : '');
    header("Location: login.php?redirect=" . urlencode($tujuan));
    exit;
}

$berhasil = false;
$diblokir = false;
$pesan_blokir = '';

// FIX: Sebelumnya pengecekan "punya denda/telat" cuma jalan SETELAH tombol submit ditekan.
// Sekarang dicek di awal (sebelum form tampil), supaya siswa langsung lihat peringatan
// begitu buka halaman ini, bukan baru ketahuan setelah isi form.
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

function cekTelatAktif($conn, $id_siswa) {
    $q = mysqli_query($conn, "SELECT tanggal_kembali FROM peminjaman WHERE id_anggota = '$id_siswa' AND status = 'Dipinjam'");
    if (!$q) return false;
    while ($r = mysqli_fetch_assoc($q)) {
        $info = hitungTelatSiswa($r['tanggal_kembali']);
        if ($info['telat'] > 0) return true;
    }
    return false;
}

$cek_denda_awal = mysqli_query($conn, "SELECT SUM(nominal) as total FROM kas_denda WHERE id_anggota = '" . $_SESSION['id_anggota'] . "' AND status_bayar = 'Belum Lunas'");
$total_denda_awal = mysqli_fetch_assoc($cek_denda_awal)['total'] ?? 0;

if ($total_denda_awal > 0) {
    $diblokir = true;
    $pesan_blokir = 'Kamu memiliki denda yang belum dilunasi. Selesaikan dulu pembayarannya sebelum meminjam buku baru.';
} elseif (cekTelatAktif($conn, $_SESSION['id_anggota'])) {
    $diblokir = true;
    $pesan_blokir = 'Kamu masih punya buku yang sudah lewat tenggat pengembalian. Kembalikan dulu buku itu sebelum mengajukan pinjaman baru.';
}

if (isset($_POST['proses_pinjam']) && !$diblokir) {
    $id_buku = $_POST['id_buku'];
    $id_siswa = $_SESSION['id_anggota'];
    $jumlah  = $_POST['jumlah_buku'];
    $durasi  = $_POST['durasi_pinjam']; 

    $tgl_pinjam = date('Y-m-d H:i:s');
    $tgl_kembali = date('Y-m-d H:i:s', strtotime("+$durasi days"));
    $status = "Menunggu";

    $cek_pinjaman = mysqli_query($conn, "SELECT tanggal_kembali FROM peminjaman WHERE id_anggota = '$id_siswa' AND status = 'Dipinjam'");
    $punya_denda = false;
    while ($r = mysqli_fetch_assoc($cek_pinjaman)) {
        $tgl_sekarang = new DateTime();
        $tgl_tenggat = new DateTime($r['tanggal_kembali']);
        if ($tgl_sekarang > $tgl_tenggat) {
            $punya_denda = true;
            break;
        }
    }

    if ($punya_denda) {
        echo "<script>
            alert('Maaf, kamu tidak bisa meminjam buku baru karena masih ada denda yang belum dibayar!');
            window.location.href = 'index.php';
        </script>";
        exit;
    }

    $query = "INSERT INTO peminjaman (id_anggota, id_buku, jumlah, tanggal_pinjam, tanggal_kembali, status) 
              VALUES ('$id_siswa', '$id_buku', '$jumlah', '$tgl_pinjam', '$tgl_kembali', '$status')";
  
    if (mysqli_query($conn, $query)) {
        $berhasil = true;
    }
}

$id_buku_pilih = $_GET['id_buku'] ?? '';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajukan Pinjaman - HARTS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/warna.css">
</head>
<body class="min-h-screen flex items-center justify-center relative overflow-hidden bg-[#FFFDF6] p-4">
    
    <div class="absolute -bottom-20 -left-20 w-60 h-60 sm:w-80 sm:h-80 bg-[#E2D4E0] rounded-full blur-3xl opacity-50"></div>
    <div class="absolute -top-20 -right-20 w-60 h-60 sm:w-80 sm:h-80 bg-[#949AB1] rounded-full blur-3xl opacity-30"></div>

    <div class="max-w-md w-full glass-card p-6 sm:p-10 rounded-3xl sm:rounded-[3rem] shadow-2xl relative z-10">
        <?php if($diblokir) : ?>
            <div class="text-center py-4 sm:py-6">
                <div class="w-16 h-16 sm:w-20 sm:h-20 bg-red-100 text-red-500 rounded-full flex items-center justify-center text-3xl sm:text-4xl mx-auto mb-4 sm:mb-6 shadow-inner">
                    ⚠️
                </div>
                <h2 class="text-xl sm:text-2xl font-black text-[#4C5372]">Tidak Bisa Meminjam</h2>
                <p class="text-[#7C7E9D] mt-2 mb-8 sm:mb-10 text-xs sm:text-sm font-medium leading-relaxed"><?= htmlspecialchars($pesan_blokir); ?></p>

                <a href="pinjamanku.php" class="block w-full py-3.5 sm:py-4 bg-[#4C5372] text-white rounded-xl sm:rounded-2xl font-black uppercase text-[10px] tracking-widest shadow-xl hover:opacity-90 transition-all mb-3">
                    Cek Pinjamanku
                </a>
                <a href="katalog.php" class="text-center block text-[#7C7E9D] text-[10px] font-bold uppercase tracking-widest hover:text-[#4C5372] transition-colors">Kembali ke Katalog</a>
            </div>
        <?php elseif(!$berhasil) : ?>
            <div class="text-center mb-6 sm:mb-8">
                <span class="text-3xl sm:text-4xl">📖</span>
                <h2 class="text-2xl sm:text-3xl font-black text-[#4C5372] mt-2 sm:mt-4">Detail Pinjam</h2>
                <p class="text-[#7C7E9D] text-xs sm:text-sm mt-1 font-medium">Isi durasi pinjamanmu di bawah ini.</p>
            </div>

            <form action="" method="POST" class="space-y-4 sm:space-y-6">
                <input type="hidden" name="id_buku" value="<?= $id_buku_pilih; ?>">
                
                <div class="space-y-2">
                    <label class="text-[10px] font-black text-[#4C5372] uppercase tracking-widest ml-1">Jumlah Buku</label>
                    <div class="relative">
                        <input type="number" name="jumlah_buku" min="1" max="5" value="1" 
                               class="w-full p-3.5 sm:p-4 rounded-xl sm:rounded-2xl bg-white/50 border-2 border-[#E2D4E0] focus:border-[#4C5372] outline-none transition-all font-bold text-[#4C5372] text-sm sm:text-base" required>
                        <span class="absolute right-4 top-3.5 sm:top-4 opacity-30 text-xs">Max 5</span>
                    </div>
                </div>

                <div class="space-y-2">
                    <label class="text-[10px] font-black text-[#4C5372] uppercase tracking-widest ml-1">Durasi Pinjam</label>
                    <select name="durasi_pinjam" 
                            class="w-full p-3.5 sm:p-4 rounded-xl sm:rounded-2xl bg-white/50 border-2 border-[#E2D4E0] focus:border-[#4C5372] outline-none transition-all font-bold text-[#4C5372] text-xs sm:text-sm appearance-none" required>
                        <?php for($i=1; $i<=7; $i++) : ?>
                            <option value="<?= $i; ?>" class="font-bold"><?= $i; ?> Hari (Tenggat: <?= date('d M', strtotime("+$i days")) ?>)</option>
                        <?php endfor; ?>
                    </select>
                </div>

                <div class="flex flex-col gap-3 pt-4">
                    <button type="submit" name="proses_pinjam" 
                            class="w-full py-3.5 sm:py-4 bg-[#4C5372] text-white rounded-xl sm:rounded-2xl font-black shadow-lg hover:scale-[1.02] active:scale-[0.98] transition-all uppercase tracking-widest text-[10px]">
                        Ajukan Sekarang
                    </button>
                    <a href="katalog.php" class="text-center text-[#7C7E9D] text-[10px] font-bold uppercase tracking-widest hover:text-[#4C5372] transition-colors">Batal</a>
                </div>
            </form>

        <?php else : ?>
            <div class="text-center py-4 sm:py-6">
                <div class="w-16 h-16 sm:w-20 sm:h-20 bg-emerald-100 text-emerald-500 rounded-full flex items-center justify-center text-3xl sm:text-4xl mx-auto mb-4 sm:mb-6 shadow-inner animate-bounce">
                    ✓
                </div>
                <h2 class="text-xl sm:text-2xl font-black text-[#4C5372]">Berhasil Diajukan!</h2>
                <p class="text-[#7C7E9D] mt-2 mb-8 sm:mb-10 text-xs sm:text-sm font-medium leading-relaxed">Admin akan segera memproses permintaanmu. <br> Cek status secara berkala ya!</p>
                
                <a href="pinjamanku.php" class="block w-full py-3.5 sm:py-4 bg-[#4C5372] text-white rounded-xl sm:rounded-2xl font-black uppercase text-[10px] tracking-widest shadow-xl hover:opacity-90 transition-all">
                    Cek Status Pinjaman
                </a>
            </div>
        <?php endif; ?>
    </div>

</body>
</html>
