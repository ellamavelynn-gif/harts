<?php
include '../config/koneksi.php';

$step = 1; 
$error = "";
$last_id = "";

// 1. PROSES KLIK LANJUT (Simpan Data Awal & Token)
if (isset($_POST['lanjut'])) {
    $nama     = mysqli_real_escape_string($conn, $_POST['nama']);
    $nis      = mysqli_real_escape_string($conn, $_POST['nis']);
    $kelas    = mysqli_real_escape_string($conn, $_POST['kelas']);
    $alamat   = mysqli_real_escape_string($conn, $_POST['alamat']);
    $no_tlp   = mysqli_real_escape_string($conn, $_POST['no_tlp']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $confirm  = $_POST['confirm_password'];

    if ($password !== $confirm) {
        $error = "Password tidak cocok!";
    } else {
        // Generate Token Random (Contoh: HRT-1234)
        $token_random = "HRT" . rand(1000, 9999);
        
        $query = "INSERT INTO anggota (nis, nama_anggota, kelas, alamat, no_tlp, password, token_regis, status_akun) 
                  VALUES ('$nis', '$nama', '$kelas', '$alamat', '$no_tlp', '$password', '$token_random', 'baru')";
        
        if (mysqli_query($conn, $query)) {
            $last_id = mysqli_insert_id($conn);
            $step = 2; 
        } else {
            $error = "Gagal Daftar! NIS sudah ada atau Database error.";
        }
    }
}

// 2. PROSES VERIFIKASI TOKEN
if (isset($_POST['verifikasi_final'])) {
    $id_user     = $_POST['id_user'];
    $input_token = mysqli_real_escape_string($conn, $_POST['token_input']);

    $cek = mysqli_query($conn, "SELECT * FROM anggota WHERE id_anggota = '$id_user' AND token_regis = '$input_token'");

    if (mysqli_num_rows($cek) > 0) {
        mysqli_query($conn, "UPDATE anggota SET status_akun = 'aktif' WHERE id_anggota = '$id_user'");
        echo "<script>alert('Akun Berhasil Aktif! Silakan Login.'); window.location.href='login.php';</script>";
    } else {
        $error = "Token Salah! Silakan cek kembali di dashboard Admin.";
        $step = 2;
        $last_id = $id_user;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar - HARTS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/warna.css">
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="bg-[#FFFDF6] min-h-screen flex items-center justify-center p-6 relative overflow-hidden">

    <div class="blob-lilac absolute -top-20 -left-20 opacity-50"></div>
    <div class="blob-lilac absolute -bottom-20 -right-20 rotate-180 opacity-50"></div>

    <div class="glass-card p-10 rounded-[3rem] w-full max-w-md shadow-2xl border border-white relative z-10 bg-white/40 backdrop-blur-xl">
        <div class="text-center mb-8">
            <div class="inline-block bg-[#4C5372] text-white p-3 rounded-2xl font-black text-2xl mb-4 shadow-lg">H</div>
            <h2 class="text-3xl font-black text-[#4C5372] uppercase italic tracking-tighter">Daftar Akun</h2>
            <p class="text-[#4C5372]/60 text-sm italic font-medium mt-1">
                <?= ($step == 1) ? "Lengkapi data dirimu dulu ya" : "Masukkan kode dari Admin Tita"; ?>
            </p>
        </div>

        <?php if($error): ?>
            <div class="bg-red-50 border border-red-100 text-red-500 text-[10px] py-3 px-4 rounded-2xl mb-6 font-black uppercase tracking-widest text-center animate-pulse">
                <?= $error; ?> ⚠️
            </div>
        <?php endif; ?>

        <?php if($step == 1): ?>
            <form action="" method="POST" class="space-y-4">
                <div class="space-y-4">
                    <input type="text" name="nama" placeholder="NAMA LENGKAP" required 
                           class="w-full p-4 rounded-2xl bg-white border border-[#4C5372]/10 outline-none font-bold text-[#4C5372] focus:border-[#4C5372] transition-all placeholder:opacity-30">
                    
                    <input type="text" name="nis" placeholder="NIS SEKOLAH" required 
                           class="w-full p-4 rounded-2xl bg-white border border-[#4C5372]/10 outline-none font-bold text-[#4C5372] focus:border-[#4C5372] transition-all placeholder:opacity-30">
                    
                    <div class="grid grid-cols-2 gap-4">
                        <select name="kelas" required 
                                class="p-4 rounded-2xl bg-white border border-[#4C5372]/10 outline-none font-bold text-[#4C5372] focus:border-[#4C5372] transition-all appearance-none cursor-pointer">
                            <option value="" disabled selected>KELAS</option>
                            <option value="X RPL">X RPL</option>
                            <option value="XI RPL">XI RPL</option>
                            <option value="XII RPL">XII RPL</option>
                        </select>
                        <input type="text" name="no_tlp" placeholder="NO. TLP" required 
                               class="p-4 rounded-2xl bg-white border border-[#4C5372]/10 outline-none font-bold text-[#4C5372] focus:border-[#4C5372] transition-all placeholder:opacity-30">
                    </div>

                    <textarea name="alamat" placeholder="ALAMAT LENGKAP" required 
                              class="w-full p-4 rounded-2xl bg-white border border-[#4C5372]/10 outline-none font-bold text-[#4C5372] focus:border-[#4C5372] transition-all placeholder:opacity-30 h-24 resize-none"></textarea>

                    <input type="password" name="password" placeholder="PASSWORD" required 
                           class="w-full p-4 rounded-2xl bg-white border border-[#4C5372]/10 outline-none font-bold text-[#4C5372] focus:border-[#4C5372] transition-all placeholder:opacity-30">
                    
                    <input type="password" name="confirm_password" placeholder="KONFIRMASI PASSWORD" required 
                           class="w-full p-4 rounded-2xl bg-white border border-[#4C5372]/10 outline-none font-bold text-[#4C5372] focus:border-[#4C5372] transition-all placeholder:opacity-30">
                </div>
                
                <button type="submit" name="lanjut" 
                        class="w-full py-5 bg-[#4C5372] text-white rounded-[1.5rem] font-black shadow-xl shadow-[#4C5372]/20 hover:scale-[1.02] transition-all uppercase text-[10px] tracking-[0.2em] mt-4">
                    Lanjut Ke Verifikasi
                </button>
            </form>

        <?php else: ?>
            <form action="" method="POST" class="space-y-6">
                <input type="hidden" name="id_user" value="<?= $last_id; ?>">
                <div class="text-center">
                    <div class="bg-amber-50 border border-amber-100 p-4 rounded-2xl mb-6">
                        <p class="text-[10px] text-amber-600 font-black uppercase tracking-widest">⚠️ Perhatian</p>
                        <p class="text-[11px] text-amber-700/70 font-medium italic">Silakan hubungi Admin (Bu Tita) untuk mendapatkan kode akses kamu.</p>
                    </div>
                    <input type="text" name="token_input" placeholder="HRT-XXXX" required 
                           class="w-full p-6 rounded-[2rem] bg-white text-[#4C5372] text-center text-3xl font-black border-2 border-[#4C5372]/10 outline-none focus:border-[#4C5372] transition-all uppercase tracking-widest shadow-inner">
                </div>
                <button type="submit" name="verifikasi_final" 
                        class="w-full py-5 bg-[#4C5372] text-white rounded-[1.5rem] font-black shadow-xl shadow-[#4C5372]/20 hover:scale-[1.02] transition-all uppercase text-[10px] tracking-[0.2em]">
                    Aktifkan Akun Sekarang
                </button>
            </form>
        <?php endif; ?>

        <div class="mt-8 text-center">
            <a href="login.php" class="text-[10px] font-black text-[#4C5372] opacity-40 uppercase tracking-widest hover:opacity-100 transition-opacity underline">Kembali ke Login</a>
        </div>
    </div>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>