<?php
session_start();
include '../../config/koneksi.php';

// Cek parameter ID
if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$id = $_GET['id'];
$status = "gagal";

// Query hapus
$sql = "DELETE FROM anggota WHERE id_anggota = '$id'";

if (mysqli_query($conn, $sql)) {
    $status = "sukses";
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proses Hapus - HARTS</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #FFDBDA;
        }
    </style>
</head>
<body>

<script>
<?php if ($status === 'sukses') : ?>
    Swal.fire({
        title: 'Berhasil Dihapus!',
        text: 'Data anggota telah dihapus dari sistem.',
        icon: 'success',
        confirmButtonColor: '#604D53',
        confirmButtonText: 'Mantap',
        customClass: {
            popup: 'rounded-3xl'
        }
    }).then(() => {
        window.location.href = 'index.php';
    });
<?php else : ?>
    Swal.fire({
        title: 'Gagal Hapus!',
        text: 'Terjadi kesalahan saat menghapus data.',
        icon: 'error',
        confirmButtonColor: '#DB7F8E',
        confirmButtonText: 'Coba Lagi',
        customClass: {
            popup: 'rounded-3xl'
        }
    }).then(() => {
        window.location.href = 'index.php';
    });
<?php endif; ?>
</script>

</body>
</html>