<?php
session_start();
include '../config/koneksi.php';

function redirectAman($url) {
    if (empty($url)) return '';
    if (preg_match('#^[a-zA-Z0-9_\-]+\.php(\?[a-zA-Z0-9_=&%.\-]*)?$#', $url)) {
        return $url;
    }
    return '';
}

$redirect = redirectAman($_GET['redirect'] ?? ($_POST['redirect'] ?? ''));

if (isset($_POST['login'])) {
    $nama = mysqli_real_escape_string($conn, trim($_POST['nama']));
    $password = $_POST['password']; 

    $query = mysqli_query($conn, "SELECT * FROM anggota WHERE nama_anggota = '$nama'");
    
    if (mysqli_num_rows($query) === 1) {
        $data = mysqli_fetch_assoc($query);
        
        if ($data['status_akun'] !== 'aktif') {
            $error = "Akun belum aktif! Verifikasi token dulu.";
        } else if ($password === $data['password']) {
            $_SESSION['id_anggota'] = $data['id_anggota'];
            $_SESSION['nama_anggota'] = $data['nama_anggota'];
            header("Location: " . (!empty($redirect) ? $redirect : "index.php"));
            exit;
        } else {
            $error = "Password salah!";
        }
    } else {
        $error = "Nama Lengkap tidak terdaftar!";
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
    <!-- Google Fonts: Plus Jakarta Sans & Cormorant Garamond untuk kesan estetik -->
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,600;1,600&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/warna.css">
    <style>
        .font-aesthetic {
            font-family: 'Cormorant Garamond', serif;
        }
    </style>
</head>
<body class="flex items-center justify-center min-h-screen p-4 overflow-hidden">

    <!-- Card Login Lebih Ramping (max-w-sm) & Estetik -->
    <div class="w-full max-w-[360px] relative z-10">
        <div class="card-login p-7 sm:p-8 rounded-[2rem] text-center">
            
            <!-- Judul Estetik -->
            <h1 class="font-aesthetic text-3xl sm:text-4xl font-bold italic tracking-wide text-[#4C5372] mb-1">
                Harts
            </h1>
            <p class="text-xs font-semibold tracking-wider uppercase text-[#7a81a0] mb-6">
                Pintu Masuk Perpustakaan 📖
            </p>

            <?php if(isset($error)) : ?>
                <div class="text-xs py-2.5 px-4 rounded-xl mb-5 font-bold bg-red-100 text-red-600 border border-red-200">
                    <?= $error; ?> ❌
                </div>
            <?php endif; ?>

            <?php if(!empty($redirect)) : ?>
                <div class="text-xs py-2.5 px-4 rounded-xl mb-5 font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                    Masuk dulu untuk melanjutkan aksimu 🔒
                </div>
            <?php endif; ?>

            <form action="" method="POST" class="space-y-4 text-left">
                <input type="hidden" name="redirect" value="<?= htmlspecialchars($redirect); ?>">

                <div>
                    <label class="block text-[11px] font-bold mb-1 ml-1 text-[#4C5372] uppercase tracking-wider">Username</label>
                    <input type="text" name="nama" required placeholder="Masukkan nama..." 
                        class="input-field w-full px-4 py-2.5 text-xs sm:text-sm font-medium outline-none transition-all">
                </div>
                
                <div>
                    <label class="block text-[11px] font-bold mb-1 ml-1 text-[#4C5372] uppercase tracking-wider">Password</label>
                    <input type="password" name="password" required placeholder="••••••••" 
                        class="input-field w-full px-4 py-2.5 text-xs sm:text-sm font-medium outline-none transition-all">
                </div>

                <button type="submit" name="login" class="btn-login w-full py-3 mt-3 rounded-xl font-bold text-xs tracking-widest uppercase shadow-md cursor-pointer">
                    Masuk Sekarang
                </button>
                
                <p class="mt-5 text-center text-xs font-medium text-[#7a81a0]">
                    Belum punya akun? <a href="registrasi.php" class="underline font-bold text-[#4C5372] hover:text-black transition-colors">Daftar di sini</a>
                </p>
            </form>
        </div>
    </div>
</body>
</html>