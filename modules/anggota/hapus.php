<?php
session_start();
include '../../config/koneksi.php';

// cek parameter id
if (!isset($_GET['id'])) {
    echo "<script>
        alert('ID tidak ditemukan!');
        window.location='index.php';
    </script>";
    exit;
}

$id = $_GET['id'];

// query hapus
$sql = "DELETE FROM anggota WHERE id_anggota = '$id'";

if (mysqli_query($conn, $sql)) {
    echo "<script>
        alert('Data berhasil dihapus 🗑️');
        window.location='index.php';
    </script>";
} else {
    echo "<script>
        alert('Gagal menghapus data!');
        window.location='index.php';
    </script>";
}
?>
