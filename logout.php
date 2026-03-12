<?php
session_start();

// Menghapus semua data session yang ada
session_unset();
session_destroy();

// Mengarahkan admin kembali ke halaman login dengan pesan sukses
echo "<script>
    alert('Anda telah berhasil keluar sistem.');
    window.location='login.php';
</script>";
exit;
?>