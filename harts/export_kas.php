<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit;
}
include 'config/koneksi.php';

$type = $_GET['type'] ?? 'excel';

// Query Ambil Data Transaksi Kas
$query = mysqli_query($conn, "
    SELECT kd.*, a.nama_anggota 
    FROM kas_denda kd 
    LEFT JOIN anggota a ON kd.id_anggota = a.id_anggota 
    ORDER BY kd.tanggal_bayar DESC
");

// Total Statistik Ringkas
$q_stat = mysqli_query($conn, "
    SELECT 
        SUM(CASE WHEN status_bayar = 'Lunas' THEN nominal ELSE 0 END) as total_masuk,
        SUM(CASE WHEN status_bayar = 'Keluar' THEN nominal ELSE 0 END) as total_keluar
    FROM kas_denda
");
$stat = mysqli_fetch_assoc($q_stat);
$total_masuk  = $stat['total_masuk'] ?? 0;
$total_keluar = $stat['total_keluar'] ?? 0;
$saldo_akhir  = $total_masuk - $total_keluar;

if ($type === 'excel') {
    // --- MODE EXCEL RAPI ---
    header("Content-Type: application/vnd.ms-excel; charset=utf-8");
    header("Content-Disposition: attachment; filename=Laporan_Keuangan_Kas_Denda_" . date('Y-m-d') . ".xls");
    header("Pragma: no-cache");
    header("Expires: 0");
    ?>
    <html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <style>
            table { border-collapse: collapse; width: 100%; font-family: Arial, sans-serif; }
            th { background-color: #4B5563; color: #ffffff; border: 1px solid #374151; padding: 8px; text-align: center; font-weight: bold; }
            td { border: 1px solid #D1D5DB; padding: 6px; vertical-align: middle; }
            .title { font-size: 16pt; font-weight: bold; text-align: center; }
            .num { text-align: right; mso-number-format:"\#\,\#\#0"; }
            .center { text-align: center; }
            .bg-header { background-color: #F3F4F6; font-weight: bold; }
            .bg-masuk { color: #047857; font-weight: bold; }
            .bg-keluar { color: #B91C1C; font-weight: bold; }
        </style>
    </head>
    <body>
        <table>
            <tr><td colspan="5" class="title">LAPORAN KEUANGAN KAS DENDA HARTS LIBRARY</td></tr>
            <tr><td colspan="5" class="center">Tanggal Cetak: <?= date('d/m/Y H:i'); ?> WIB</td></tr>
            <tr><td colspan="5"></td></tr>
            
            <!-- Ringkasan Kas -->
            <tr class="bg-header">
                <td colspan="2">Ringkasan Kas</td>
                <td colspan="3" class="num">Nominal (Rp)</td>
            </tr>
            <tr>
                <td colspan="2">Total Pemasukan (Lunas)</td>
                <td colspan="3" class="num bg-masuk"><?= $total_masuk; ?></td>
            </tr>
            <tr>
                <td colspan="2">Total Pengeluaran / Penarikan</td>
                <td colspan="3" class="num bg-keluar"><?= $total_keluar; ?></td>
            </tr>
            <tr class="bg-header">
                <td colspan="2">Saldo Akhir Kas Riil</td>
                <td colspan="3" class="num"><?= $saldo_akhir; ?></td>
            </tr>
            <tr><td colspan="5"></td></tr>

            <!-- Table Data Utama -->
            <thead>
                <tr>
                    <th width="50">No</th>
                    <th width="160">Tanggal & Waktu</th>
                    <th width="250">Siswa / Keterangan</th>
                    <th width="120">Status</th>
                    <th width="150">Nominal (Rp)</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $no = 1;
                while($r = mysqli_fetch_assoc($query)): 
                    $nama = $r['nama_anggota'] ?? $r['keterangan'] ?? 'Tanpa Keterangan';
                    $status = $r['status_bayar'] ?? 'Belum Lunas';
                    $is_keluar = ($status == 'Keluar');
                    $nom = abs($r['nominal']);
                ?>
                <tr>
                    <td class="center"><?= $no++; ?></td>
                    <td class="center"><?= date('d/m/Y H:i', strtotime($r['tanggal_bayar'])); ?></td>
                    <td><?= htmlspecialchars($nama); ?></td>
                    <td class="center"><?= htmlspecialchars($status); ?></td>
                    <td class="num <?= $is_keluar ? 'bg-keluar' : 'bg-masuk'; ?>">
                        <?= $is_keluar ? -1 * $nom : $nom; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </body>
    </html>
    <?php
    exit;

} else if ($type === 'pdf') {
    // --- MODE PDF SIAP CETAK ---
    ?>
    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <title>Laporan Keuangan Kas Denda</title>
        <style>
            body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 11pt; color: #333; margin: 20px; }
            .header { text-align: center; margin-bottom: 25px; border-bottom: 2px solid #604D53; padding-bottom: 10px; }
            .header h2 { margin: 0; text-transform: uppercase; color: #604D53; font-size: 18pt; }
            .header p { margin: 3px 0 0; font-size: 9pt; color: #666; }
            
            .summary-table { width: 100%; margin-bottom: 20px; border-collapse: collapse; }
            .summary-table td { padding: 8px 12px; border: 1px solid #e5e7eb; }
            .summary-title { font-weight: bold; background-color: #f9fafb; width: 40%; }
            
            .data-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
            .data-table th, .data-table td { border: 1px solid #d1d5db; padding: 8px 10px; text-align: left; }
            .data-table th { background-color: #604D53; color: #ffffff; font-size: 10pt; text-transform: uppercase; }
            .data-table tr:nth-child(even) { background-color: #f9fafb; }
            
            .text-center { text-align: center; }
            .text-right { text-align: right; }
            .text-red { color: #DB7F8E; font-weight: bold; }
            .text-green { color: #059669; font-weight: bold; }

            .footer { margin-top: 30px; text-align: right; font-size: 10pt; }
            .signature { margin-top: 60px; font-weight: bold; }

            @media print {
                @page { size: A4; margin: 1.5cm; }
                body { margin: 0; }
            }
        </style>
    </head>
    <body onload="window.print()">
        <div class="header">
            <h2>HARTS LIBRARY</h2>
            <p>Laporan Keuangan & Rekapitulasi Kas Denda</p>
            <p>Dicetak pada: <?= date('d F Y, H:i'); ?> WIB</p>
        </div>

        <!-- Summary -->
        <table class="summary-table">
            <tr>
                <td class="summary-title">Total Pemasukan Denda (Lunas)</td>
                <td class="text-right text-green">Rp <?= number_format($total_masuk, 0, ',', '.'); ?></td>
            </tr>
            <tr>
                <td class="summary-title">Total Pengeluaran / Penarikan Kas</td>
                <td class="text-right text-red">- Rp <?= number_format($total_keluar, 0, ',', '.'); ?></td>
            </tr>
            <tr>
                <td class="summary-title"><strong>Total Saldo Kas Riil</strong></td>
                <td class="text-right"><strong>Rp <?= number_format($saldo_akhir, 0, ',', '.'); ?></strong></td>
            </tr>
        </table>

        <!-- Table Utama -->
        <table class="data-table">
            <thead>
                <tr>
                    <th class="text-center" width="5%">No</th>
                    <th width="22%">Tanggal / Waktu</th>
                    <th width="38%">Siswa / Keterangan</th>
                    <th class="text-center" width="15%">Status</th>
                    <th class="text-right" width="20%">Nominal</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $no = 1;
                while($row = mysqli_fetch_assoc($query)):
                    $nama = $row['nama_anggota'] ?? $row['keterangan'] ?? 'Tanpa Keterangan';
                    $status = $row['status_bayar'] ?? 'Belum Lunas';
                    $is_keluar = ($status == 'Keluar');
                    $nom = abs($row['nominal']);
                ?>
                <tr>
                    <td class="text-center"><?= $no++; ?></td>
                    <td><?= date('d M Y, H:i', strtotime($row['tanggal_bayar'])); ?> WIB</td>
                    <td><?= htmlspecialchars($nama); ?></td>
                    <td class="text-center"><?= htmlspecialchars($status); ?></td>
                    <td class="text-right <?= $is_keluar ? 'text-red' : '' ?>">
                        <?= ($is_keluar ? '-' : '+') ?> Rp <?= number_format($nom, 0, ',', '.'); ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <div class="footer">
            <p>Petugas Penanggung Jawab,</p>
            <div class="signature"><?= htmlspecialchars($_SESSION['admin']['nama_petugas'] ?? 'Administrator'); ?></div>
        </div>
    </body>
    </html>
    <?php
}