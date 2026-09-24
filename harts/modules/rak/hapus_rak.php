<?php
session_start();
include '../../config/koneksi.php';

// 1. Cek apakah kode dikirim melalui URL
if (!isset($_GET['id'])) {
    header("Location: rak.php?status=error_invalid_id");
    exit;
}

// 2. Ambil nilai dari URL (biasanya string untuk kode rak, misal: RAK01)
$id = $_GET['id'];

// 3. Prepared statement - Ganti 'id' menjadi 'kode_rak' (atau nama kolom yang benar di DB kamu)
$stmt = mysqli_prepare($conn, "DELETE FROM rak WHERE kode_rak = ?");

if ($stmt) {
    // "s" berarti string (karena kode rak biasanya campuran huruf/angka)
    mysqli_stmt_bind_param($stmt, "s", $id);

    if (mysqli_stmt_execute($stmt)) {
        header("Location: rak.php?status=success_hapus");
    } else {
        echo "Error menghapus rak: " . mysqli_stmt_error($stmt);
    }
    mysqli_stmt_close($stmt);
} else {
    // Jika kolom salah, ini akan memberitahu kamu nama kolom yang benar apa
    die("Gagal menyiapkan query: " . mysqli_error($conn));
}

mysqli_close($conn);
?>