<?php
session_start();
include '../../config/koneksi.php';

// --- KEAMANAN: Cek Session Admin ---
if (!isset($_SESSION['admin'])) {
    header("Location: ../../login.php");
    exit;
}

if (!isset($_GET['id'])) {
    header("Location: rak.php");
    exit;
}

// Gunakan intval untuk memastikan ID adalah angka integer
$id = intval($_GET['id']);

// --- PERBAIKAN: Gunakan Prepared Statement untuk Select ---
$stmt = mysqli_prepare($conn, "SELECT * FROM rak WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$query = mysqli_stmt_get_result($stmt);

if (!$query) {
    die("Query error: " . mysqli_error($conn));
}

$data = mysqli_fetch_assoc($query);

if (!$data) {
    header("Location: rak.php");
    exit;
}
mysqli_stmt_close($stmt);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Rak - HARTS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: linear-gradient(135deg, #fbcfe8 0%, #e9d5ff 40%, #c3dafe 100%); min-height: 100vh; }
        .glass-card { background: rgba(255, 255, 255, 0.4); backdrop-filter: blur(20px); border: 1px solid rgba(255, 255, 255, 0.6); }
    </style>
</head>
<body class="p-10">
    <div class="max-w-2xl mx-auto">
        <a href="rak.php" class="text-slate-600 hover:text-blue-600 mb-6 inline-flex items-center font-semibold">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
            Kembali
        </a>

        <div class="glass-card rounded-[2.5rem] p-10 shadow-2xl">
            <h2 class="text-4xl font-black text-slate-800 mb-2">Edit Rak ✏️</h2>
            <p class="text-slate-600 mb-8 font-medium">Ubah informasi lokasi rak.</p>

            <form action="proses_rak.php" method="POST" class="space-y-6">
                <!-- Hidden input untuk ID -->
                <input type="hidden" name="id" value="<?= htmlspecialchars($data['id']); ?>">
                
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Kode Rak</label>
                    <input type="text" name="kode_rak" value="<?= htmlspecialchars($data['kode_rak']); ?>" required 
                           class="w-full px-5 py-4 rounded-xl bg-white/50 border border-white/70 focus:ring-2 focus:ring-blue-300 outline-none">
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Lokasi / Lantai</label>
                    <input type="text" name="lokasi" value="<?= htmlspecialchars($data['lokasi']); ?>" required 
                           class="w-full px-5 py-4 rounded-xl bg-white/50 border border-white/70 focus:ring-2 focus:ring-blue-300 outline-none">
                </div>

                <button type="submit" name="edit" class="w-full bg-amber-500 text-white py-4 rounded-2xl font-bold text-lg shadow-lg hover:bg-amber-600 transition-all">
                    SIMPAN PERUBAHAN
                </button>
            </form>
        </div>
    </div>
</body>
</html>