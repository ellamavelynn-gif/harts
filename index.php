<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit;
}
include 'config/koneksi.php';

$current_page = basename($_SERVER['PHP_SELF']);

// --- LOGIKA APPROVE ---
if (isset($_GET['approve_id'])) {
    $id_pinjam = $_GET['approve_id'];
    $data = mysqli_query($conn, "SELECT id_buku, jumlah FROM peminjaman WHERE kode_transaksi = '$id_pinjam'");
    $row_peminjaman = mysqli_fetch_assoc($data);
    $id_buku = $row_peminjaman['id_buku'];
    $jml_diminta = $row_peminjaman['jumlah'] ?? 1;

    $update = mysqli_query($conn, "UPDATE peminjaman SET status = 'Dipinjam' WHERE kode_transaksi = '$id_pinjam'");
    if ($update) {
        mysqli_query($conn, "UPDATE buku SET jumlah_buku = jumlah_buku - $jml_diminta WHERE id_buku = '$id_buku'");
        header("Location: index.php?status=approved");
        exit;
    }
}

// --- LOGIKA REJECT ---
if (isset($_GET['reject_id'])) {
    $id_reject = $_GET['reject_id'];
    $delete = mysqli_query($conn, "DELETE FROM peminjaman WHERE kode_transaksi = '$id_reject'");
    if ($delete) {
        header("Location: index.php?status=rejected");
        exit;
    }
}

// --- LOGIKA SELESAIKAN (RETURN) ---
if (isset($_GET['return_id'])) {
    $id_kembali = $_GET['return_id'];
    $bayar_denda = isset($_GET['bayar_denda']) ? $_GET['bayar_denda'] : 0;
    $keterangan_denda = isset($_GET['ket_denda']) ? $_GET['ket_denda'] : 'Denda Keterlambatan';

    $q_info = mysqli_query($conn, "SELECT * FROM peminjaman WHERE kode_transaksi = '$id_kembali'");
    $d = mysqli_fetch_assoc($q_info);
    
    if ($d) {
        $id_buku_kembali = $d['id_buku'];
        $id_anggota = $d['id_anggota'];
        $jml_balik = $d['jumlah'] ?? 1;

        if ($bayar_denda > 0) {
            $tgl_skrg = date('Y-m-d H:i:s');
            mysqli_query($conn, "INSERT INTO kas_denda (kode_transaksi, id_anggota, nominal, tanggal_bayar, keterangan) 
                                 VALUES ('$id_kembali', '$id_anggota', '$bayar_denda', '$tgl_skrg', '$keterangan_denda')");
        }

        $delete_permanen = mysqli_query($conn, "DELETE FROM peminjaman WHERE kode_transaksi = '$id_kembali'");
        if ($delete_permanen) {
            mysqli_query($conn, "UPDATE buku SET jumlah_buku = jumlah_buku + $jml_balik WHERE id_buku = '$id_buku_kembali'");
            $status_msg = ($bayar_denda > 0) ? "returned_fine" : "returned";
            header("Location: index.php?status=$status_msg");
            exit;
        }
    }
}

function hitungDenda($tgl_deadline) {
    if (!$tgl_deadline || $tgl_deadline == '0000-00-00') return 0;
    date_default_timezone_set('Asia/Jakarta');
    $tgl_sekarang = new DateTime(date('Y-m-d')); 
    $tgl_tenggat = new DateTime(date('Y-m-d', strtotime($tgl_deadline)));
    if ($tgl_sekarang > $tgl_tenggat) {
        $selisih = $tgl_sekarang->diff($tgl_tenggat);
        return $selisih->days * 5000;
    }
    return 0;
}

$admin_data = $_SESSION['admin'];
$admin_name = $admin_data['nama_petugas'] ?? "Admin";
$initial    = strtoupper(substr($admin_name, 0, 1));

// Statistik
$total_buku = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM buku"))['total'];
$total_anggota = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM anggota"))['total'];
$total_pengajuan = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM peminjaman WHERE status = 'Menunggu' OR status IS NULL OR status = ''"))['total'];
$total_pinjam = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM peminjaman WHERE status = 'Dipinjam'"))['total'];

// Query Persetujuan
$query_approval = mysqli_query($conn, "SELECT peminjaman.*, anggota.nama_anggota, buku.judul_buku FROM peminjaman LEFT JOIN anggota ON peminjaman.id_anggota = anggota.id_anggota LEFT JOIN buku ON peminjaman.id_buku = buku.id_buku WHERE (peminjaman.status = 'Menunggu' OR peminjaman.status = '' OR peminjaman.status IS NULL)");

// Query Monitoring
$query_monitoring = mysqli_query($conn, "SELECT peminjaman.*, anggota.nama_anggota, buku.judul_buku FROM peminjaman LEFT JOIN anggota ON peminjaman.id_anggota = anggota.id_anggota LEFT JOIN buku ON peminjaman.id_buku = buku.id_buku WHERE peminjaman.status = 'Dipinjam'");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>HARTS - Admin Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: linear-gradient(135deg, #fbcfe8 0%, #e9d5ff 40%, #c3dafe 100%); height: 100vh; overflow: hidden; }
        .glass-sidebar { background: rgba(255, 255, 255, 0.3); backdrop-filter: blur(20px); border-right: 1px solid rgba(255, 255, 255, 0.5); }
        .glass-card { background: rgba(255, 255, 255, 0.4); backdrop-filter: blur(15px); border: 1px solid rgba(255, 255, 255, 0.6); }
        .nav-active { background-color: #2563eb; color: white; box-shadow: 0 10px 15px -3px rgba(59, 130, 246, 0.3); }
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-thumb { background: rgba(0,0,0,0.1); border-radius: 10px; }
    </style>
</head>
<body class="flex">

    <div class="w-80 h-full glass-sidebar p-8 flex flex-col shadow-2xl relative z-50">
        <div class="flex items-center mb-10">
            <div class="bg-blue-600 p-2 rounded-xl mr-3 shadow-lg"><span class="text-white font-black text-xl">H</span></div>
            <h1 class="text-2xl font-black text-slate-800 tracking-tighter uppercase">Harts</h1>
        </div>
        
        <a href="profile.php" class="bg-white/40 border border-white/60 p-5 rounded-[2.5rem] mb-10 flex items-center shadow-sm hover:bg-white/70 transition-all group">
            <div class="w-12 h-12 bg-gradient-to-tr from-blue-600 to-indigo-500 rounded-2xl flex items-center justify-center text-white font-bold text-xl mr-4 group-hover:scale-110 transition-transform"><?= $initial; ?></div>
            <div>
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Admin</p>
                <p class="text-lg font-bold text-slate-800 leading-tight"><?= $admin_name; ?></p>
            </div>
        </a>
<nav class="space-y-2 flex-1 overflow-y-auto pr-2 custom-scrollbar">
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-4 mb-2">Main Menu</p>
            
            <a href="index.php" class="flex items-center p-4 rounded-2xl transition-all group <?= ($current_page == 'index.php') ? 'nav-active font-bold' : 'text-slate-600 hover:bg-white/50'; ?>">
                <span class="mr-4 group-hover:scale-125 transition-transform">🏠</span> Dashboard
            </a>
            
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-4 mt-6 mb-2">Layanan & Scanner</p>
            
            <a href="presensi.php" class="flex items-center p-4 rounded-2xl transition-all group <?= ($current_page == 'presensi.php') ? 'nav-active font-bold' : 'text-slate-600 hover:bg-white/50'; ?>">
                <span class="mr-4 group-hover:scale-125 transition-transform">⏱️</span> Scanner Presensi
            </a>
            
            <a href="log_kunjungan.php" class="flex items-center p-4 rounded-2xl transition-all group <?= ($current_page == 'log_kunjungan.php') ? 'nav-active font-bold' : 'text-slate-600 hover:bg-white/50'; ?>">
                <span class="mr-4 group-hover:scale-125 transition-transform">📋</span> Log Kunjungan
            </a>

            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-4 mt-6 mb-2">Keuangan</p>
            
            <a href="kas_denda.php" class="flex items-center p-4 rounded-2xl transition-all group <?= ($current_page == 'kas_denda.php') ? 'nav-active font-bold' : 'text-slate-600 hover:bg-white/50'; ?>">
                <span class="mr-4 group-hover:scale-125 transition-transform">💰</span> Kas Denda
            </a>

            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-4 mt-6 mb-2">Katalog & User</p>
            
            <a href="modules/anggota/index.php?page=daftar" class="flex items-center p-4 rounded-2xl transition-all group <?= (isset($_GET['page']) && $_GET['page'] == 'daftar') ? 'nav-active font-bold' : 'text-slate-600 hover:bg-white/50'; ?>">
                <span class="mr-4 group-hover:scale-125 transition-transform">👥</span> Data Anggota
            </a>

            <a href="modules/anggota/index.php?page=validasi" class="flex items-center p-4 rounded-2xl transition-all group <?= (isset($_GET['page']) && $_GET['page'] == 'validasi') ? 'nav-active font-bold' : 'text-slate-600 hover:bg-white/50'; ?>">
                <span class="mr-4 group-hover:scale-125 transition-transform">✅</span> Validasi Akun
            </a>
            
            <a href="modules/buku/index.php" class="flex items-center p-4 rounded-2xl transition-all group <?= (strpos($_SERVER['REQUEST_URI'], 'buku') !== false) ? 'nav-active font-bold' : 'text-slate-600 hover:bg-white/50'; ?>">
                <span class="mr-4 group-hover:scale-125 transition-transform">📚</span> Katalog Buku
            </a>

            <a href="modules/rak/rak.php" class="flex items-center p-4 rounded-2xl transition-all group <?= (strpos($_SERVER['REQUEST_URI'], 'rak') !== false) ? 'nav-active font-bold' : 'text-slate-600 hover:bg-white/50'; ?>">
                <span class="mr-4 group-hover:scale-125 transition-transform">🗄️</span> Data Rak
            </a>

            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-4 mt-6 mb-2">Laporan</p>
            
            <a href="laporan.php" class="flex items-center p-4 rounded-2xl transition-all group <?= ($current_page == 'laporan.php') ? 'nav-active font-bold' : 'text-slate-600 hover:bg-white/50'; ?>">
                <span class="mr-4 group-hover:scale-125 transition-transform">📊</span> Laporan Utama
            </a>
        </nav>
        <a href="logout.php" class="p-4 text-red-500 font-bold flex items-center hover:bg-red-50/50 rounded-2xl mt-auto">🚪 Keluar</a>
    </div>

    <div class="flex-1 h-full overflow-y-auto p-12">
        <div class="max-w-6xl mx-auto">
            <div class="flex justify-between items-start mb-12">
                <div>
                    <h2 class="text-5xl font-black text-slate-800">Halo, <?= explode(' ', $admin_name)[0]; ?>!</h2>
                    <p class="text-slate-500 mt-2 text-xl italic">Semua kendali ada di tanganmu.</p>
                </div>
                <div class="bg-white/60 px-8 py-4 rounded-[2rem] border border-white font-bold text-slate-600">
                    📅 <?= date('d F Y'); ?>
                </div>
            </div>

            <div class="grid grid-cols-4 gap-8 mb-12">
                <div class="bg-blue-500 p-8 rounded-[3rem] text-white shadow-xl"><p class="text-sm font-bold opacity-80 uppercase">Buku</p><h3 class="text-6xl font-black mt-2"><?= $total_buku; ?></h3></div>
                <div class="bg-emerald-500 p-8 rounded-[3rem] text-white shadow-xl"><p class="text-sm font-bold opacity-80 uppercase">Anggota</p><h3 class="text-6xl font-black mt-2"><?= $total_anggota; ?></h3></div>
                <div class="bg-orange-500 p-8 rounded-[3rem] text-white shadow-xl"><p class="text-sm font-bold opacity-80 uppercase">Pengajuan</p><h3 class="text-6xl font-black mt-2"><?= $total_pengajuan; ?></h3></div>
                <div class="bg-indigo-500 p-8 rounded-[3rem] text-white shadow-xl"><p class="text-sm font-bold opacity-80 uppercase">Dipinjam</p><h3 class="text-6xl font-black mt-2"><?= $total_pinjam; ?></h3></div>
            </div>

            <div class="glass-card p-10 rounded-[3.5rem] shadow-xl mb-12">
                <h3 class="text-3xl font-black text-slate-800 flex items-center mb-10"><span class="w-3 h-10 bg-orange-500 rounded-full mr-4"></span> Menunggu Persetujuan</h3>
                <table class="w-full text-left border-separate border-spacing-y-4">
                    <thead>
                        <tr class="text-slate-400 text-[10px] uppercase tracking-widest">
                            <th class="px-6 font-black">Siswa & Buku</th>
                            <th class="px-6 font-black text-center">Jumlah</th>
                            <th class="px-6 font-black text-center">Tindakan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(mysqli_num_rows($query_approval) > 0): ?>
                            <?php while($row = mysqli_fetch_assoc($query_approval)) : ?>
                            <tr class="bg-white/30 hover:bg-white/60 transition-all">
                                <td class="p-6 rounded-l-[2rem]"><div class="font-bold text-slate-800"><?= $row['nama_anggota']; ?></div><div class="text-[10px] text-slate-500">"<?= $row['judul_buku']; ?>"</div></td>
                                <td class="p-6 text-center font-black text-blue-600 text-lg"><?= $row['jumlah'] ?? '1'; ?></td>
                                <td class="p-6 rounded-r-[2rem] text-center space-x-2">
                                    <button onclick="confirmApprove(<?= $row['kode_transaksi']; ?>)" class="px-6 py-2 bg-emerald-500 text-white rounded-xl text-[10px] font-black uppercase">Terima</button>
                                    <button onclick="confirmReject(<?= $row['kode_transaksi']; ?>)" class="px-6 py-2 bg-rose-500 text-white rounded-xl text-[10px] font-black uppercase">Tolak</button>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="3" class="text-center p-10 italic text-slate-400">Tidak ada pengajuan.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="glass-card p-10 rounded-[3.5rem] shadow-xl border-2 border-indigo-400/30">
                <h3 class="text-3xl font-black text-slate-800 flex items-center mb-10"><span class="w-3 h-10 bg-indigo-600 rounded-full mr-4"></span> Monitoring & Denda</h3>
                <table class="w-full text-left border-separate border-spacing-y-4">
                    <thead>
                        <tr class="text-slate-400 text-[10px] uppercase tracking-widest">
                            <th class="px-6 font-black">Peminjam</th>
                            <th class="px-6 font-black text-center">Tenggat</th>
                            <th class="px-6 font-black text-center">Denda</th>
                            <th class="px-6 font-black text-center">Tindakan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($query_monitoring) > 0): ?>
                            <?php while($m = mysqli_fetch_assoc($query_monitoring)) : 
                                $tgl_tenggat = $m['tgl_kembali'] ?? $m['tanggal_kembali'] ?? date('Y-m-d');
                                $denda_val = hitungDenda($tgl_tenggat);
                            ?>
                            <tr class="bg-white/30 hover:bg-white/60 transition-all">
                                <td class="p-6 rounded-l-[2rem]"><div class="font-bold text-slate-800"><?= $m['nama_anggota']; ?></div><div class="text-[10px] text-slate-500 italic"><?= $m['judul_buku']; ?></div></td>
                                <td class="p-6 text-center font-bold text-slate-600"><?= date('d/m/Y', strtotime($tgl_tenggat)); ?></td>
                                <td class="p-6 text-center font-black <?= ($denda_val > 0) ? 'text-rose-600' : 'text-slate-400'; ?>">Rp <?= number_format($denda_val, 0, ',', '.'); ?></td>
                                <td class="p-6 rounded-r-[2rem] text-center space-x-2">
                                    <button onclick="confirmReturn(<?= $m['kode_transaksi']; ?>)" class="px-6 py-2 bg-indigo-600 text-white rounded-xl text-[10px] font-black uppercase shadow-md">Selesai</button>
                                    <button onclick="pilihDenda(<?= $m['kode_transaksi']; ?>, <?= $denda_val; ?>)" class="px-6 py-2 bg-rose-500 text-white rounded-xl text-[10px] font-black uppercase shadow-md">Denda</button>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="4" class="text-center p-10 italic text-slate-400">Tidak ada data peminjaman.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
    async function pilihDenda(id, dendaOtomatis) {
        const { value: jenis } = await Swal.fire({
            title: 'Pilih Jenis Denda',
            input: 'select',
            inputOptions: { 'Denda Keterlambatan': 'Denda Keterlambatan', 'Denda Kerusakan': 'Denda Kerusakan', 'Denda Buku Hilang': 'Denda Buku Hilang' },
            inputValue: (dendaOtomatis > 0) ? 'Denda Keterlambatan' : 'Denda Kerusakan',
            showCancelButton: true, confirmButtonColor: '#4f46e5'
        });
        if (jenis) {
            const { value: nominal } = await Swal.fire({
                title: `Nominal ${jenis}`,
                input: 'number',
                inputLabel: 'Masukkan jumlah denda (Rp)',
                inputValue: (jenis === 'Denda Keterlambatan') ? dendaOtomatis : '',
                showCancelButton: true, confirmButtonText: 'Catat & Selesai', confirmButtonColor: '#10b981',
                inputValidator: (v) => { if (!v || v <= 0) return 'Nominal tidak valid!' }
            });
            if (nominal) window.location.href = `?return_id=${id}&bayar_denda=${nominal}&ket_denda=${encodeURIComponent(jenis)}`;
        }
    }
    function confirmApprove(id) { Swal.fire({ title: 'Setujui?', icon: 'question', showCancelButton: true, confirmButtonColor: '#10b981' }).then((r) => { if (r.isConfirmed) window.location.href = "?approve_id="+id; }); }
    function confirmReject(id) { Swal.fire({ title: 'Tolak?', icon: 'warning', showCancelButton: true, confirmButtonColor: '#f43f5e' }).then((r) => { if (r.isConfirmed) window.location.href = "?reject_id="+id; }); }
    function confirmReturn(id) { Swal.fire({ title: 'Buku Kembali?', icon: 'info', showCancelButton: true, confirmButtonColor: '#4f46e5' }).then((r) => { if (r.isConfirmed) window.location.href = "?return_id="+id; }); }

    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('status') === 'approved') Swal.fire({ title: 'Berhasil!', icon: 'success', timer: 1500, showConfirmButton: false });
    if (urlParams.get('status') === 'returned') Swal.fire({ title: 'Selesai!', icon: 'success', timer: 1500, showConfirmButton: false });
    if (urlParams.get('status') === 'returned_fine') Swal.fire({ title: 'Lunas!', text: 'Denda dicatat ke Kas.', icon: 'success', timer: 1500, showConfirmButton: false });
    </script>
</body>
</html>