<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit;
}
include 'config/koneksi.php';

$current_page = 'peminjaman_aktif.php';
$admin_data   =$_SESSION['admin'];
$admin_name   =$admin_data['nama_petugas'] ?? "Admin";
$initial      = strtoupper(substr($admin_name, 0, 1));

// --- FUNGSI HITUNG DENDA & SISA WAKTU (Disamakan & Disinkronkan) ---
function hitungDendaInfo($tgl_deadline) {
    if (!$tgl_deadline ||$tgl_deadline == '0000-00-00' || $tgl_deadline == '0000-00-00 00:00:00' || trim($tgl_deadline) == '') {
        return ['telat' => 0, 'denda' => 0, 'sisa_hari' => -1];
    }
    
    date_default_timezone_set('Asia/Jakarta');
    $time_deadline = strtotime($tgl_deadline);

    if (!$time_deadline) {
        return ['telat' => 0, 'denda' => 0, 'sisa_hari' => -1];
    }

    $tgl_sekarang = new DateTime(date('Y-m-d')); 
    $tgl_tenggat  = new DateTime(date('Y-m-d',$time_deadline));
    
    if ($tgl_sekarang > $tgl_tenggat) {$selisih = $tgl_tenggat->diff($tgl_sekarang);
        $telat_hari = (int)$selisih->days; // Menggunakan ->days agar akurat
        if ($telat_hari > 0) {
            return ['telat' => $telat_hari, 'denda' => $telat_hari * 5000, 'sisa_hari' => 0];
        }
    } else {$selisih = $tgl_sekarang->diff($tgl_tenggat);
        $sisa_hari = (int)$selisih->format("%r%a");
        return ['telat' => 0, 'denda' => 0, 'sisa_hari' => $sisa_hari];
    }
    return ['telat' => 0, 'denda' => 0, 'sisa_hari' => 0];
}

// QUERY UTAMA
$query = mysqli_query($conn, "
    SELECT peminjaman.*, anggota.nama_anggota, buku.judul_buku 
    FROM peminjaman 
    LEFT JOIN anggota ON peminjaman.id_anggota = anggota.id_anggota 
    LEFT JOIN buku ON peminjaman.id_buku = buku.id_buku 
    WHERE LOWER(peminjaman.status) = 'dipinjam'
    ORDER BY peminjaman.kode_transaksi DESC
");

// Saring pinjaman: HANYA yang BELUM telat (< 1 hari) yang tampil di sini
$list_aktif = [];
if ($query) {
    while ($row = mysqli_fetch_assoc($query)) {$tgl_kembali = $row['tgl_kembali'] ?? $row['tanggal_kembali'] ?? '-';
        $info_denda  = hitungDendaInfo($tgl_kembali);

        // Jika belum telat (telat == 0), masukkan ke list aktif
        if ($info_denda['telat'] < 1) {
            $row['tgl_kembali_final'] =$tgl_kembali;
            $row['info_denda']        =$info_denda;
            $list_aktif[] =$row;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Peminjaman Aktif - HARTS Admin</title>
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

        body { 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            background-color: var(--soft-blush); 
            color: var(--taupe-grey);
        }

        .sidebar-theme { 
            background-color: var(--cool-steel); 
            border-right: 1px solid rgba(96, 77, 83, 0.12); 
        }

        .nav-item {
            color: #ffffff;
            transition: all 0.2s ease-in-out;
        }

        .nav-item:hover {
            background-color: rgba(255, 255, 255, 0.2);
            color: #ffffff;
        }

        .card-custom { 
            background-color: #ffffff; 
            border: 1px solid var(--pale-slate); 
            box-shadow: 0 10px 25px -5px rgba(96, 77, 83, 0.04);
        }

        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-thumb { background: var(--old-rose); border-radius: 10px; }
    </style>
</head>
<body class="min-h-screen flex flex-col md:flex-row antialiased">

    <!-- Mobile Top Navigation Header -->
    <div class="md:hidden flex items-center justify-between p-4 sidebar-theme text-white sticky top-0 z-50">
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-lg flex items-center justify-center text-white" style="background-color: var(--taupe-grey);">
                <i class="fa-solid fa-book-bookmark text-sm"></i>
            </div>
            <span class="font-extrabold text-lg uppercase tracking-wider">Harts</span>
        </div>
        <button id="toggleSidebar" class="p-2 rounded-lg bg-white/10 hover:bg-white/20 text-white focus:outline-none">
            <i class="fa-solid fa-bars text-xl"></i>
        </button>
    </div>

    <!-- Sidebar Overlay Mobile -->
    <div id="sidebarOverlay" class="fixed inset-0 bg-black/50 z-40 hidden md:hidden"></div>

    <!-- Sidebar Menu -->
    <aside id="sidebar" class="fixed md:static inset-y-0 left-0 w-72 h-full sidebar-theme p-6 flex flex-col shadow-sm z-50 transform -translate-x-full md:translate-x-0 transition-transform duration-300 ease-in-out">
        <div class="flex items-center justify-between mb-8 px-2">
            <div class="flex items-center">
                <div class="w-10 h-10 rounded-xl mr-3 shadow-sm flex items-center justify-center text-white" style="background-color: var(--taupe-grey);">
                    <i class="fa-solid fa-book-bookmark text-lg"></i>
                </div>
                <div>
                    <h1 class="text-xl font-extrabold tracking-tight uppercase text-white leading-none">Harts</h1>
                    <span class="text-[10px] font-semibold tracking-wider text-white/80 uppercase">Library System</span>
                </div>
            </div>
            <button id="closeSidebar" class="md:hidden text-white/80 hover:text-white">
                <i class="fa-solid fa-xmark text-xl"></i>
            </button>
        </div>
        
        <!-- User Badge -->
        <a href="profile.php" class="p-3.5 rounded-2xl mb-6 flex items-center transition-all group border border-white/20 bg-white/10 hover:bg-white/20">
            <div class="w-9 h-9 rounded-xl flex items-center justify-center font-bold text-sm mr-3 text-white transition-transform group-hover:scale-105 shadow-sm" style="background-color: var(--old-rose);">
                <?= $initial; ?>
            </div>
            <div class="overflow-hidden">
                <p class="text-[10px] font-bold uppercase tracking-widest text-white/70">Petugas</p>
                <p class="text-sm font-bold leading-tight text-white truncate"><?= htmlspecialchars($admin_name); ?></p>
            </div>
        </a>

        <!-- Navigation Links -->
        <nav class="space-y-1 flex-1 overflow-y-auto pr-1">
            <p class="text-[10px] font-extrabold uppercase tracking-widest ml-3 mb-2 text-white/60">Utama</p>
            <a href="index.php" class="nav-item flex items-center px-4 py-2.5 rounded-xl text-sm font-medium">
                <i class="fa-solid fa-house w-6 text-center text-sm mr-2.5"></i> Dashboard
            </a>
            
            <p class="text-[10px] font-extrabold uppercase tracking-widest ml-3 mt-6 mb-2 text-white/60">Layanan & Presensi</p>
            <a href="presensi.php" class="nav-item flex items-center px-4 py-2.5 rounded-xl text-sm font-medium">
                <i class="fa-solid fa-qrcode w-6 text-center text-sm mr-2.5"></i> Scanner Presensi
            </a>
            <a href="log_kunjungan.php" class="nav-item flex items-center px-4 py-2.5 rounded-xl text-sm font-medium">
                <i class="fa-solid fa-clipboard-user w-6 text-center text-sm mr-2.5"></i> Log Kunjungan
            </a>

            <p class="text-[10px] font-extrabold uppercase tracking-widest ml-3 mt-6 mb-2 text-white/60">Keuangan</p>
            <a href="kas_denda.php" class="nav-item flex items-center px-4 py-2.5 rounded-xl text-sm font-medium">
                <i class="fa-solid fa-wallet w-6 text-center text-sm mr-2.5"></i> Kas Denda
            </a>
            <a href="monitoring_denda.php" class="nav-item flex items-center px-4 py-2.5 rounded-xl text-sm font-medium">
                <i class="fa-solid fa-clock-rotate-left w-6 text-center text-sm mr-2.5"></i> Monitoring Denda
            </a>

            <p class="text-[10px] font-extrabold uppercase tracking-widest ml-3 mt-6 mb-2 text-white/60">Katalog & Anggota</p>
            <a href="modules/anggota/index.php?page=daftar" class="nav-item flex items-center px-4 py-2.5 rounded-xl text-sm font-medium">
                <i class="fa-solid fa-users w-6 text-center text-sm mr-2.5"></i> Data Anggota
            </a>
            <a href="modules/anggota/index.php?page=validasi" class="nav-item flex items-center px-4 py-2.5 rounded-xl text-sm font-medium">
                <i class="fa-solid fa-user-check w-6 text-center text-sm mr-2.5"></i> Validasi Akun
            </a>
            <a href="modules/buku/index.php" class="nav-item flex items-center px-4 py-2.5 rounded-xl text-sm font-medium">
                <i class="fa-solid fa-book w-6 text-center text-sm mr-2.5"></i> Katalog Buku
            </a>
            <a href="modules/rak/rak.php" class="nav-item flex items-center px-4 py-2.5 rounded-xl text-sm font-medium">
                <i class="fa-solid fa-cubes w-6 text-center text-sm mr-2.5"></i> Data Rak
            </a>

            <p class="text-[10px] font-extrabold uppercase tracking-widest ml-3 mt-6 mb-2 text-white/60">Laporan</p>
            <a href="laporan.php" class="nav-item flex items-center px-4 py-2.5 rounded-xl text-sm font-medium">
                <i class="fa-solid fa-chart-pie w-6 text-center text-sm mr-2.5"></i> Laporan Utama
            </a>
        </nav>
        
        <a href="logout.php" class="px-4 py-3 text-white/90 hover:text-white font-bold flex items-center hover:bg-white/10 rounded-xl mt-[20px] transition-colors text-sm">
            <i class="fa-solid fa-arrow-right-from-bracket mr-2.5 text-center w-6"></i> Keluar
        </a>
    </aside>

    <!-- Main Content Area -->
    <main class="flex-1 w-full min-w-0 p-4 sm:p-6 lg:p-10 overflow-y-auto">
        <div class="max-w-7xl mx-auto">
            
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6 sm:mb-8">
                <div>
                    <h2 class="text-xl sm:text-2xl lg:text-3xl font-extrabold tracking-tight" style="color: var(--taupe-grey);">
                        Peminjaman Aktif <i class="fa-solid fa-clock-rotate-left text-lg sm:text-xl ml-1" style="color: var(--old-rose);"></i>
                    </h2>
                    <p class="text-xs sm:text-sm font-medium opacity-80 mt-1" style="color: var(--taupe-grey);">
                        Daftar buku yang sedang dipinjam dan belum dikembalikan.
                    </p>
                </div>
                <a href="index.php" class="inline-flex items-center justify-center px-4 py-2.5 rounded-2xl font-bold text-xs sm:text-sm bg-white border transition-all hover:bg-stone-50 w-fit" style="border-color: var(--pale-slate); color: var(--taupe-grey);">
                    <i class="fa-solid fa-arrow-left mr-2"></i> Kembali
                </a>
            </div>

            <div class="card-custom rounded-2xl sm:rounded-3xl p-4 sm:p-6 lg:p-8">
                <div class="flex items-center mb-4 sm:mb-6">
                    <div class="w-1.5 h-5 rounded-full mr-3 shrink-0" style="background-color: var(--old-rose);"></div>
                    <h3 class="text-base sm:text-lg font-bold tracking-tight" style="color: var(--taupe-grey);">Daftar Pinjaman Aktif</h3>
                </div>

                <?php if (count($list_aktif) > 0) : ?>
                    <!-- Tampilan Mobile -->
                    <div class="grid grid-cols-1 gap-4 md:hidden">
                        <?php foreach ($list_aktif as$row) : 
                            $tgl_kembali =$row['tgl_kembali_final'];
                            $info_denda  =$row['info_denda'];
                            $kode_trans  =$row['kode_transaksi'] ?? $row['id_peminjaman'] ?? $row['id'] ?? '';
                        ?>
                            <div class="p-4 rounded-2xl border bg-stone-50/50 space-y-3" style="border-color: var(--pale-slate);">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <span class="text-[10px] font-bold uppercase tracking-wider text-stone-400 block">Peminjam</span>
                                        <h4 class="font-bold text-sm" style="color: var(--taupe-grey);"><?= htmlspecialchars($row['nama_anggota'] ?? 'Anggota Tak Dikenal'); ?></h4>
                                    </div>
                                    <span class="px-2.5 py-1 rounded-xl text-[10px] font-extrabold uppercase tracking-wider border" style="background-color: var(--soft-blush); color: var(--taupe-grey);">
                                        Jml: <?= $row['jumlah'] ?? 1; ?>
                                    </span>
                                </div>

                                <div>
                                    <span class="text-[10px] font-bold uppercase tracking-wider text-stone-400 block">Judul Buku</span>
                                    <p class="text-xs font-semibold" style="color: var(--taupe-grey);">"<?= htmlspecialchars($row['judul_buku'] ?? 'Judul Tidak Ada'); ?>"</p>
                                </div>

                                <div class="flex justify-between items-center pt-2 border-t border-stone-200">
                                    <div>
                                        <span class="text-[10px] font-bold uppercase tracking-wider text-stone-400 block">Tenggat</span>
                                        <span class="text-xs font-bold"><?= ($tgl_kembali != '-') ? date('d M Y', strtotime($tgl_kembali)) : '-'; ?></span>
                                    </div>
                                    <div>
                                        <?php if ($info_denda['sisa_hari'] == 0) : ?>
                                            <span class="px-2 py-0.5 rounded-lg text-[9px] font-extrabold uppercase bg-amber-50 text-amber-700 border border-amber-200">Hari Ini Tenggat</span>
                                        <?php elseif ($info_denda['sisa_hari'] == 1) : ?>
                                            <span class="px-2 py-0.5 rounded-lg text-[9px] font-extrabold uppercase bg-amber-50 text-amber-700 border border-amber-200">H-1</span>
                                        <?php else : ?>
                                            <span class="px-2 py-0.5 rounded-lg text-[9px] font-extrabold uppercase bg-emerald-50 text-emerald-700 border border-emerald-200">Aman</span>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 gap-2 pt-2">
                                    <button onclick="konfirmasiKembali('<?= $kode_trans; ?>')" class="w-full py-2 text-white font-extrabold text-xs rounded-xl shadow-sm transition-all uppercase tracking-wider" style="background-color: var(--old-rose);">
                                        <i class="fa-solid fa-rotate-left mr-1"></i> Kembali
                                    </button>
                                    <button onclick="inputDenda('<?= $kode_trans; ?>')" class="w-full py-2 text-red-600 font-extrabold text-xs rounded-xl border border-red-200 bg-red-50 hover:bg-red-100 transition-all">
                                        <i class="fa-solid fa-triangle-exclamation mr-1"></i> Denda
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Tampilan Desktop -->
                    <div class="hidden md:block overflow-x-auto w-full">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="border-b text-[10px] uppercase tracking-widest font-extrabold" style="border-color: var(--pale-slate); color: var(--taupe-grey);">
                                    <th class="pb-4 px-4">Nama Peminjam</th>
                                    <th class="pb-4 px-4">Judul Buku</th>
                                    <th class="pb-4 px-4 text-center">Jumlah</th>
                                    <th class="pb-4 px-4 text-center">Tenggat</th>
                                    <th class="pb-4 px-4 text-center">Status</th>
                                    <th class="pb-4 px-4 text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y text-sm font-semibold" style="border-color: var(--pale-slate); color: var(--taupe-grey);">
                                <?php foreach ($list_aktif as$row) : 
                                    $tgl_kembali =$row['tgl_kembali_final'];
                                    $info_denda  =$row['info_denda'];
                                    $kode_trans  =$row['kode_transaksi'] ?? $row['id_peminjaman'] ?? $row['id'] ?? '';
                                ?>
                                    <tr class="hover:bg-stone-50 transition-colors">
                                        <td class="py-4 px-4 font-bold"><?= htmlspecialchars($row['nama_anggota'] ?? 'Anggota Tak Dikenal'); ?></td>
                                        <td class="py-4 px-4 font-semibold"><?= htmlspecialchars($row['judul_buku'] ?? 'Judul Tidak Ada'); ?></td>
                                        <td class="py-4 px-4 text-center font-bold" style="color: var(--old-rose);"><?= $row['jumlah'] ?? 1; ?></td>
                                        <td class="py-4 px-4 text-center font-bold opacity-80">
                                            <?= ($tgl_kembali != '-') ? date('d M Y', strtotime($tgl_kembali)) : '-'; ?>
                                        </td>
                                        <td class="py-4 px-4 text-center">
                                            <?php if ($info_denda['sisa_hari'] == 0) : ?>
                                                <span class="px-2.5 py-1 rounded-xl text-[10px] font-extrabold uppercase tracking-wider border bg-amber-50 text-amber-700 border-amber-200 inline-block">
                                                    Hari Ini Tenggat
                                                </span>
                                            <?php elseif ($info_denda['sisa_hari'] == 1) : ?>
                                                <span class="px-2.5 py-1 rounded-xl text-[10px] font-extrabold uppercase tracking-wider border bg-amber-50 text-amber-700 border-amber-200 inline-block">
                                                    H-1 
                                                </span>
                                            <?php else : ?>
                                                <span class="px-2.5 py-1 rounded-xl text-[10px] font-extrabold uppercase tracking-wider border bg-emerald-50 text-emerald-700 border-emerald-200 inline-block">
                                                    Aman
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="py-4 px-4 text-center">
                                            <div class="flex items-center justify-center gap-2">
                                                <button onclick="konfirmasiKembali('<?= $kode_trans; ?>')" 
                                                        class="px-3.5 py-2 text-white font-extrabold text-xs rounded-xl shadow-sm hover:opacity-90 transition-all uppercase tracking-wider" style="background-color: var(--old-rose);">
                                                    <i class="fa-solid fa-rotate-left mr-1"></i> Kembali
                                                </button>
                                                <button onclick="inputDenda('<?= $kode_trans; ?>')" 
                                                        class="px-3 py-2 text-red-600 font-extrabold text-xs rounded-xl border border-red-200 bg-red-50 hover:bg-red-100 transition-all"
                                                        title="Kembali dengan kondisi rusak/denda">
                                                    <i class="fa-solid fa-triangle-exclamation mr-1"></i> Denda
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else : ?>
                    <div class="py-12 text-center text-xs sm:text-sm font-semibold italic opacity-60">
                        Tidak ada buku yang sedang dipinjam saat ini (atau semua pinjaman aktif telah melewati batas tenggat).
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <script>
        const sidebar = document.getElementById('sidebar');
        const toggleSidebarBtn = document.getElementById('toggleSidebar');
        const closeSidebarBtn = document.getElementById('closeSidebar');
        const overlay = document.getElementById('sidebarOverlay');

        function openSidebar() {
            sidebar.classList.remove('-translate-x-full');
            overlay.classList.remove('hidden');
        }

        function closeSidebar() {
            sidebar.classList.add('-translate-x-full');
            overlay.classList.add('hidden');
        }

        toggleSidebarBtn?.addEventListener('click', openSidebar);
        closeSidebarBtn?.addEventListener('click', closeSidebar);
        overlay?.addEventListener('click', closeSidebar);

        function konfirmasiKembali(id) {
            Swal.fire({
                title: 'Proses Pengembalian?',
                text: "Buku dikembalikan dalam kondisi baik.",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#DB7F8E',
                cancelButtonColor: '#9DA3A4',
                confirmButtonText: 'Ya, Kembalikan!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'proses_kembali.php?id=' + id;
                }
            });
        }

        function inputDenda(id) {
            Swal.fire({
                title: 'Pengembalian & Denda',
                html: `
                    <p class="text-xs text-stone-500 mb-4">Masukkan jumlah denda kerusakan/hilang dan alasannya.</p>
                    <div class="text-left space-y-3">
                        <div>
                            <label class="text-[10px] font-bold uppercase tracking-wider text-stone-600">Nominal Denda (Rp)</label>
                            <input id="swal-denda" type="number" min="0" placeholder="Contoh: 20000" class="w-full mt-1 px-4 py-2 border rounded-xl text-stone-800 font-bold focus:outline-none">
                        </div>
                        <div>
                            <label class="text-[10px] font-bold uppercase tracking-wider text-stone-600">Keterangan / Alasan Denda</label>
                            <textarea id="swal-ket" placeholder="Contoh: Cover buku sobek / Halaman hilang" class="w-full mt-1 px-4 py-2 border rounded-xl text-stone-800 text-sm focus:outline-none" rows="3"></textarea>
                        </div>
                    </div>
                `,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#DB7F8E',
                cancelButtonColor: '#9DA3A4',
                confirmButtonText: 'Proses Denda',
                cancelButtonText: 'Batal',
                preConfirm: () => {
                    const denda = document.getElementById('swal-denda').value;
                    const ket = document.getElementById('swal-ket').value;
                    if (!denda || denda < 0) {
                        Swal.showValidationMessage('Harap masukkan nominal denda yang valid!');
                        return false;
                    }
                    return { denda: denda, keterangan: ket };
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    const denda = result.value.denda;
                    const ket = encodeURIComponent(result.value.keterangan);
                    window.location.href = `proses_kembali.php?id=${id}&denda=${denda}&ket=${ket}`;
                }
            });
        }
    </script>
</body>
</html>