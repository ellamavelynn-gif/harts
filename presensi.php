<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit;
}
include 'config/koneksi.php';

$admin_data = $_SESSION['admin'];
$admin_name = $admin_data['nama_petugas'] ?? "Admin";
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>HARTS - Presensi Siswa</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            background: linear-gradient(135deg, #fbcfe8 0%, #e9d5ff 40%, #c3dafe 100%); 
            min-height: 100vh; 
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .glass-card { background: rgba(255, 255, 255, 0.4); backdrop-filter: blur(15px); border: 1px solid rgba(255, 255, 255, 0.6); }
        #reader { border: none !important; width: 100% !important; }
        /* Styling tombol kamera bawaan library */
        #reader__dashboard_section_csr button { 
            background: #4C5372 !important; 
            color: white !important; 
            border-radius: 1rem !important; 
            padding: 12px 24px !important; 
            border: none !important; 
            font-weight: 800 !important; 
            text-transform: uppercase !important;
            font-size: 12px !important;
            letter-spacing: 1px !important;
            cursor: pointer !important; 
            box-shadow: 0 4px 15px rgba(0,0,0,0.1) !important;
        }
        video { border-radius: 2rem !important; object-fit: cover !important; }
    </style>
</head>
<body class="p-6">

    <div class="max-w-6xl w-full grid grid-cols-1 md:grid-cols-2 gap-10">
        
        <div class="glass-card p-10 rounded-[3.5rem] shadow-2xl flex flex-col items-center border-2 border-white">
            <div class="text-center mb-8">
                <h2 class="text-3xl font-black text-slate-800">Presensi Digital 📸</h2>
                <p class="text-slate-500 font-medium italic">Arahkan QR Code ke Kamera</p>
            </div>

            <div class="w-full bg-black/5 rounded-[2.5rem] overflow-hidden shadow-inner p-4 min-h-[300px] flex items-center justify-center">
                <div id="reader"></div>
            </div>

            <div id="result" class="mt-6 hidden">
                <span class="bg-emerald-500 text-white px-6 py-3 rounded-2xl font-black uppercase text-xs animate-bounce flex items-center shadow-lg">
                    ✅ QR Terdeteksi! Memproses...
                </span>
            </div>

            <a href="index.php" class="mt-8 text-slate-400 font-bold hover:text-[#4C5372] transition-all flex items-center group">
                <span class="mr-2 group-hover:-translate-x-1 transition-transform">←</span> Kembali ke Dashboard
            </a>
        </div>

        <div class="glass-card p-10 rounded-[3.5rem] shadow-2xl flex flex-col border-2 border-white max-h-[600px]">
            <h3 class="text-2xl font-black text-slate-800 mb-8 flex items-center">
                <span class="relative flex h-3 w-3 mr-4">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-3 w-3 bg-emerald-500"></span>
                </span>
                Di Dalam Perpustakaan
            </h3>

            <div class="flex-1 overflow-y-auto pr-2 space-y-4 custom-scrollbar">
                <?php 
                $q_present = mysqli_query($conn, "SELECT kunjungan.*, anggota.nama_anggota 
                                                 FROM kunjungan 
                                                 JOIN anggota ON kunjungan.id_anggota = anggota.id_anggota 
                                                 WHERE kunjungan.waktu_keluar IS NULL 
                                                 ORDER BY kunjungan.waktu_masuk DESC");
                
                if(mysqli_num_rows($q_present) > 0):
                    while($kp = mysqli_fetch_assoc($q_present)) : 
                ?>
                <div class="bg-white/60 p-5 rounded-[2rem] flex justify-between items-center border border-white/40 shadow-sm hover:bg-white/80 transition-all">
                    <div>
                        <p class="font-extrabold text-slate-800 text-lg"><?= $kp['nama_anggota']; ?></p>
                        <p class="text-[10px] font-black text-blue-500 uppercase tracking-widest mt-1">
                            🕒 Masuk: <?= date('H:i', strtotime($kp['waktu_masuk'])); ?>
                        </p>
                    </div>
                    <div class="w-12 h-12 bg-white rounded-2xl flex items-center justify-center text-2xl shadow-sm">
                        📖
                    </div>
                </div>
                <?php endwhile; 
                else: ?>
                <div class="h-full flex flex-col items-center justify-center text-slate-400 italic py-20">
                    <p class="text-5xl mb-4 opacity-30">🍃</p>
                    <p class="font-bold uppercase text-[10px] tracking-[0.2em]">Belum ada siswa di dalam</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <form id="form-presensi" action="proses_kunjungan.php" method="POST" class="hidden">
        <input type="text" id="id_anggota_qr" name="id_anggota">
    </form>

    <script src="https://unpkg.com/html5-qrcode"></script>
    <script>
    // 1. Inisialisasi Scanner
    let html5QrcodeScanner = new Html5QrcodeScanner(
        "reader", { 
            fps: 20, // Lebih cepet dikit biar smooth
            qrbox: { width: 250, height: 250 },
            aspectRatio: 1.0 
        }
    );

    function onScanSuccess(decodedText, decodedResult) {
        // Bersihkan ID (Hapus prefix USER-)
        let cleanID = decodedText.replace("USER-", "");
        
        // Stop scanner biar gak double post
        html5QrcodeScanner.clear();
        
        // Efek UI
        document.getElementById('result').classList.remove('hidden');
        document.getElementById('id_anggota_qr').value = cleanID;
        
        // Submit otomatis
        setTimeout(() => {
            document.getElementById('form-presensi').submit();
        }, 600);
    }

    // 2. Jalankan Render
    html5QrcodeScanner.render(onScanSuccess);

    // SweetAlert handling
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('status') === 'masuk_success') {
        Swal.fire({ title: 'Selamat Datang!', text: 'Presensi masuk berhasil', icon: 'success', timer: 2000, showConfirmButton: false, borderRadius: '2rem' });
    } else if (urlParams.get('status') === 'keluar_success') {
        Swal.fire({ title: 'Sampai Jumpa!', text: 'Presensi keluar berhasil', icon: 'info', timer: 2000, showConfirmButton: false, borderRadius: '2rem' });
    }
    </script>
</body>
</html>