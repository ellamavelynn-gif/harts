<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit;
}
include 'config/koneksi.php';

$current_page = basename($_SERVER['PHP_SELF']);
$admin_data = $_SESSION['admin'];
$admin_name = $admin_data['nama_petugas'] ?? "Admin";
$initial    = strtoupper(substr($admin_name, 0, 1));

if (isset($_POST['bayar_denda'])) {
    $id_bayar = $_POST['id_denda']; 
    
    // Pastikan WHERE menggunakan id_kas
    $stmt = $conn->prepare("UPDATE kas_denda SET status_bayar = 'Lunas' WHERE id_kas = ?");
    $stmt->bind_param("i", $id_bayar);
    
    if ($stmt->execute()) {
        // Cek apakah ada baris yang berubah
        if ($stmt->affected_rows > 0) {
            header("Location: kas_denda.php?status=sukses");
            exit;
        } else {
            // Jika masuk ke sini, berarti ID $id_bayar tidak ada di kolom id_kas
            die("Gagal: ID ($id_bayar) tidak ditemukan di database.");
        }
    }
    $stmt->close();
}


// --- LOGIKA 2: TARIK SALDO (FIXED STATUS JADI 'KELUAR') ---
if (isset($_POST['tarik_saldo'])) {
    $nominal = $_POST['nominal'];
    $keterangan = mysqli_real_escape_string($conn, $_POST['keterangan']);
    
    // Kita masukkan nominal negatif agar otomatis mengurangi saldo
    $nominal_negatif = -$nominal;
    
    $query_tarik = "INSERT INTO kas_denda (id_anggota, nominal, tanggal_bayar, keterangan, status_bayar) 
                    VALUES (NULL, '$nominal_negatif', NOW(), '$keterangan', 'Keluar')";
    
    if (mysqli_query($conn, $query_tarik)) {
        header("Location: kas_denda.php");
        exit;
    } else {
        die("Gagal Tarik Saldo: " . mysqli_error($conn));
    }
}

// --- LOGIKA TOTAL SALDO (HITUNG LUNAS & KELUAR) ---
$query_total = mysqli_query($conn, "SELECT SUM(nominal) as total FROM kas_denda WHERE status_bayar IN ('Lunas', 'Keluar')");
$data_kas = mysqli_fetch_assoc($query_total);
$total_kas = $data_kas['total'] ?? 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kas Denda - HARTS Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: linear-gradient(135deg, #fbcfe8 0%, #e9d5ff 40%, #c3dafe 100%); height: 100vh; overflow: hidden; }
        .glass-sidebar { background: rgba(255, 255, 255, 0.3); backdrop-filter: blur(20px); border-right: 1px solid rgba(255, 255, 255, 0.5); }
        .glass-card { background: rgba(255, 255, 255, 0.4); backdrop-filter: blur(15px); border: 1px solid rgba(255, 255, 255, 0.6); }
        .nav-active { background-color: #2563eb !important; color: white !important; }
    </style>
</head>
<body class="flex text-slate-800">

    <div class="w-80 h-full glass-sidebar p-8 flex flex-col shadow-2xl relative z-50">
        
        <div class="flex items-center mb-10">
            <div class="bg-blue-600 p-2 rounded-xl mr-3 shadow-lg">
                <span class="text-white font-black text-xl">H</span>
            </div>
            <h1 class="text-2xl font-black text-slate-800 tracking-tighter uppercase italic">Harts</h1>
        </div>
        
        <a href="profile.php" class="bg-white/40 border border-white/60 p-5 rounded-[2.5rem] mb-10 flex items-center shadow-sm hover:bg-white/70 transition-all group">
            <div class="w-12 h-12 bg-gradient-to-tr from-blue-600 to-indigo-500 rounded-2xl flex items-center justify-center text-white font-bold text-xl mr-4 group-hover:scale-110 transition-transform">
                <?= $initial; ?>
            </div>
            <div>
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Admin</p>
                <p class="text-lg font-bold text-slate-800 leading-tight"><?= $admin_name; ?></p>
            </div>
        </a>

        <nav class="space-y-2 flex-1 overflow-y-auto pr-2 custom-scrollbar">
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-4 mb-2">Main Menu</p>
            
            <a href="index.php" class="flex items-center p-4 rounded-2xl transition-all group <?= ($current_page == 'index.php') ? 'nav-active font-bold shadow-lg shadow-blue-200' : 'text-slate-600 hover:bg-white/50'; ?>">
                <span class="mr-4 group-hover:scale-125 transition-transform">🏠</span> Dashboard
            </a>
            
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-4 mt-6 mb-2">Layanan & Scanner</p>
            
            <a href="presensi.php" class="flex items-center p-4 rounded-2xl transition-all group <?= ($current_page == 'presensi.php') ? 'nav-active font-bold shadow-lg shadow-blue-200' : 'text-slate-600 hover:bg-white/50'; ?>">
                <span class="mr-4 group-hover:scale-125 transition-transform">⏱️</span> Scanner Presensi
            </a>
            
            <a href="log_kunjungan.php" class="flex items-center p-4 rounded-2xl transition-all group <?= ($current_page == 'log_kunjungan.php') ? 'nav-active font-bold shadow-lg shadow-blue-200' : 'text-slate-600 hover:bg-white/50'; ?>">
                <span class="mr-4 group-hover:scale-125 transition-transform">📋</span> Log Kunjungan
            </a>

            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-4 mt-6 mb-2">Keuangan</p>
            
            <a href="kas_denda.php" class="flex items-center p-4 rounded-2xl transition-all group <?= ($current_page == 'kas_denda.php') ? 'nav-active font-bold shadow-lg shadow-blue-200' : 'text-slate-600 hover:bg-white/50'; ?>">
                <span class="mr-4 group-hover:scale-125 transition-transform">💰</span> Kas Denda
            </a>

            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-4 mt-6 mb-2">Katalog & User</p>
            
            <a href="modules/anggota/index.php?page=daftar" class="flex items-center p-4 rounded-2xl transition-all group <?= (isset($_GET['page']) && $_GET['page'] == 'daftar') ? 'nav-active font-bold shadow-lg shadow-blue-200' : 'text-slate-600 hover:bg-white/50'; ?>">
                <span class="mr-4 group-hover:scale-125 transition-transform">👥</span> Data Anggota
            </a>

            <a href="modules/anggota/index.php?page=validasi" class="flex items-center p-4 rounded-2xl transition-all group <?= (isset($_GET['page']) && $_GET['page'] == 'validasi') ? 'nav-active font-bold shadow-lg shadow-blue-200' : 'text-slate-600 hover:bg-white/50'; ?>">
                <span class="mr-4 group-hover:scale-125 transition-transform">✅</span> Validasi Akun
            </a>
            
            <a href="modules/buku/index.php" class="flex items-center p-4 rounded-2xl transition-all group <?= (strpos($_SERVER['REQUEST_URI'], 'buku') !== false) ? 'nav-active font-bold shadow-lg shadow-blue-200' : 'text-slate-600 hover:bg-white/50'; ?>">
                <span class="mr-4 group-hover:scale-125 transition-transform">📚</span> Katalog Buku
            </a>

            <a href="modules/rak/rak.php" class="flex items-center p-4 rounded-2xl transition-all group <?= (strpos($_SERVER['REQUEST_URI'], 'rak') !== false) ? 'nav-active font-bold shadow-lg shadow-blue-200' : 'text-slate-600 hover:bg-white/50'; ?>">
                <span class="mr-4 group-hover:scale-125 transition-transform">🗄️</span> Data Rak
            </a>

            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-4 mt-6 mb-2">Laporan</p>
            
            <a href="laporan.php" class="flex items-center p-4 rounded-2xl transition-all group <?= ($current_page == 'laporan.php') ? 'nav-active font-bold shadow-lg shadow-blue-200' : 'text-slate-600 hover:bg-white/50'; ?>">
                <span class="mr-4 group-hover:scale-125 transition-transform">📊</span> Laporan Utama
            </a>
        </nav>

        <a href="logout.php" class="p-4 text-red-500 font-bold flex items-center hover:bg-red-50/50 rounded-2xl mt-auto transition-all group">
            <span class="mr-4 group-hover:rotate-12 transition-transform">🚪</span> Keluar
        </a>
    </div>

    <div class="flex-1 h-full overflow-y-auto p-12">
        <div class="max-w-6xl mx-auto">
            <header class="flex justify-between items-center mb-12">
                <div>
                    <h2 class="text-5xl font-black">Kas Denda 💰</h2>
                    <p class="text-slate-500 italic mt-2">Manajemen keuangan kas denda siswa.</p>
                </div>
                <button onclick="openModal('modalTarik')" class="bg-red-500 text-white px-8 py-4 rounded-2xl font-bold shadow-lg">💸 Tarik Saldo</button>
            </header>

            <div class="bg-gradient-to-r from-blue-600 to-indigo-600 p-10 rounded-[3.5rem] text-white shadow-2xl mb-12">
                <p class="text-lg font-bold opacity-80 uppercase tracking-widest">Saldo Riil</p>
                <h3 class="text-6xl font-black mt-2">Rp <?= number_format($total_kas, 0, ',', '.'); ?></h3>
            </div>

            <div class="glass-card p-10 rounded-[3.5rem] shadow-xl overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="text-slate-400 text-[10px] uppercase font-black tracking-widest">
                            <th class="px-8 py-4">Siswa / Keterangan</th>
                            <th class="px-8 py-4 text-center">Status</th>
                            <th class="px-8 py-4 text-center">Nominal</th>
                            <th class="px-8 py-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Pastikan kita mengambil kolom 'id' sebagai primary key
                        $query = mysqli_query($conn, "SELECT kd.*, a.nama_anggota FROM kas_denda kd LEFT JOIN anggota a ON kd.id_anggota = a.id_anggota ORDER BY kd.tanggal_bayar DESC");
                        while($row = mysqli_fetch_assoc($query)) :
                            $nom = $row['nominal'] ?? 0;
                            $status = $row['status_bayar'] ?? 'Belum Lunas';
                            $is_lunas = ($status == 'Lunas');
                            $is_keluar = ($status == 'Keluar');
                            $is_penarikan = ($nom < 0);
                            
                            // Ambil ID yang benar (pastikan kolomnya 'id' atau 'id_denda')
                            $js_id = $row['id_kas'] ?? 0; 
                            $js_nama = htmlspecialchars(addslashes(trim($row['nama_anggota'] ?? $row['keterangan'] ?? 'ADMIN')));
                        ?>
                        <tr class="bg-white/40 border-b border-white/20 hover:bg-white/60 transition-all">
                            <td class="px-8 py-6">
                                <p class="font-black text-slate-800"><?= $row['nama_anggota'] ?? $row['keterangan'] ?? '🚨 TANPA NAMA'; ?></p>
                                <p class="text-[10px] opacity-50"><?= date('d M Y', strtotime($row['tanggal_bayar'])); ?></p>
                            </td>
                            <td class="px-8 py-6 text-center">
                                <?php if($is_lunas): ?>
                                    <span class="px-4 py-1.5 rounded-full text-[10px] font-black uppercase bg-emerald-100 text-emerald-600">Lunas</span>
                                <?php elseif($is_keluar): ?>
                                    <span class="px-4 py-1.5 rounded-full text-[10px] font-black uppercase bg-blue-100 text-blue-600">Tarik Saldo</span>
                                <?php else: ?>
                                    <span class="px-4 py-1.5 rounded-full text-[10px] font-black uppercase bg-rose-100 text-rose-600">Belum Lunas</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-8 py-6 text-center font-black">
                                <span class="<?= $is_penarikan ? 'text-red-500' : 'text-slate-700' ?>">
                                    <?= ($is_penarikan ? '-' : '+') ?> Rp <?= number_format(abs($nom), 0, ',', '.'); ?>
                                </span>
                            </td>
                            <td class="px-8 py-6 text-center">
                                <?php if (!$is_lunas && !$is_penarikan) : ?>
                                    <button onclick="openModalBayar('<?= $js_id ?>', '<?= $js_nama ?>', <?= abs($nom) ?>)" class="bg-blue-600 text-white px-5 py-2 rounded-xl text-[10px] font-black uppercase shadow-md">Bayar</button>
                                <?php else : ?>
                                    <span class="opacity-30 italic text-[10px]">TERCATAT</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div id="modalBayar" class="hidden fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-[100] items-center justify-center p-4">
        <div class="bg-white p-10 rounded-[3rem] w-full max-w-md shadow-2xl text-center">
            <h3 class="text-2xl font-black text-slate-800" id="labelSiswa">Nama Siswa</h3>
            <form action="" method="POST" class="mt-6 space-y-6">
                <!-- Input hidden untuk ID -->
                <input type="hidden" name="id_denda" id="input_id_denda">
                <input type="number" name="nominal_bayar" id="input_nominal" required readonly class="w-full p-5 rounded-2xl bg-slate-50 border-2 border-blue-100 font-black text-2xl text-center outline-none">
                <div class="flex gap-4">
                    <button type="button" onclick="closeModal('modalBayar')" class="flex-1 font-bold text-slate-400">Batal</button>
                    <button type="submit" name="bayar_denda" class="flex-[2] bg-blue-600 p-5 rounded-2xl font-bold text-white shadow-lg">Lunasin Sekarang</button>
                </div>
            </form>
        </div>
    </div>

    <div id="modalTarik" class="hidden fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-[100] items-center justify-center p-4">
        <div class="bg-white p-10 rounded-[3rem] w-full max-w-md shadow-2xl">
            <h3 class="text-3xl font-black mb-6">Tarik Saldo 💸</h3>
            <form action="" method="POST" class="space-y-6">
                <input type="number" name="nominal" required placeholder="Nominal" class="w-full p-5 rounded-2xl bg-slate-100 font-bold outline-none">
                <textarea name="keterangan" required placeholder="Keterangan..." class="w-full p-5 rounded-2xl bg-slate-100 font-bold h-32 outline-none"></textarea>
                <div class="flex gap-4">
                    <button type="button" onclick="closeModal('modalTarik')" class="flex-1 font-bold text-slate-400">Batal</button>
                    <button type="submit" name="tarik_saldo" class="flex-[2] bg-red-500 p-5 rounded-2xl font-bold text-white shadow-lg">Konfirmasi</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openModal(id) {
            const el = document.getElementById(id);
            el.classList.remove('hidden');
            el.style.display = 'flex';
        }
        function closeModal(id) {
            const el = document.getElementById(id);
            el.classList.add('hidden');
            el.style.display = 'none';
        }
        function openModalBayar(id, nama, nominal) {
            document.getElementById('input_id_denda').value = id;
            document.getElementById('input_nominal').value = nominal;
            document.getElementById('labelSiswa').innerText = nama;
            openModal('modalBayar');
        }
    </script>
</body>
</html>