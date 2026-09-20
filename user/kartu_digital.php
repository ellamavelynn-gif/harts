<?php
session_start();
include '../config/koneksi.php';

if (!isset($_SESSION['id_anggota'])) {
    header("Location: login.php");
    exit;
}

$is_login = true;
$nama_siswa = $_SESSION['nama_anggota'];
$id_siswa = $_SESSION['id_anggota'];
$current_page = basename($_SERVER['PHP_SELF']);

$cek_denda = mysqli_query($conn, "SELECT SUM(nominal) as total FROM kas_denda WHERE id_anggota = '$id_siswa' AND status_bayar = 'Belum Lunas'");
$data_denda = mysqli_fetch_assoc($cek_denda);
$punya_hutang = ($data_denda['total'] > 0);

$qr_data = "USER-" . $id_siswa; 
$qr_url = "https://qrickit.com/api/qr.php?d=" . $qr_data . "&addtext=HARTS&txtcolor=4C5372&fgdcolor=4C5372&qrsize=300";
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kartu Digital - HARTS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/warna.css">
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="bg-[#FFFDF6] min-h-screen flex flex-col">

    <?php include 'topnav.php'; ?>

    <!-- Main Content -->
    <div class="flex-1 p-4 sm:p-8 lg:p-12 min-h-[calc(100vh-65px)] flex flex-col items-center justify-center relative">
        <div class="max-w-md w-full text-center relative z-10">
            <h1 class="text-3xl sm:text-4xl font-black text-[#4C5372] mb-2 uppercase tracking-tighter">Kartu Digital</h1>
            <p class="text-[#4C5372] opacity-50 font-medium mb-8 sm:mb-10 italic text-xs sm:text-sm">Tunjukkan QR ini ke petugas untuk verifikasi.</p>

            <div class="glass-card p-6 sm:p-10 rounded-3xl sm:rounded-[3rem] shadow-2xl border border-white bg-white/40 backdrop-blur-xl group">
                <div class="flex items-center justify-between mb-6 sm:mb-8 border-b border-[#4C5372]/5 pb-4 sm:pb-6 text-left">
                    <div>
                        <p class="text-[9px] sm:text-[10px] font-black text-[#4C5372] opacity-40 uppercase tracking-[0.2em]">Nama Anggota</p>
                        <h2 class="text-base sm:text-lg font-black text-[#4C5372] uppercase italic line-clamp-1"><?= $nama_siswa; ?></h2>
                    </div>
                    <div class="bg-[#4C5372] p-2 sm:p-2.5 rounded-xl text-white font-black shadow-lg shadow-[#4C5372]/20 text-sm sm:text-base">H</div>
                </div>

                <div class="bg-white p-4 sm:p-6 rounded-2xl sm:rounded-[2.5rem] shadow-inner border-2 border-[#4C5372]/5 mb-6 sm:mb-8 flex justify-center">
                    <img src="<?= $qr_url; ?>" 
                         alt="QR Member" 
                         class="w-44 h-44 sm:w-56 sm:h-56 object-contain group-hover:scale-105 transition-transform duration-500">
                </div>

                <div class="space-y-1">
                    <p class="text-[9px] sm:text-[10px] font-black text-[#4C5372] opacity-40 uppercase tracking-[0.2em]">ID Pelajar</p>
                    <p class="text-lg sm:text-xl font-black text-[#4C5372] tracking-widest italic"><?= $id_siswa; ?></p>
                </div>
            </div>
        </div>
    </div>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>
