<?php
include 'config/koneksi.php';

// KODE RAHASIA (Hanya pegawai yang tahu)
// Kamu bisa ganti kodenya di sini
$KODE_RAHASIA_ADMIN = "HARTS2024"; 

if (isset($_POST['daftar'])) {
    $username = $_POST['username'];
    $nama     = $_POST['nama_petugas'];
    $password = $_POST['password'];
    $kode_input = $_POST['kode_admin'];

    // Cek apakah kode admin benar
    if ($kode_input !== $KODE_RAHASIA_ADMIN) {
        echo "<script>alert('Kode Rahasia Admin Salah! Anda bukan pegawai.');</script>";
    } else {
        // Cek apakah username sudah ada
        $cek = mysqli_query($conn, "SELECT * FROM petugas WHERE username='$username'");
        if (mysqli_num_rows($cek) > 0) {
            echo "<script>alert('Username sudah terdaftar!');</script>";
        } else {
            // Simpan ke database
            $simpan = mysqli_query($conn, "INSERT INTO petugas (username, password, nama_petugas) 
                                         VALUES ('$username', '$password', '$nama')");
            if ($simpan) {
                echo "<script>alert('Berhasil Daftar! Silakan Login.'); window.location='login.php';</script>";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Daftar Pegawai - HARTS</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-900 flex items-center justify-center h-screen">
    <form action="" method="POST" class="bg-white p-10 rounded-3xl shadow-2xl w-[450px]">
        <h2 class="text-3xl font-bold mb-2 text-slate-800 text-center">Daftar Pegawai</h2>
        <p class="text-slate-400 text-center mb-8 text-sm">Khusus staf perpustakaan HARTS</p>
        
        <div class="space-y-4">
            <div>
                <label class="text-xs font-bold text-slate-400 uppercase ml-2">Nama Lengkap</label>
                <input type="text" name="nama_petugas" class="w-full p-4 border rounded-2xl bg-slate-50" placeholder="Nama asli" required>
            </div>
            <div>
                <label class="text-xs font-bold text-slate-400 uppercase ml-2">Username</label>
                <input type="text" name="username" class="w-full p-4 border rounded-2xl bg-slate-50" placeholder="Untuk login" required>
            </div>
            <div>
                <label class="text-xs font-bold text-slate-400 uppercase ml-2">Password</label>
                <input type="password" name="password" class="w-full p-4 border rounded-2xl bg-slate-50" placeholder="******" required>
            </div>
            <div class="pt-2 border-t">
                <label class="text-xs font-bold text-blue-500 uppercase ml-2">Kode Rahasia Admin</label>
                <input type="text" name="kode_admin" class="w-full p-4 border-2 border-blue-100 rounded-2xl bg-blue-50 focus:border-blue-500 outline-none" placeholder="Masukkan kode khusus" required>
            </div>
            
            <button type="submit" name="daftar" class="w-full bg-slate-800 text-white p-4 rounded-2xl font-bold hover:bg-black transition mt-4">Daftar Sekarang</button>
            <p class="text-center text-sm text-slate-500 mt-4">
                Sudah punya akun? <a href="login.php" class="text-blue-600 font-bold">Login</a>
            </p>
        </div>
    </form>
</body>
</html>