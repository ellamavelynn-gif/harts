<?php
session_start();
include 'config/koneksi.php';

$error_message = ""; 

if (isset($_POST['login'])) {
    $user = trim($_POST['username']);
    $pass = trim($_POST['password']);

    // Ambil data user berdasarkan username saja
    $stmt = mysqli_prepare($conn, "SELECT * FROM petugas WHERE username = ?");
    
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "s", $user);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($data = mysqli_fetch_assoc($result)) {
            // Verifikasi Password (Bisa memverifikasi hash password ATAU plaintext lama jika ada)
            if (password_verify($pass, $data['password']) || $pass === $data['password']) {
                $_SESSION['admin'] = $data;
                header("Location: index.php");
                exit(); 
            } else {
                $error_message = "Username atau Password Salah!";
            }
        } else {
            $error_message = "Username atau Password Salah!";
        }
        mysqli_stmt_close($stmt);
    } else {
        $error_message = "Database Error: " . mysqli_error($conn);
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - HARTS</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }

        body {
            background: linear-gradient(135deg, #e0e7ff 0%, #fce7f3 50%, #eef2ff 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-card {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.5);
            padding: 40px;
            border-radius: 30px;
            width: 100%;
            max-width: 400px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.05);
            text-align: center;
        }

        h2 { color: #1e293b; font-weight: 600; margin-bottom: 5px; }
        p.subtitle { color: #64748b; font-size: 13px; margin-bottom: 25px; }

        .input-group {
            background: rgba(255, 255, 255, 0.5);
            border: 1px solid rgba(255, 255, 255, 0.8);
            border-radius: 16px;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            overflow: hidden;
            transition: 0.3s;
        }

        .input-group:focus-within { border-color: #6366f1; background: #fff; }

        .input-group i { padding: 0 15px; color: #94a3b8; }

        input {
            width: 100%;
            padding: 15px 10px;
            border: none;
            background: transparent;
            outline: none;
            color: #334155;
        }

        .btn-login {
            width: 100%;
            padding: 15px;
            background: #6366f1;
            color: white;
            border: none;
            border-radius: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: 0.3s;
            margin-top: 10px;
        }

        .btn-login:hover { background: #4f46e5; transform: translateY(-2px); }

        .alert-error {
            background: rgba(239, 68, 68, 0.1);
            color: #ef4444;
            padding: 10px;
            border-radius: 12px;
            font-size: 12px;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>

    <div class="login-card">
        <h2>Login</h2>
        <p class="subtitle">Masuk ke sistem perpustakaan HARTS</p>

        <?php if (!empty($error_message)): ?>
            <div class="alert-error"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <form action="" method="POST">
            <div class="input-group">
                <i class="fa-solid fa-at"></i>
                <input type="text" name="username" placeholder="Username" required>
            </div>
            <div class="input-group">
                <i class="fa-solid fa-lock"></i>
                <input type="password" name="password" placeholder="Password" required>
            </div>
            
            <button type="submit" name="login" class="btn-login">Masuk</button>
        </form>

        <p style="margin-top: 20px; font-size: 12px; color: #64748b;">
            Belum punya akun? <a href="registrasi_petugas.php" style="color: #6366f1; font-weight: 600; text-decoration: none;">Daftar</a>
        </p>
    </div>

</body>
</html>