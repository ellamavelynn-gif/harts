<?php
session_start();
include '../config/koneksi.php';

// Proteksi Halaman
if (!isset($_SESSION['id_anggota'])) {
    header("Location: login.php");
    exit;
}

$nama_siswa = $_SESSION['nama_anggota'];
$id_siswa = $_SESSION['id_anggota'];
$current_page = basename($_SERVER['PHP_SELF']);

// --- LOGIKA CEK DENDA (Untuk Notifikasi Sidebar) ---
$cek_denda = mysqli_query($conn, "SELECT SUM(nominal) as total FROM kas_denda WHERE id_anggota = '$id_siswa' AND status_bayar = 'Belum Lunas'");
$data_denda = mysqli_fetch_assoc($cek_denda);
$punya_hutang = ($data_denda['total'] > 0);
// -------------------------------------------

// Data QR Code
$qr_data = "USER-" . $id_siswa; 
// Pake QRICKIT API (Gratis & Stabil)
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

    <div class="ml-80 flex-1 p-12 min-h-screen flex flex-col items-center justify-center relative">
        <div class="max-w-md w-full text-center relative z-10">
            <h1 class="text-4xl font-black text-[#4C5372] mb-2 uppercase tracking-tighter">Kartu Digital </h1>
            <p class="text-[#4C5372] opacity-50 font-medium mb-10 italic text-sm">Tunjukkan QR ini ke petugas untuk verifikasi.</p>

            <div class="glass-card p-10 rounded-[3rem] shadow-2xl border border-white bg-white/40 backdrop-blur-xl group">
                <div class="flex items-center justify-between mb-8 border-b border-[#4C5372]/5 pb-6 text-left">
                    <div>
                        <p class="text-[10px] font-black text-[#4C5372] opacity-40 uppercase tracking-[0.2em]">Nama Anggota</p>
                        <h2 class="text-lg font-black text-[#4C5372] uppercase italic"><?= $nama_siswa; ?></h2>
                    </div>
                    <div class="bg-[#4C5372] p-2.5 rounded-xl text-white font-black shadow-lg shadow-[#4C5372]/20">H</div>
                </div>

                <div class="bg-white p-6 rounded-[2.5rem] shadow-inner border-2 border-[#4C5372]/5 mb-8 flex justify-center">
                    <img src="<?= $qr_url; ?>" 
                         alt="QR Member" 
                         class="w-56 h-56 object-contain group-hover:scale-105 transition-transform duration-500">
                </div>

                <div class="space-y-1">
                    <p class="text-[10px] font-black text-[#4C5372] opacity-40 uppercase tracking-[0.2em]">ID Pelajar</p>
                    <p class="text-xl font-black text-[#4C5372] tracking-widest italic"><?= $id_siswa; ?></p>
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