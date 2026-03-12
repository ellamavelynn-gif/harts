<?php
session_start();
include '../config/koneksi.php';

if (isset($_POST['login'])) {
    $nis = mysqli_real_escape_string($conn, $_POST['nis']);
    $password = $_POST['password']; 

    $query = mysqli_query($conn, "SELECT * FROM anggota WHERE nis = '$nis'");
    
    if (mysqli_num_rows($query) === 1) {
        $data = mysqli_fetch_assoc($query);
        if ($password === $data['password']) {
            $_SESSION['id_anggota'] = $data['id_anggota'];
            $_SESSION['nama_anggota'] = $data['nama_anggota'];
            header("Location: index.php");
            exit;
        } else {
            $error = "Password salah!";
        }
    } else {
        $error = "NIS tidak terdaftar!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Siswa - HARTS</title>
     <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="../assets/css/warna.css">
<body class="flex items-center justify-center h-screen">
    <div class="w-full max-w-md p-8">
        <div class="card-login p-10 rounded-[3rem] shadow-2xl text-center">
            <h1 class="text-3xl font-black mb-2 uppercase italic title-text">Harts Siswa</h1>
            <p class="subtitle-text font-medium mb-8 text-sm">Pintu Masuk Perpustakaan 📖</p>

            <?php if(isset($error)) : ?>
                <div class="error-box text-xs py-3 rounded-xl mb-6 font-bold uppercase">
                    <?= $error; ?> ❌
                </div>
            <?php endif; ?>

            <form action="" method="POST" class="space-y-5">
                <input type="text" name="nis" required placeholder="Masukkan NIS" 
                    class="input-field w-full px-6 py-4 rounded-2xl outline-none transition-all font-bold">
                
                <input type="password" name="password" required placeholder="Masukkan Password" 
                    class="input-field w-full px-6 py-4 rounded-2xl outline-none transition-all font-bold">

                <button type="submit" name="login" class="btn-login w-full py-5 rounded-2xl font-black text-sm tracking-widest shadow-xl transition-all">
                    MASUK SEKARANG
                </button>
                
                <p class="mt-8 footer-text text-xs font-bold">
                    Belum punya akun? <a href="registrasi.php" class="underline">Daftar di sini</a>
                </p>
            </form>
        </div>
    </div>
</body>
</html>