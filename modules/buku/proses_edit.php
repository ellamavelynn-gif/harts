<?php
session_start();
include '../../config/koneksi.php';

// Proteksi: Cek apakah data dikirim via POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // 1. Tangkap data dari form edit.php
    $id_buku        = mysqli_real_escape_string($conn, $_POST['id_buku']);
    $judul_buku     = mysqli_real_escape_string($conn, $_POST['judul_buku']);
    $kategori       = mysqli_real_escape_string($conn, $_POST['kategori']); // Mapel Baru
    $nama_pengarang = mysqli_real_escape_string($conn, $_POST['nama_pengarang']);
    $tahun_buku     = mysqli_real_escape_string($conn, $_POST['tahun_buku']);
    $jumlah_buku    = mysqli_real_escape_string($conn, $_POST['jumlah_buku']);
    $id_rak         = mysqli_real_escape_string($conn, $_POST['id_rak']);

    // 2. Ambil ID pengarang lama dari buku ini
    $query_buku = mysqli_query($conn, "SELECT id_pengarang FROM buku WHERE id_buku = '$id_buku'");
    $res_buku   = mysqli_fetch_assoc($query_buku);
    
    if ($res_buku) {
        $id_pgr = $res_buku['id_pengarang'];

        // 3. Update Nama Pengarang di tabel pengarang
        mysqli_query($conn, "UPDATE pengarang SET nama_pengarang = '$nama_pengarang' WHERE id_pengarang = '$id_pgr'");

        // 4. Update data buku utama (Sesuai kolom baru: judul, kategori, rak, tahun, jumlah)
        // Kita abaikan id_penerbit karena lo bilang gak guna.
        $sql_update = "UPDATE buku SET 
                        judul_buku  = '$judul_buku',
                        kategori    = '$kategori',
                        tahun_buku  = '$tahun_buku',
                        jumlah_buku = '$jumlah_buku',
                        id_rak      = '$id_rak' 
                       WHERE id_buku = '$id_buku'";

        $query = mysqli_query($conn, $sql_update);

        if ($query) {
            // Redirect balik ke index dengan status sukses
            header("Location: index.php?status=success&msg=Data buku '$judul_buku' berhasil diperbarui! ✅");
            exit;
        } else {
            header("Location: index.php?status=error&msg=Gagal Update: " . mysqli_error($conn));
            exit;
        }
    } else {
        header("Location: index.php?status=error&msg=Data buku tidak ditemukan!");
        exit;
    }
} else {
    // Kalau coba akses file ini tanpa POST, tendang ke index
    header("Location: index.php");
    exit;
}
?>