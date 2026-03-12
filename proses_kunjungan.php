<?php
session_start();
include 'config/koneksi.php';

if (isset($_POST['id_anggota'])) {
    // Bersihkan ID dari prefix USER- kalau-kalau JS lo meleset
    $id_raw = mysqli_real_escape_string($conn, $_POST['id_anggota']);
    $id_anggota = str_replace("USER-", "", $id_raw);
    
    $hari_ini = date('Y-m-d');
    
    // Cek apakah ID Anggota valid (ada di database)
    $cek_user = mysqli_query($conn, "SELECT id_anggota FROM anggota WHERE id_anggota = '$id_anggota'");
    if(mysqli_num_rows($cek_user) == 0) {
        header("Location: presensi.php?status=not_found"); // Ganti index jadi presensi
        exit;
    }

    // 1. Cek status kunjungan aktif
    $cek = mysqli_query($conn, "SELECT id_kunjungan FROM kunjungan 
                                WHERE id_anggota = '$id_anggota' 
                                AND DATE(waktu_masuk) = '$hari_ini' 
                                AND waktu_keluar IS NULL 
                                LIMIT 1");

    if (mysqli_num_rows($cek) > 0) {
        // --- LOGIKA KELUAR ---
        $data = mysqli_fetch_assoc($cek);
        $id_kunjungan = $data['id_kunjungan'];
        $update = mysqli_query($conn, "UPDATE kunjungan SET waktu_keluar = NOW() WHERE id_kunjungan = '$id_kunjungan'");
        
        if ($update) {
            header("Location: presensi.php?status=keluar_success"); // BALIK KE PRESENSI
        }
    } else {
        // --- LOGIKA MASUK ---
        $insert = mysqli_query($conn, "INSERT INTO kunjungan (id_anggota, waktu_masuk) VALUES ('$id_anggota', NOW())");
        
        if ($insert) {
            header("Location: presensi.php?status=masuk_success"); // BALIK KE PRESENSI
        }
    }
} else {
    header("Location: presensi.php");
}
?>