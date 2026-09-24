<?php
include '../../config/koneksi.php';

$id = $_GET['id'];
$tgl_sekarang = date('Y-m-d');

// 1. Ambil data transaksi
$query = mysqli_query($conn, "SELECT * FROM peminjaman WHERE kode_transaksi = '$id'");
$data = mysqli_fetch_assoc($query);

$tgl_kembali_seharusnya = $data['tanggal_kembali'];

// 2. Logika Hitung Denda (Misal: Rp 1.000 per hari telat)
$denda_per_hari = 1000;
$denda_total = 0;

if (strtotime($tgl_sekarang) > strtotime($tgl_kembali_seharusnya)) {
    $selisih = strtotime($tgl_sekarang) - strtotime($tgl_kembali_seharusnya);
    $hari_telat = floor($selisih / (60 * 60 * 24));
    $denda_total = $hari_telat * $denda_per_hari;
}

// 3. Update database: Status jadi Kembali & Isi Nominal Denda
$update = mysqli_query($conn, "UPDATE peminjaman SET 
    status = 'Kembali', 
    denda = '$denda_total' 
    WHERE kode_transaksi = '$id'");

if ($update) {
    echo "<script>
            alert('Buku berhasil dikembalikan! Denda: Rp " . number_format($denda_total, 0, ',', '.') . "');
            window.location.href='kembali.php';
          </script>";
}
?>