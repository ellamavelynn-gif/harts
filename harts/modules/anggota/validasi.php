<?php
include '../../config/koneksi.php';

// Logika tombol "Selesai"
if (isset($_GET['action']) && $_GET['action'] == 'selesai') {
    $id = $_GET['id'];
    mysqli_query($conn, "UPDATE anggota SET status_akun = 'aktif' WHERE id_anggota = '$id'");
    header("Location: index.php?page=validasi"); // Sesuaikan dengan link navigasi kamu
}

// Ambil Token Aktif
$query_token = mysqli_query($conn, "SELECT * FROM token_regis WHERE status = 'aktif'");
// Ambil Siswa Baru (Status 'baru')
$query_baru = mysqli_query($conn, "SELECT * FROM anggota WHERE status_akun = 'baru' ORDER BY id_anggota DESC");
?>

<div class="p-8 space-y-8">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <div class="lg:col-span-1 bg-white/50 backdrop-blur-xl p-8 rounded-[3rem] border border-white shadow-xl">
            <h3 class="text-xl font-black text-slate-800 mb-6 flex items-center">
                <span class="mr-3">🔑</span> TOKEN AKTIF
            </h3>
            <div class="grid grid-cols-2 gap-3">
                <?php while($t = mysqli_fetch_assoc($query_token)) : ?>
                    <div class="bg-indigo-600 p-4 rounded-2xl text-center shadow-lg shadow-indigo-200">
                        <span class="font-mono font-black text-white text-lg tracking-widest"><?= $t['token']; ?></span>
                    </div>
                <?php endwhile; ?>
            </div>
            <p class="mt-6 text-[10px] text-slate-400 font-bold uppercase tracking-widest text-center italic">Token di atas siap dipakai siswa</p>
        </div>

        <div class="lg:col-span-2 bg-white/50 backdrop-blur-xl p-8 rounded-[3rem] border border-white shadow-xl">
            <h3 class="text-xl font-black text-slate-800 mb-6 flex items-center">
                <span class="mr-3">✨</span> SISWA BARU DAFTAR
            </h3>
            
            <div class="space-y-4">
                <?php if(mysqli_num_rows($query_baru) > 0) : ?>
                    <?php while($row = mysqli_fetch_assoc($query_baru)) : ?>
                    <div class="flex items-center justify-between bg-white p-6 rounded-[2rem] border border-indigo-50 hover:scale-[1.02] transition-all shadow-sm">
                        <div class="flex items-center">
                            <div class="w-12 h-12 bg-gradient-to-br from-indigo-500 to-purple-500 rounded-2xl flex items-center justify-center text-white font-bold text-xl mr-5 shadow-lg">
                                <?= strtoupper(substr($row['nama_anggota'], 0, 1)); ?>
                            </div>
                            <div>
                                <h4 class="font-black text-slate-800"><?= $row['nama_anggota']; ?></h4>
                                <p class="text-xs font-bold text-indigo-400 uppercase tracking-tighter">
                                    <?= $row['nis']; ?> • <?= $row['kelas']; ?>
                                </p>
                            </div>
                        </div>
                        
                        <a href="validasi.php?action=selesai&id=<?= $row['id_anggota']; ?>" 
                           class="bg-green-500 text-white px-6 py-3 rounded-2xl font-black text-xs hover:bg-green-600 shadow-lg shadow-green-100 transition-all uppercase tracking-widest">
                            SELESAI
                        </a>
                    </div>
                    <?php endwhile; ?>
                <?php else : ?>
                    <div class="py-10 text-center">
                        <p class="text-4xl mb-3">📭</p>
                        <p class="text-slate-400 font-bold uppercase text-xs tracking-widest">Belum ada siswa baru</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </div>
</div>