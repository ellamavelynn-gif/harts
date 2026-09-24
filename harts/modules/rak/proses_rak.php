<?php
session_start();
include '../../config/koneksi.php';

// Pastikan koneksi database berhasil
if (!$conn) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

if (isset($_POST['tambah'])) {
    $kode_rak = mysqli_real_escape_string($conn, $_POST['kode_rak']);
    $lokasi   = mysqli_real_escape_string($conn, $_POST['lokasi']);

    // Gunakan prepared statement untuk keamanan
    $stmt = mysqli_prepare($conn, "INSERT INTO rak (kode_rak, lokasi) VALUES (?, ?)");
    mysqli_stmt_bind_param($stmt, "ss", $kode_rak, $lokasi);

    if (mysqli_stmt_execute($stmt)) {
        header("Location: rak.php?status=success_tambah");
        exit;
    } else {
        echo "Error menambah rak: " . mysqli_error($conn);
    }
    mysqli_stmt_close($stmt);

} elseif (isset($_POST['edit'])) {
    $id       = intval($_POST['id']);
    $kode_rak = mysqli_real_escape_string($conn, $_POST['kode_rak']);
    $lokasi   = mysqli_real_escape_string($conn, $_POST['lokasi']);

    // Gunakan prepared statement
    $stmt = mysqli_prepare($conn, "UPDATE rak SET kode_rak = ?, lokasi = ? WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "ssi", $kode_rak, $lokasi, $id);

    if (mysqli_stmt_execute($stmt)) {
        header("Location: rak.php?status=success_edit");
        exit;
    } else {
        echo "Error mengedit rak: " . mysqli_error($conn);
    }
    mysqli_stmt_close($stmt);
} else {
    // Jika tidak ada POST yang valid, redirect
    header("Location: rak.php");
    exit;
}

// Tutup koneksi
mysqli_close($conn);
?>