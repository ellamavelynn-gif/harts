<?php
session_start();
include '../../config/koneksi.php';

// Proteksi: Cek apakah data dikirim via POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // 1. Tangkap data dari form edit.php
    $id_buku        = mysqli_real_escape_string($conn, $_POST['id_buku']);
    $judul_buku     = mysqli_real_escape_string($conn, $_POST['judul_buku']);
    $kategori       = mysqli_real_escape_string($conn, $_POST['kategori']); 
    $nama_pengarang = mysqli_real_escape_string($conn, $_POST['nama_pengarang']);
    $tahun_buku     = mysqli_real_escape_string($conn, $_POST['tahun_buku']);
    $jumlah_buku    = mysqli_real_escape_string($conn, $_POST['jumlah_buku']);
    $id_rak         = mysqli_real_escape_string($conn, $_POST['id_rak']);

    // 2. Ambil ID pengarang lama & nama foto lama dari buku ini
    $query_buku = mysqli_query($conn, "SELECT id_pengarang, foto FROM buku WHERE id_buku = '$id_buku'");
    $res_buku   = mysqli_fetch_assoc($query_buku);
    
    if ($res_buku) {
        $id_pgr = $res_buku['id_pengarang'];
        $foto_db = $res_buku['foto']; // Gunakan foto lama sebagai default

        // 3. Proses Upload Foto Baru (Jika ada file yang diunggah)
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] === 0) {
            $nama_file   = $_FILES['foto']['name'];
            $ukuran_file = $_FILES['foto']['size'];
            $tmp_name    = $_FILES['foto']['tmp_name'];
            
            $ekstensi_valid = ['jpg', 'jpeg', 'png'];
            $ekstensi_file  = strtolower(pathinfo($nama_file, PATHINFO_EXTENSION));

            // Validasi Ekstensi
            if (!in_array($ekstensi_file, $ekstensi_valid)) {
                header("Location: index.php?status=error&msg=Format foto harus JPG, JPEG, atau PNG! ❌");
                exit;
            }

            // Validasi Ukuran (Max 2MB)
            if ($ukuran_file > 2097152) {
                header("Location: index.php?status=error&msg=Ukuran foto terlalu besar! Maksimal 2MB. ❌");
                exit;
            }

            // Generate nama file baru yang unik
            $nama_file_baru = uniqid() . '.' . $ekstensi_file;
            $folder_tujuan  = '../../assets/img/buku/';

            // Hapus file foto lama di server jika bukan default_buku.png
            if (!empty($res_buku['foto']) && $res_buku['foto'] !== 'default_buku.png') {
                $path_foto_lama = $folder_tujuan . $res_buku['foto'];
                if (file_exists($path_foto_lama)) {
                    unlink($path_foto_lama);
                }
            }

            // Pindahkan file foto baru ke direktori tujuan
            if (move_uploaded_file($tmp_name, $folder_tujuan . $nama_file_baru)) {
                $foto_db = $nama_file_baru;
            }
        }

        // 4. Update Nama Pengarang di tabel pengarang
        mysqli_query($conn, "UPDATE pengarang SET nama_pengarang = '$nama_pengarang' WHERE id_pengarang = '$id_pgr'");

        // 5. Update data buku utama (Termasuk kolom foto)
        $sql_update = "UPDATE buku SET 
                        judul_buku  = '$judul_buku',
                        kategori    = '$kategori',
                        tahun_buku  = '$tahun_buku',
                        jumlah_buku = '$jumlah_buku',
                        id_rak      = '$id_rak',
                        foto        = '$foto_db' 
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