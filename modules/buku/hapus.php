<?php
session_start();
include '../../config/koneksi.php';

// Cek login
if (!isset($_SESSION['admin'])) {
    header("Location: ../../login.php");
    exit;
}

// Ambil ID dari URL
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
    // Proses Hapus
    $query = "DELETE FROM buku WHERE id_buku = '$id'";
    
    if (mysqli_query($conn, $query)) {
        echo "<script>alert('Buku berhasil dihapus!'); window.location='index.php';</script>";
    } else {
        echo "<script>alert('Gagal menghapus!'); window.location='index.php';</script>";
    }
} else {
    header("Location: index.php");
}
?>