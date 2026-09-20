<?php
session_start();
include 'config/koneksi.php';

// Proteksi Halaman
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit;
}

$admin_data = $_SESSION['admin'];
$id_cari    = $admin_data['id_petugas'];
$error_message = "";
$success_message = "";

// PROSES UPDATE DATA (AKUN & PASSWORD)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Update Profile (Nama, Email, Kontak, Foto)
    if (isset($_POST['update_profile'])) {
        $nama_petugas = trim($_POST['nama_petugas']);
        $email        = trim($_POST['email']);
        $no_hp        = trim($_POST['no_hp']);
        
        $foto_name = $admin_data['foto'] ?? '';
        
        if (!empty($_FILES['foto']['name'])) {
            $target_dir = "uploads/";
            if (!file_exists($target_dir)) {
                mkdir($target_dir, 0777, true);
            }
            $ext = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
            $new_foto_name = "petugas_" . $id_cari . "_" . time() . "." . $ext;
            $target_file = $target_dir . $new_foto_name;

            if (move_uploaded_file($_FILES['foto']['tmp_name'], $target_file)) {
                $foto_name = $new_foto_name;
            } else {
                $error_message = "Gagal mengunggah foto profil.";
            }
        }

        if (empty($error_message)) {
            $stmt_up = mysqli_prepare($conn, "UPDATE petugas SET nama_petugas=?, email=?, no_hp=?, foto=? WHERE id_petugas=?");
            mysqli_stmt_bind_param($stmt_up, "ssssi", $nama_petugas, $email, $no_hp, $foto_name, $id_cari);
            
            if (mysqli_stmt_execute($stmt_up)) {
                $success_message = "Data profil berhasil diperbarui!";
                $_SESSION['admin']['nama_petugas'] = $nama_petugas;
                $_SESSION['admin']['email']        = $email;
                $_SESSION['admin']['no_hp']        = $no_hp;
                $_SESSION['admin']['foto']         = $foto_name;
            } else {
                $error_message = "Gagal memperbarui database: " . mysqli_error($conn);
            }
            mysqli_stmt_close($stmt_up);
        }
    }

    // 2. Update Password
    if (isset($_POST['update_password'])) {
        $pass_baru = $_POST['pass_baru'];

        if (empty($pass_baru)) {
            $error_message = "Password baru tidak boleh kosong!";
        } else {
            $hash_baru = password_hash($pass_baru, PASSWORD_DEFAULT);
            $stmt_pass = mysqli_prepare($conn, "UPDATE petugas SET password=? WHERE id_petugas=?");
            mysqli_stmt_bind_param($stmt_pass, "si", $hash_baru, $id_cari);
            if (mysqli_stmt_execute($stmt_pass)) {
                $success_message = "Password berhasil diperbarui!";
            } else {
                $error_message = "Gagal mengupdate password.";
            }
            mysqli_stmt_close($stmt_pass);
        }
    }
}

// Fetch Fresh Data
$stmt = mysqli_prepare($conn, "SELECT * FROM petugas WHERE id_petugas = ?");
mysqli_stmt_bind_param($stmt, "i", $id_cari);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$data   = mysqli_fetch_assoc($result) ?? $admin_data;

$admin_name = $data['nama_petugas'];
$initial    = strtoupper(substr($admin_name, 0, 1));
mysqli_stmt_close($stmt);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Saya - HARTS Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
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
            min-height: 100vh;
        }

        .card-custom { 
            background-color: #ffffff; 
            border: 1px solid var(--pale-slate); 
            box-shadow: 0 20px 40px -15px rgba(96, 77, 83, 0.12);
        }

        .wavy-header {
            background-color: var(--old-rose);
            height: 140px;
            border-top-left-radius: 1.5rem;
            border-top-right-radius: 1.5rem;
            position: relative;
        }

        .wavy-svg {
            position: absolute;
            bottom: -1px;
            left: 0;
            width: 100%;
            height: 40px;
            fill: #ffffff;
        }
    </style>
</head>
<body class="flex items-center justify-center p-4 sm:p-6">

    <!-- Card Profil Terpusat -->
    <div class="max-w-md w-full card-custom rounded-3xl overflow-hidden relative my-auto">
        
        <!-- Header Wave -->
        <div class="wavy-header flex items-center justify-between px-6 pt-5">
            <a href="index.php" class="w-9 h-9 rounded-full bg-white/20 backdrop-blur-sm flex items-center justify-center text-white hover:bg-white/30 transition-colors" title="Kembali ke Dashboard">
                <i class="fa-solid fa-chevron-left text-xs"></i>
            </a>
            <h2 class="text-white font-bold text-base tracking-wide">Profile</h2>
            <div class="w-9 h-9 rounded-full bg-white/20 backdrop-blur-sm flex items-center justify-center text-white text-xs font-bold" title="ID Petugas">
                #<?= sprintf('%02d', $data['id_petugas']); ?>
            </div>

            <svg class="wavy-svg" viewBox="0 0 500 150" preserveAspectRatio="none">
                <path d="M0,0 C150,90 350,-40 500,40 L500,150 L0,150 Z"></path>
            </svg>
        </div>

        <!-- Content Body -->
        <div class="px-6 pb-6 -mt-10 relative z-10">
            
            <!-- Circular Profile Avatar -->
            <form action="" method="POST" enctype="multipart/form-data" id="form-foto">
                <input type="hidden" name="update_profile" value="1">
                <input type="hidden" name="nama_petugas" value="<?= htmlspecialchars($data['nama_petugas'] ?? ''); ?>">
                <input type="hidden" name="email" value="<?= htmlspecialchars($data['email'] ?? ''); ?>">
                <input type="hidden" name="no_hp" value="<?= htmlspecialchars($data['no_hp'] ?? ''); ?>">
                
                <div class="flex flex-col items-center text-center mb-6">
                    <div class="relative group">
                        <div class="w-24 h-24 rounded-full p-1 bg-white shadow-lg">
                            <div class="w-full h-full rounded-full overflow-hidden flex items-center justify-center text-3xl font-extrabold text-white" style="background-color: var(--old-rose);">
                                <img id="profile-preview" src="<?= (!empty($data['foto']) && file_exists('uploads/' . $data['foto'])) ? 'uploads/' . $data['foto'] : 'https://ui-avatars.com/api/?name=' . urlencode($admin_name) . '&background=DB7F8E&color=fff'; ?>" class="w-full h-full object-cover">
                            </div>
                        </div>
                        <label for="foto-input" class="absolute bottom-0 right-0 w-8 h-8 rounded-full flex items-center justify-center text-white cursor-pointer shadow-md transition-transform hover:scale-110" style="background-color: var(--taupe-grey);" title="Ganti Foto">
                            <i class="fa-solid fa-camera text-xs"></i>
                        </label>
                        <input type="file" id="foto-input" name="foto" accept="image/*" class="hidden" onchange="document.getElementById('form-foto').submit();">
                    </div>

                    <h3 class="text-xl font-extrabold mt-3 tracking-tight" style="color: var(--taupe-grey);"><?= htmlspecialchars($admin_name); ?></h3>
                    <p class="text-xs font-semibold opacity-60 flex items-center gap-1 mt-0.5" style="color: var(--taupe-grey);">
                        <i class="fa-solid fa-location-dot text-[10px]" style="color: var(--old-rose);"></i> HARTS Library System
                    </p>
                </div>
            </form>

            <!-- Alert Messages -->
            <?php if (!empty($error_message)): ?>
                <div class="mb-4 p-3 rounded-xl bg-red-50 border border-red-200 text-red-600 text-xs font-semibold flex items-center gap-2">
                    <i class="fa-solid fa-circle-exclamation text-sm"></i> <?= $error_message; ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($success_message)): ?>
                <div class="mb-4 p-3 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-600 text-xs font-semibold flex items-center gap-2">
                    <i class="fa-solid fa-circle-check text-sm"></i> <?= $success_message; ?>
                </div>
            <?php endif; ?>

            <!-- Single Card: Account (Termasuk Password & Tombol Edit/Simpan) -->
            <div class="space-y-3">
                <h4 class="text-xs font-extrabold tracking-wider uppercase opacity-50 px-1" style="color: var(--taupe-grey);">Account</h4>
                
                <div class="p-4 rounded-2xl bg-stone-50 border space-y-3.5" style="border-color: var(--pale-slate);">
                    
                    <!-- Form Utama untuk Profil -->
                    <form action="" method="POST" id="main-profile-form">
                        <input type="hidden" name="update_profile" value="1">

                        <!-- Username (Readonly) -->
                        <div class="flex items-center justify-between text-xs pb-3 border-b" style="border-color: var(--pale-slate);">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-xl flex items-center justify-center text-white text-xs shadow-sm" style="background-color: var(--old-rose);">
                                    <i class="fa-solid fa-user-shield"></i>
                                </div>
                                <span class="font-bold">Username</span>
                            </div>
                            <span class="font-bold opacity-50 pr-2">@<?= htmlspecialchars($data['username']); ?></span>
                        </div>

                        <!-- Nama -->
                        <div class="flex items-center justify-between text-xs py-3 border-b" style="border-color: var(--pale-slate);">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-xl flex items-center justify-center text-white text-xs shadow-sm" style="background-color: var(--old-rose);">
                                    <i class="fa-solid fa-signature"></i>
                                </div>
                                <span class="font-bold">Nama</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <input type="text" id="input-nama" name="nama_petugas" value="<?= htmlspecialchars($data['nama_petugas'] ?? ''); ?>" readonly class="text-right font-bold bg-transparent outline-none w-36 border-b border-transparent focus:border-stone-400 readonly:opacity-70">
                                <button type="button" onclick="enableEdit('input-nama', 'save-profile-btn')" class="text-stone-400 hover:text-stone-700 transition-colors p-1" title="Edit Nama">
                                    <i class="fa-solid fa-pen text-xs"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Email -->
                        <div class="flex items-center justify-between text-xs py-3 border-b" style="border-color: var(--pale-slate);">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-xl flex items-center justify-center text-white text-xs shadow-sm" style="background-color: var(--old-rose);">
                                    <i class="fa-solid fa-envelope"></i>
                                </div>
                                <span class="font-bold">Email</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <input type="email" id="input-email" name="email" value="<?= htmlspecialchars($data['email'] ?? ''); ?>" readonly placeholder="petugas@harts.com" class="text-right font-bold bg-transparent outline-none w-40 border-b border-transparent focus:border-stone-400 readonly:opacity-70">
                                <button type="button" onclick="enableEdit('input-email', 'save-profile-btn')" class="text-stone-400 hover:text-stone-700 transition-colors p-1" title="Edit Email">
                                    <i class="fa-solid fa-pen text-xs"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Kontak -->
                        <div class="flex items-center justify-between text-xs py-3 border-b" style="border-color: var(--pale-slate);">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-xl flex items-center justify-center text-white text-xs shadow-sm" style="background-color: var(--old-rose);">
                                    <i class="fa-solid fa-phone"></i>
                                </div>
                                <span class="font-bold">Kontak</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <input type="text" id="input-nohp" name="no_hp" value="<?= htmlspecialchars($data['no_hp'] ?? ''); ?>" readonly placeholder="08xxxxxxxxxx" class="text-right font-bold bg-transparent outline-none w-32 border-b border-transparent focus:border-stone-400 readonly:opacity-70">
                                <button type="button" onclick="enableEdit('input-nohp', 'save-profile-btn')" class="text-stone-400 hover:text-stone-700 transition-colors p-1" title="Edit Kontak">
                                    <i class="fa-solid fa-pen text-xs"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Tombol Simpan Profil (Otomatis muncul pas pensil diklik) -->
                        <div id="save-profile-btn" class="hidden pt-3">
                            <button type="submit" class="w-full py-2.5 rounded-xl font-extrabold text-xs text-white shadow-sm hover:opacity-90 transition-all flex items-center justify-center gap-1.5" style="background-color: var(--old-rose);">
                                <i class="fa-solid fa-check text-sm"></i> Simpan Perubahan Profil
                            </button>
                        </div>
                    </form>

                    <!-- Form Password (Gabung di Card Account) -->
                    <form action="" method="POST" id="pass-form">
                        <input type="hidden" name="update_password" value="1">
                        <div class="flex items-center justify-between text-xs pt-1">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-xl flex items-center justify-center text-white text-xs shadow-sm" style="background-color: var(--taupe-grey);">
                                    <i class="fa-solid fa-key"></i>
                                </div>
                                <span class="font-bold">Password</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <input type="password" id="input-pass" name="pass_baru" value="••••••••" readonly placeholder="Password baru..." class="text-right font-bold bg-transparent outline-none w-32 border-b border-transparent focus:border-stone-400 readonly:opacity-70">
                                
                                <button type="button" id="edit-pass-btn" onclick="enablePasswordEdit()" class="text-stone-400 hover:text-stone-700 transition-colors p-1" title="Ganti Password">
                                    <i class="fa-solid fa-pen text-xs"></i>
                                </button>

                                <button type="submit" id="save-pass-btn" class="hidden text-emerald-600 hover:text-emerald-700 transition-colors p-1" title="Simpan Password">
                                    <i class="fa-solid fa-circle-check text-base"></i>
                                </button>
                            </div>
                        </div>
                    </form>

                </div>
            </div>

        </div>
    </div>

    <script>
        // Fungsi untuk aktifkan input profil & tampilkan tombol simpan profil
        function enableEdit(inputId, btnContainerId) {
            const input = document.getElementById(inputId);
            const btnContainer = document.getElementById(btnContainerId);
            
            input.removeAttribute('readonly');
            input.focus();
            input.classList.remove('readonly:opacity-70');
            input.classList.add('border-stone-400', 'bg-white', 'px-2', 'py-0.5', 'rounded-lg');

            if (btnContainer) {
                btnContainer.classList.remove('hidden');
            }
        }

        // Fungsi khusus aktifkan mode ubah password
        function enablePasswordEdit() {
            const inputPass = document.getElementById('input-pass');
            const editBtn = document.getElementById('edit-pass-btn');
            const saveBtn = document.getElementById('save-pass-btn');

            inputPass.removeAttribute('readonly');
            inputPass.value = '';
            inputPass.focus();
            inputPass.classList.add('border-stone-400', 'bg-white', 'px-2', 'py-0.5', 'rounded-lg');
            
            editBtn.classList.add('hidden');
            saveBtn.classList.remove('hidden');
        }
    </script>
</body>
</html>