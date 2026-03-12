<?php
session_start();
include '../config/koneksi.php';

if (!isset($_SESSION['id_anggota'])) {
    header("Location: login.php");
    exit;
}

$berhasil = false;

if (isset($_POST['proses_pinjam'])) {
    $id_buku = $_POST['id_buku'];
    $id_siswa = $_SESSION['id_anggota'];
    $jumlah  = $_POST['jumlah_buku'];
    $durasi  = $_POST['durasi_pinjam']; 

    $tgl_pinjam = date('Y-m-d H:i:s');
    $tgl_kembali = date('Y-m-d H:i:s', strtotime("+$durasi days"));
    $status = "Menunggu";

    // Cek denda/pinjaman aktif
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
<body class="min-h-screen flex items-center justify-center relative overflow-hidden bg-[#FFFDF6]">
    
    <div class="absolute -bottom-20 -left-20 w-80 h-80 bg-[#E2D4E0] rounded-full blur-3xl opacity-50"></div>
    <div class="absolute -top-20 -right-20 w-80 h-80 bg-[#949AB1] rounded-full blur-3xl opacity-30"></div>

    <div class="max-w-md w-full glass-card p-10 shadow-2xl relative z-10 mx-4">
        <?php if(!$berhasil) : ?>
            <div class="text-center mb-8">
                <span class="text-4xl">📖</span>
                <h2 class="text-3xl font-black text-[#4C5372] mt-4">Detail Pinjam</h2>
                <p class="text-[#7C7E9D] text-sm mt-1 font-medium">Isi durasi pinjamanmu di bawah ini.</p>
            </div>

            <form action="" method="POST" class="space-y-6">
                <input type="hidden" name="id_buku" value="<?= $id_buku_pilih; ?>">
                
                <div class="space-y-2">
                    <label class="text-[10px] font-black text-[#4C5372] uppercase tracking-widest ml-1">Jumlah Buku</label>
                    <div class="relative">
                        <input type="number" name="jumlah_buku" min="1" max="5" value="1" 
                               class="w-full p-4 rounded-2xl bg-white/50 border-2 border-[#E2D4E0] focus:border-[#4C5372] outline-none transition-all font-bold text-[#4C5372]" required>
                        <span class="absolute right-4 top-4 opacity-30 text-xs">Max 5</span>
                    </div>
                </div>

                <div class="space-y-2">
                    <label class="text-[10px] font-black text-[#4C5372] uppercase tracking-widest ml-1">Durasi Pinjam</label>
                    <select name="durasi_pinjam" 
                            class="w-full p-4 rounded-2xl bg-white/50 border-2 border-[#E2D4E0] focus:border-[#4C5372] outline-none transition-all font-bold text-[#4C5372] appearance-none" required>
                        <?php for($i=1; $i<=7; $i++) : ?>
                            <option value="<?= $i; ?>" class="font-bold"><?= $i; ?> Hari (Tenggat: <?= date('d M', strtotime("+$i days")) ?>)</option>
                        <?php endfor; ?>
                    </select>
                </div>

                <div class="flex flex-col gap-3 pt-4">
                    <button type="submit" name="proses_pinjam" 
                            class="w-full py-4 bg-[#4C5372] text-white rounded-2xl font-black shadow-lg hover:scale-[1.02] active:scale-[0.98] transition-all uppercase tracking-widest text-[10px]">
                        Ajukan Sekarang
                    </button>
                    <a href="katalog.php" class="text-center text-[#7C7E9D] text-[10px] font-bold uppercase tracking-widest hover:text-[#4C5372] transition-colors">Batal</a>
                </div>
            </form>

        <?php else : ?>
            <div class="text-center py-6">
                <div class="w-20 h-20 bg-emerald-100 text-emerald-500 rounded-full flex items-center justify-center text-4xl mx-auto mb-6 shadow-inner animate-bounce">
                    ✓
                </div>
                <h2 class="text-2xl font-black text-[#4C5372]">Berhasil Diajukan!</h2>
                <p class="text-[#7C7E9D] mt-2 mb-10 text-sm font-medium leading-relaxed">Admin akan segera memproses permintaanmu. <br> Cek status secara berkala ya!</p>
                
                <a href="pinjamanku.php" class="block w-full py-4 bg-[#4C5372] text-white rounded-2xl font-black uppercase text-[10px] tracking-widest shadow-xl hover:opacity-90 transition-all">
                    Cek Status Pinjaman
                </a>
            </div>
        <?php endif; ?>
    </div>

</body>
</html>