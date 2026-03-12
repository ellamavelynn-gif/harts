<?php
session_start();
include 'config/koneksi.php';

if (isset($_POST['login'])) {
    $user = $_POST['username'];
    $pass = $_POST['password'];

    $query = mysqli_query($conn, "SELECT * FROM petugas WHERE username='$user' AND password='$pass'");
    if (mysqli_num_rows($query) > 0) {
        $_SESSION['admin'] = mysqli_fetch_array($query);
        echo "<script>alert('Login Berhasil!'); window.location='index.php';</script>";
    } else {
        echo "<script>alert('Username atau Password Salah!');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login - HARTS</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-900 flex items-center justify-center h-screen">
    <form action="" method="POST" class="bg-white p-10 rounded-3xl shadow-2xl w-96">
        <h2 class="text-3xl font-bold mb-6 text-slate-800 text-center">HARTS Login</h2>
        <div class="space-y-4">
            <input type="text" name="username" placeholder="Username" class="w-full p-4 border rounded-2xl" required>
            <input type="password" name="password" placeholder="Password" class="w-full p-4 border rounded-2xl" required>
            <button type="submit" name="login" class="w-full bg-blue-600 text-white p-4 rounded-2xl font-bold hover:bg-blue-700">Masuk</button>
            <p class="text-center text-sm text-slate-500 mt-6">
    Pegawai baru? <a href="registrasi_petugas.php" class="text-blue-600 font-bold">Daftar di sini</a>
</p>
        </div>
    </form>
</body>
</html>