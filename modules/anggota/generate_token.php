<?php
session_start();
include '../../config/koneksi.php';

if (isset($_POST['generate'])) {
    // Membuat kode acak 7 karakter (contoh: HRT9921)
    $token_baru = "HRT" . rand(1000, 9999);
    mysqli_query($conn, "INSERT INTO token_regis (token, status) VALUES ('$token_baru', 'aktif')");
    $msg = $token_baru;
}
?>