<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit;
}
include 'config/koneksi.php';

// Ambil data semua peminjaman (baik yang masih dipinjam maupun riwayat)
$query = mysqli_query($conn, "SELECT peminjaman.*, anggota.nama_anggota, buku.judul_buku 
    FROM peminjaman 
    JOIN anggota ON peminjaman.id_anggota = anggota.id_anggota 
    JOIN buku ON peminjaman.id_buku = buku.id_buku 
    ORDER BY peminjaman.tanggal_pinjam DESC");

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Perpustakaan HARTS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        @media print {
            .no-print { display: none; }
            body { background: white; }
            .print-area { box-shadow: none; border: none; width: 100%; }
        }
    </style>
</head>
<body class="bg-slate-50 p-10">

    <div class="max-w-5xl mx-auto bg-white p-12 rounded-[2rem] shadow-sm print-area">
        <div class="flex justify-between items-center border-b-4 border-slate-800 pb-8 mb-8">
            <div>
                <h1 class="text-4xl font-black text-slate-800 uppercase tracking-tighter">Laporan Peminjaman</h1>
                <p class="text-slate-500 font-bold uppercase tracking-widest text-xs">Perpustakaan Digital HARTS</p>
            </div>
            <div class="text-right">
                <p class="font-bold text-slate-800">Dicetak Pada:</p>
                <p class="text-slate-500"><?= date('d F Y'); ?></p>
            </div>
        </div>

        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-800 text-white">
                    <th class="p-4 border border-slate-700 uppercase text-[10px] font-black">No</th>
                    <th class="p-4 border border-slate-700 uppercase text-[10px] font-black">Peminjam</th>
                    <th class="p-4 border border-slate-700 uppercase text-[10px] font-black">Buku</th>
                    <th class="p-4 border border-slate-700 uppercase text-[10px] font-black text-center">Tgl Pinjam</th>
                    <th class="p-4 border border-slate-700 uppercase text-[10px] font-black text-center">Status</th>
                </tr>
            </thead>
            <tbody>
                <?php $no = 1; while($row = mysqli_fetch_assoc($query)) : ?>
                <tr class="hover:bg-slate-50 transition-all">
                    <td class="p-4 border border-slate-200 text-sm"><?= $no++; ?></td>
                    <td class="p-4 border border-slate-200 font-bold text-slate-800 text-sm"><?= $row['nama_anggota']; ?></td>
                    <td class="p-4 border border-slate-200 text-slate-600 text-sm italic">"<?= $row['judul_buku']; ?>"</td>
                    <td class="p-4 border border-slate-200 text-center text-sm"><?= date('d/m/Y', strtotime($row['tanggal_pinjam'])); ?></td>
                    <td class="p-4 border border-slate-200 text-center">
                        <span class="px-3 py-1 rounded-full text-[9px] font-black uppercase <?= ($row['status'] == 'Dipinjam') ? 'bg-orange-100 text-orange-600' : 'bg-emerald-100 text-emerald-600'; ?>">
                            <?= $row['status'] ?: 'Menunggu'; ?>
                        </span>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <div class="mt-20 flex justify-end">
            <div class="text-center">
                <p class="text-slate-500 mb-20 text-sm font-bold">Petugas Perpustakaan,</p>
                <p class="font-black text-slate-800 border-b-2 border-slate-800 pb-1"><?= $_SESSION['admin']['nama_petugas']; ?></p>
                <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest mt-1 italic">Admin HARTS</p>
            </div>
        </div>
    </div>

    <div class="fixed bottom-10 right-10 space-x-4 no-print">
        <button onclick="window.print()" class="bg-slate-800 text-white px-8 py-4 rounded-2xl font-black shadow-2xl hover:scale-105 transition-all">🖨️ CETAK SEKARANG</button>
        <a href="index.php" class="bg-white text-slate-800 px-8 py-4 rounded-2xl font-black shadow-xl border border-slate-200 hover:bg-slate-100 transition-all text-center inline-block">🔙 KEMBALI</a>
    </div>

</body>
</html>