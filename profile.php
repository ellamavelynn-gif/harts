<?php
session_start();
include 'config/koneksi.php';

if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit;
}

// Ambil data terbaru dari database
$admin_data = $_SESSION['admin'];
$id_cari = $admin_data['id_petugas'];
$query = mysqli_query($conn, "SELECT * FROM petugas WHERE id_petugas = '$id_cari'");
$data = mysqli_fetch_assoc($query) ?? $admin_data;

$admin_name = $data['nama_petugas'];
$initial    = strtoupper(substr($admin_name, 0, 1));
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Saya - HARTS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            background: linear-gradient(135deg, #fbcfe8 0%, #e9d5ff 40%, #c3dafe 100%);
            height: 100vh;
            overflow: hidden;
        }
        .glass-sidebar { background: rgba(255, 255, 255, 0.3); backdrop-filter: blur(20px); border-right: 1px solid rgba(255, 255, 255, 0.5); }
        .glass-card { background: rgba(255, 255, 255, 0.45); backdrop-filter: blur(20px); border: 1px solid rgba(255, 255, 255, 0.7); }
    </style>
</head>
<body class="flex">

    <div class="w-80 h-full glass-sidebar p-8 flex flex-col shadow-2xl relative z-50">
        <div class="flex items-center mb-10">
            <div class="bg-blue-600 p-2 rounded-xl mr-3 shadow-lg">
                <span class="text-white font-black text-xl">H</span>
            </div>
            <h1 class="text-2xl font-black text-slate-800 tracking-tighter uppercase">Harts</h1>
        </div>

        <a href="profile.php" class="bg-white/60 border border-white p-5 rounded-[2.5rem] mb-10 flex items-center shadow-md transition-all cursor-default group">
            <div class="w-12 h-12 bg-gradient-to-tr from-blue-600 to-indigo-500 rounded-2xl flex items-center justify-center text-white font-bold text-xl mr-4 shadow-lg">
                <?= $initial; ?>
            </div>
            <div class="overflow-hidden">
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Admin</p>
                <p class="text-lg font-bold text-slate-800 leading-tight truncate"><?= htmlspecialchars($admin_name); ?></p>
            </div>
        </a>

        <nav class="space-y-4 flex-1">
            <a href="index.php" class="flex items-center p-4 text-slate-600 hover:bg-white/50 rounded-2xl transition-all group border border-transparent hover:border-white">
                <span class="mr-3 group-hover:scale-125 transition-transform">🏠</span> Dashboard
            </a>

            <a href="modules/anggota/index.php" class="flex items-center p-4 text-slate-600 hover:bg-white/50 rounded-2xl transition-all group border border-transparent hover:border-white">
                <span class="mr-3 group-hover:scale-125 transition-transform">👥</span> Data Anggota
            </a>

            <a href="modules/buku/index.php" class="flex items-center p-4 text-slate-600 hover:bg-white/50 rounded-2xl transition-all group border border-transparent hover:border-white">
                <span class="mr-3 group-hover:scale-125 transition-transform">📚</span> Katalog Buku
            </a>

            <a href="modules/rak/index.php" class="flex items-center p-4 text-slate-600 hover:bg-white/50 rounded-2xl transition-all group border border-transparent hover:border-white">
                <span class="mr-3 group-hover:scale-125 transition-transform">🗄️</span> Data Rak
            </a>
        </nav>

        <a href="logout.php" class="p-4 text-red-500 font-bold flex items-center hover:bg-red-50/50 rounded-2xl transition-all mt-auto group">
            <span class="mr-3 group-hover:rotate-12 transition-transform">🚪</span> Keluar
        </a>
    </div>

    <div class="flex-1 h-full flex items-center justify-center p-12 bg-white/5">
        
        <div class="w-full max-w-xl">
            <div class="glass-card p-12 rounded-[4rem] shadow-2xl relative overflow-hidden border border-white">
                <div class="absolute -top-10 -right-10 w-40 h-40 bg-purple-300/30 rounded-full blur-3xl"></div>
                <div class="absolute -bottom-10 -left-10 w-40 h-40 bg-blue-300/30 rounded-full blur-3xl"></div>

                <a href="index.php" class="absolute top-8 right-8 w-10 h-10 flex items-center justify-center bg-white/50 rounded-full text-slate-500 hover:bg-red-500 hover:text-white transition-all shadow-sm z-10">✕</a>

                <div class="relative flex flex-col items-center">
                    <div class="w-32 h-32 bg-gradient-to-tr from-blue-600 to-indigo-500 rounded-[2.5rem] flex items-center justify-center text-5xl font-black text-white shadow-2xl mb-8 border-4 border-white transform hover:rotate-6 transition-transform">
                        <?= $initial; ?>
                    </div>

                    <h2 class="text-4xl font-black text-slate-800 mb-2 tracking-tight"><?= htmlspecialchars($admin_name); ?></h2>
                    <span class="px-8 py-2 bg-blue-600 text-white rounded-full text-[10px] font-black tracking-[0.2em] uppercase shadow-lg shadow-blue-200">
                        ADMINISTRATOR
                    </span>

                    <div class="w-full mt-12 space-y-4">
                        <div class="bg-white/40 p-6 rounded-[2rem] border border-white/60 shadow-sm">
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-1">Username System</p>
                            <p class="text-xl font-bold text-slate-700 italic">@<?= htmlspecialchars($data['username']); ?></p>
                        </div>
                        
                        <div class="bg-white/40 p-6 rounded-[2rem] border border-white/60 shadow-sm">
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-1">Akses Level</p>
                            <p class="text-xl font-bold text-slate-700">Petugas Perpustakaan Utama</p>
                        </div>
                    </div>

                    <a href="index.php" class="w-full mt-10 bg-slate-800 text-white p-6 rounded-[2.5rem] font-black hover:bg-black transition-all shadow-xl hover:-translate-y-1 text-center block uppercase tracking-widest text-sm">
                        Kembali ke Dashboard
                    </a>
                </div>
            </div>
        </div>
        
    </div>

</body>
</html>