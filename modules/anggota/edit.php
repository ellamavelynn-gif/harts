<?php
session_start();
include '../../config/koneksi.php';

if (!isset($_SESSION['admin'])) {
    header("Location: ../../login.php");
    exit;
}

// 1. Ambil ID
$id = (isset($_GET['id'])) ? mysqli_real_escape_string($conn, $_GET['id']) : 0;

// 2. Query data
$query = mysqli_query($conn, "SELECT * FROM anggota WHERE id_anggota = '$id'");
$data = mysqli_fetch_assoc($query);

// 3. CEK DATA (Fallback Logic)
$display_nis   = isset($data['nis']) ? $data['nis'] : '';
$display_nama  = isset($data['nama_anggota']) ? $data['nama_anggota'] : (isset($data['nama']) ? $data['nama'] : '');
$display_kelas = isset($data['kelas']) ? $data['kelas'] : '';
$display_tlp   = isset($data['no_tlp']) ? $data['no_tlp'] : ''; // Tambahan No Telp

// 4. Proses Update
if (isset($_POST['update'])) {
    $nama  = mysqli_real_escape_string($conn, $_POST['nama_anggota']);
    $nis   = mysqli_real_escape_string($conn, $_POST['nis']);
    $kelas = mysqli_real_escape_string($conn, $_POST['kelas']);
    $no_tlp = mysqli_real_escape_string($conn, $_POST['no_tlp']); // Ambil input telp

    mysqli_query($conn, "UPDATE anggota SET 
        nama_anggota='$nama', 
        nis='$nis', 
        kelas='$kelas',
        no_tlp='$no_tlp' 
        WHERE id_anggota='$id'");
        
    echo "<script>alert('Data Berhasil Diupdate!'); window.location='index.php';</script>";
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Anggota - HARTS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { 
            background: linear-gradient(135deg, #fbcfe8 0%, #e9d5ff 40%, #c3dafe 100%); 
            min-height: 100vh; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
        .glass-box { 
            background: rgba(255, 255, 255, 0.4); 
            backdrop-filter: blur(20px); 
            border-radius: 40px; 
            border: 1px solid white; 
            padding: 40px; 
            width: 450px; 
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.1); 
        }
    </style>
</head>
<body>

<div class="glass-box">
    <h2 class="text-2xl font-black text-slate-800 mb-8 uppercase italic tracking-tighter">✏️ Edit Anggota</h2>
    
    <form action="" method="POST" class="space-y-4">
        <div>
            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">NIS Siswa</label>
            <input type="text" name="nis" value="<?php echo $display_nis; ?>" placeholder="Masukkan NIS" required
                   class="w-full p-4 rounded-2xl border-none bg-white/50 focus:ring-2 focus:ring-blue-400 outline-none font-bold text-slate-700 transition-all">
        </div>

        <div>
            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Nama Lengkap</label>
            <input type="text" name="nama_anggota" value="<?php echo $display_nama; ?>" placeholder="Nama Siswa" required
                   class="w-full p-4 rounded-2xl border-none bg-white/50 focus:ring-2 focus:ring-blue-400 outline-none font-bold text-slate-700 transition-all">
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Kelas</label>
                <input type="text" name="kelas" value="<?php echo $display_kelas; ?>" placeholder="X RPL" required
                       class="w-full p-4 rounded-2xl border-none bg-white/50 focus:ring-2 focus:ring-blue-400 outline-none font-bold text-slate-700 transition-all">
            </div>
            <div>
                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">No. Telepon</label>
                <input type="text" name="no_tlp" value="<?php echo $display_tlp; ?>" placeholder="08xxx" required
                       class="w-full p-4 rounded-2xl border-none bg-white/50 focus:ring-2 focus:ring-blue-400 outline-none font-bold text-slate-700 transition-all">
            </div>
        </div>

        <div class="flex gap-3 pt-6">
            <button type="submit" name="update" 
                    class="flex-1 bg-blue-600 text-white py-4 rounded-2xl font-black shadow-lg shadow-blue-200 hover:bg-blue-700 hover:scale-[1.02] active:scale-95 transition-all uppercase text-xs tracking-widest">
                Simpan
            </button>
            <a href="index.php" 
               class="flex-1 bg-white/50 text-slate-500 py-4 rounded-2xl font-black text-center border border-white hover:bg-white/80 transition-all uppercase text-xs tracking-widest">
                Batal
            </a>
        </div>
    </form>
</div>

</body>
</html>