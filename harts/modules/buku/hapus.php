<?php
session_start();
include '../../config/koneksi.php';

if (!isset($_SESSION['admin'])) {
    header("Location: ../../login.php");
    exit;
}

if (isset($_GET['id'])) {
    $id = mysqli_real_escape_string($conn, $_GET['id']);

    // Cari foto lama untuk dihapus dari folder
    $q = mysqli_query($conn, "SELECT foto FROM buku WHERE id_buku = '$id'");
    $d = mysqli_fetch_assoc($q);

    if ($d && !empty($d['foto']) && $d['foto'] !== 'default_buku.png') {
        $path_foto = '../../assets/img/buku/' . $d['foto'];
        if (file_exists($path_foto)) {
            unlink($path_foto);
        }
    }

    // Query Hapus Buku
    $sql = "DELETE FROM buku WHERE id_buku = '$id'";

    if (mysqli_query($conn, $sql)) {
        header("Location: index.php?status=success&msg=Buku berhasil dihapus! 🗑️");
        exit;
    } else {
        header("Location: index.php?status=error&msg=Gagal menghapus buku!");
        exit;
    }
} else {
    header("Location: index.php");
    exit;
}
?>