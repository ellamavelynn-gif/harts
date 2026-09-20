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
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="theme-color" content="#9DA3A4">
    <title>HARTS - Presensi Siswa</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --soft-blush: #FFDBDA;
            --old-rose: #DB7F8E;
            --pale-slate: #D5C5C8;
            --cool-steel: #9DA3A4;
            --taupe-grey: #604D53;
        }

        html, body { -webkit-text-size-adjust: 100%; text-size-adjust: 100%; }

        body { 
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; 
            background-color: var(--soft-blush); 
            color: var(--taupe-grey);
            min-height: 100vh;
            min-height: 100dvh;
            display: flex;
            align-items: flex-start;
            justify-content: center;
            padding-top: calc(1rem + env(safe-area-inset-top));
            padding-bottom: calc(1rem + env(safe-area-inset-bottom));
            padding-left: calc(1rem + env(safe-area-inset-left));
            padding-right: calc(1rem + env(safe-area-inset-right));
        }

        @media (min-width: 768px) {
            body { align-items: center; }
        }

        button, a { -webkit-tap-highlight-color: transparent; }

        .card-panel { 
            background: #ffffff; 
            border: 1px solid var(--pale-slate); 
            box-shadow: 0 10px 30px -5px rgba(96, 77, 83, 0.05);
        }

        #reader { 
            border: none !important; 
            width: 100% !important; 
        }

        /* Kompatibilitas elemen bawaan library html5-qrcode di layar sempit */
        #reader__scan_region { 
            display: flex !important; 
            justify-content: center !important; 
        }

        #reader__scan_region img { 
            max-width: 100% !important; 
            height: auto !important; 
        }

        #reader__dashboard_section_csr {
            display: flex !important;
            flex-wrap: wrap !important;
            gap: 8px !important;
            justify-content: center !important;
        }

        #reader__dashboard_section_swaplink,
        #reader__camera_selection {
            max-width: 100% !important;
            font-size: 12px !important;
        }

        #reader__camera_selection {
            padding: 8px 10px !important;
            border-radius: 0.75rem !important;
            border: 1px solid var(--pale-slate) !important;
        }

        #reader__dashboard_section_csr button { 
            background-color: var(--taupe-grey) !important; 
            color: #ffffff !important; 
            border-radius: 0.75rem !important; 
            padding: 10px 20px !important; 
            border: none !important; 
            font-weight: 700 !important; 
            text-transform: uppercase !important;
            font-size: 11px !important;
            letter-spacing: 0.5px !important;
            cursor: pointer !important; 
            transition: all 0.2s ease !important;
        }

        #reader__dashboard_section_csr button:hover {
            opacity: 0.9 !important;
            transform: translateY(-1px) !important;
        }

        video { 
            border-radius: 1.5rem !important; 
            object-fit: cover !important; 
        }

        ::-webkit-scrollbar { width: 5px; }
        ::-webkit-scrollbar-thumb { background: var(--old-rose); border-radius: 10px; }
    </style>
</head>
<body class="p-6">

    <div class="max-w-5xl w-full grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6 md:gap-8">
        
        <!-- Scanner Card Area -->
        <div class="card-panel p-4 sm:p-6 md:p-8 rounded-2xl sm:rounded-3xl flex flex-col items-center">
            <div class="text-center mb-4 sm:mb-6">
                <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-2xl mx-auto mb-3 flex items-center justify-center text-white shadow-sm" style="background-color: var(--cool-steel);">
                    <i class="fa-solid fa-qrcode text-lg sm:text-xl"></i>
                </div>
                <h2 class="text-xl sm:text-2xl font-extrabold tracking-tight" style="color: var(--taupe-grey);">Presensi Digital</h2>
                <p class="text-xs font-semibold opacity-75 mt-0.5" style="color: var(--taupe-grey);">Arahkan QR Code anggota ke area kamera</p>
            </div>

            <div class="w-full rounded-2xl overflow-hidden p-2 sm:p-3 border min-h-[240px] sm:min-h-[280px] flex items-center justify-center" style="background-color: var(--soft-blush); border-color: var(--pale-slate);">
                <div id="reader"></div>
                <div id="reader-insecure-warning" class="hidden text-center px-4 py-6">
                    <i class="fa-solid fa-triangle-exclamation text-2xl mb-3" style="color: var(--old-rose);"></i>
                    <p class="text-xs font-bold" style="color: var(--taupe-grey);">Kamera diblokir browser</p>
                    <p class="text-[11px] font-medium mt-2 opacity-80 leading-relaxed" style="color: var(--taupe-grey);">
                        Halaman ini dibuka lewat <b>HTTP biasa</b> (alamat IP), bukan HTTPS/localhost. Browser tidak akan memberi izin kamera di kondisi ini.<br><br>
                        Solusi: akses via <b>https://</b>, atau lewat <b>localhost</b> di komputer server-nya.
                    </p>
                </div>
            </div>

            <div id="result" class="mt-5 hidden w-full flex justify-center">
                <span class="text-white px-4 sm:px-5 py-2.5 rounded-xl font-bold uppercase text-[11px] sm:text-xs animate-bounce flex items-center shadow-sm text-center" style="background-color: var(--old-rose);">
                    <i class="fa-solid fa-circle-check mr-2 shrink-0"></i> QR Terdeteksi! Memproses...
                </span>
            </div>

            <a href="index.php" class="mt-6 text-xs font-bold transition-all flex items-center hover:opacity-80 active:opacity-70 group" style="color: var(--taupe-grey);">
                <i class="fa-solid fa-arrow-left mr-2 group-hover:-translate-x-1 transition-transform"></i> Kembali ke Dashboard
            </a>
        </div>

        <!-- List Presensi Aktif -->
        <div class="card-panel p-4 sm:p-6 md:p-8 rounded-2xl sm:rounded-3xl flex flex-col max-h-[320px] sm:max-h-[420px] md:max-h-[580px]">
            <div class="flex items-center justify-between mb-4 sm:mb-6 pb-4 border-b" style="border-color: var(--pale-slate);">
                <h3 class="text-base sm:text-lg font-bold tracking-tight flex items-center" style="color: var(--taupe-grey);">
                    <span class="relative flex h-2.5 w-2.5 mr-3 shrink-0">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full opacity-75" style="background-color: var(--old-rose);"></span>
                        <span class="relative inline-flex rounded-full h-2.5 w-2.5" style="background-color: var(--old-rose);"></span>
                    </span>
                    Di Dalam Perpustakaan
                </h3>
            </div>

            <div class="flex-1 overflow-y-auto pr-1 space-y-3" style="-webkit-overflow-scrolling: touch;">
                <?php 
                $q_present = mysqli_query($conn, "SELECT kunjungan.*, anggota.nama_anggota 
                                                 FROM kunjungan 
                                                 JOIN anggota ON kunjungan.id_anggota = anggota.id_anggota 
                                                 WHERE kunjungan.waktu_keluar IS NULL 
                                                 ORDER BY kunjungan.waktu_masuk DESC");
                
                if(mysqli_num_rows($q_present) > 0):
                    while($kp = mysqli_fetch_assoc($q_present)) : 
                ?>
                <div class="p-3 sm:p-4 rounded-2xl flex justify-between items-center gap-3 border transition-all" style="background-color: var(--soft-blush); border-color: var(--pale-slate);">
                    <div class="min-w-0">
                        <p class="font-bold text-sm truncate" style="color: var(--taupe-grey);"><?= htmlspecialchars($kp['nama_anggota']); ?></p>
                        <p class="text-[10px] font-extrabold uppercase tracking-wider mt-1 opacity-80" style="color: var(--taupe-grey);">
                            <i class="fa-regular fa-clock mr-1" style="color: var(--old-rose);"></i> Masuk: <?= date('H:i', strtotime($kp['waktu_masuk'])); ?> WIB
                        </p>
                    </div>
                    <div class="w-9 h-9 shrink-0 rounded-xl flex items-center justify-center text-sm text-white shadow-sm" style="background-color: var(--cool-steel);">
                        <i class="fa-solid fa-book-reader"></i>
                    </div>
                </div>
                <?php endwhile; 
                else: ?>
                <div class="h-full flex flex-col items-center justify-center italic py-16 opacity-60" style="color: var(--taupe-grey);">
                    <i class="fa-solid fa-users-slash text-3xl mb-3" style="color: var(--old-rose);"></i>
                    <p class="font-bold uppercase text-[10px] tracking-widest">Belum ada siswa di dalam</p>
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
    // Ukuran kotak scan menyesuaikan lebar layar area kamera (kompatibel HP kecil s/d desktop)
    function qrboxFunction(viewfinderWidth, viewfinderHeight) {
        let minEdge = Math.min(viewfinderWidth, viewfinderHeight);
        let size = Math.floor(minEdge * 0.7);
        size = Math.max(180, Math.min(size, 280));
        return { width: size, height: size };
    }

    let html5QrcodeScanner = new Html5QrcodeScanner(
        "reader", { 
            fps: 20, 
            qrbox: qrboxFunction,
            aspectRatio: 1.0 
        }
    );

    function onScanSuccess(decodedText, decodedResult) {
        let cleanID = decodedText.replace("USER-", "").trim();
        
        html5QrcodeScanner.clear();
        
        document.getElementById('result').classList.remove('hidden');
        document.getElementById('id_anggota_qr').value = cleanID;
        
        setTimeout(() => {
            document.getElementById('form-presensi').submit();
        }, 500);
    }

    // Kamera hanya bisa diakses browser lewat HTTPS atau localhost.
    // Kalau halaman ini dibuka via HTTP + alamat IP biasa, jangan render kamera
    // (nanti error-nya membingungkan), tampilkan pesan yang jelas saja.
    if (window.isSecureContext) {
        html5QrcodeScanner.render(onScanSuccess, () => {});
    } else {
        document.getElementById('reader').classList.add('hidden');
        document.getElementById('reader-insecure-warning').classList.remove('hidden');
    }

    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('status') === 'masuk_success') {
        Swal.fire({ 
            title: 'Selamat Datang!', 
            text: 'Presensi masuk berhasil', 
            icon: 'success', 
            timer: 2000, 
            showConfirmButton: false,
            confirmButtonColor: '#604D53'
        });
    } else if (urlParams.get('status') === 'keluar_success') {
        Swal.fire({ 
            title: 'Sampai Jumpa!', 
            text: 'Presensi keluar berhasil', 
            icon: 'info', 
            timer: 2000, 
            showConfirmButton: false,
            confirmButtonColor: '#604D53'
        });
    }
    </script>
</body>
</html>