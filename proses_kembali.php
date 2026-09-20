<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit;
}

include 'config/koneksi.php';

$id_pinjam = $_GET['id'] ?? '';
$denda     = isset($_GET['denda']) ? (int)$_GET['denda'] : 0;
$ket_input = isset($_GET['ket']) ? mysqli_real_escape_string($conn, urldecode($_GET['ket'])) : '';

if (!empty($id_pinjam)) {
    $id_pinjam = mysqli_real_escape_string($conn, $id_pinjam);

    // 1. Ambil data peminjaman untuk dapat id_anggota, id_buku, dan jumlah pinjam
    $query_get = mysqli_query($conn, "SELECT * FROM peminjaman WHERE kode_transaksi = '$id_pinjam'");
    
    if ($query_get && mysqli_num_rows($query_get) > 0) {
        $data_pinjam = mysqli_fetch_assoc($query_get);
        $id_anggota  = $data_pinjam['id_anggota'];
        $id_buku     = $data_pinjam['id_buku'];
        $jumlah_pinjam = isset($data_pinjam['jumlah']) ? (int)$data_pinjam['jumlah'] : 1;

        // 2. Update status peminjaman jadi Kembali
        $update = mysqli_query($conn, "UPDATE peminjaman SET status = 'Kembali' WHERE kode_transaksi = '$id_pinjam'");

        if ($update) {
            // 3. TAMBAH STOK BUKU KEMBALI (Bagian ini yang sebelumnya hilang)
            mysqli_query($conn, "UPDATE buku SET jumlah_buku = jumlah_buku + $jumlah_pinjam WHERE id_buku = '$id_buku'");

            // 4. Masukkan ke kas_denda jika ada denda
            if ($denda > 0) {
                $keterangan = !empty($ket_input) ? "DENDA KERUSAKAN (" . $ket_input . ")" : "DENDA KERUSAKAN";
                
                $query_denda = "INSERT INTO kas_denda (id_anggota, keterangan, nominal, status_bayar, tanggal_bayar) 
                                VALUES ('$id_anggota', '$keterangan', '$denda', 'Lunas', NOW())";
                
                $insert_denda = mysqli_query($conn, $query_denda);

                if (!$insert_denda) {
                    $status = 'error';
                    $msg    = "Gagal simpan denda: " . mysqli_error($conn);
                } else {
                    $status = 'success';
                    $msg    = "Buku dikembalikan, stok ditambahkan, & Denda Rp " . number_format($denda, 0, ',', '.') . " berhasil masuk Kas Denda!";
                }
            } else {
                $status = 'success';
                $msg    = "Buku berhasil dikembalikan & stok telah ditambahkan!";
            }
        } else {
            $status = 'error';
            $msg    = "Gagal memperbarui status peminjaman!";
        }
    } else {
        $status = 'error';
        $msg    = 'Data peminjaman tidak ditemukan di database!';
    }
} else {
    $status = 'error';
    $msg    = 'ID transaksi tidak valid!';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; background: #e9d5ff; }</style>
</head>
<body>
    <script>
        Swal.fire({
            title: '<?= $status === "success" ? "Berhasil!" : "Gagal!"; ?>',
            text: '<?= $msg; ?>',
            icon: '<?= $status; ?>',
            confirmButtonColor: '#2563eb',
            customClass: {
                popup: 'rounded-[2rem] p-6',
                confirmButton: 'rounded-xl px-6 py-2.5 font-bold'
            }
        }).then(() => {
            window.location.href = 'peminjaman_aktif.php';
        });
    </script>
</body>
</html>