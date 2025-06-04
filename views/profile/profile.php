<?php
session_start();
require_once 'config.php';

// Redirect jika belum login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

// Ambil data pengguna dari database
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Pengguna tidak ditemukan");
}

$user = $result->fetch_assoc();

// Ambil data transaksi
$transactions = [];
$stmt = $conn->prepare("SELECT * FROM transactions WHERE user_id = ? ORDER BY transaction_date DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $transactions[] = $row;
}

// Ambil data kupon
$coupons = [];
$stmt = $conn->prepare("SELECT c.* FROM coupons c 
                       JOIN user_coupons uc ON c.id = uc.coupon_id 
                       WHERE uc.user_id = ? AND uc.used = 0 AND c.expiry_date > NOW()");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $coupons[] = $row;
}

// Ambil data keanggotaan
$stmt = $conn->prepare("SELECT m.level_name, m.discount_percentage, m.min_points, 
                       (SELECT COUNT(*) FROM users WHERE membership_level = m.level_name) as total_members
                       FROM membership_levels m 
                       WHERE ? BETWEEN m.min_points AND m.max_points");
$stmt->bind_param("i", $user['points']);
$stmt->execute();
$result = $stmt->get_result();
$membership = $result->fetch_assoc();

// Handle upload foto profil
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profile_picture'])) {
    $target_dir = "uploads/profiles/";
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    $file_ext = strtolower(pathinfo($_FILES["profile_picture"]["name"], PATHINFO_EXTENSION));
    $target_file = $target_dir . "user_" . $user_id . "." . $file_ext;
    
    // Validasi file
    $check = getimagesize($_FILES["profile_picture"]["tmp_name"]);
    if ($check !== false) {
        // Batasi ukuran file (max 2MB)
        if ($_FILES["profile_picture"]["size"] > 2000000) {
            $error = "Ukuran file terlalu besar. Maksimal 2MB.";
        } elseif (!in_array($file_ext, ['jpg', 'jpeg', 'png', 'gif'])) {
            $error = "Hanya file JPG, JPEG, PNG & GIF yang diperbolehkan.";
        } else {
            // Hapus file lama jika ada
            if (!empty($user['profile_picture']) && file_exists($user['profile_picture'])) {
                unlink($user['profile_picture']);
            }
            
            if (move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $target_file)) {
                // Update foto profil di database
                $stmt = $conn->prepare("UPDATE users SET profile_picture = ? WHERE id = ?");
                $stmt->bind_param("si", $target_file, $user_id);
                if ($stmt->execute()) {
                    $user['profile_picture'] = $target_file;
                    $success = "Foto profil berhasil diupdate!";
                } else {
                    $error = "Gagal menyimpan ke database.";
                }
            } else {
                $error = "Terjadi kesalahan saat mengupload file.";
            }
        }
    } else {
        $error = "File yang diupload bukan gambar.";
    }
}

// Handle update profil
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $birthdate = $_POST['birthdate'];
    $address = $_POST['address'];
    
    $stmt = $conn->prepare("UPDATE users SET name = ?, email = ?, phone = ?, birthdate = ?, address = ? WHERE id = ?");
    $stmt->bind_param("sssssi", $name, $email, $phone, $birthdate, $address, $user_id);
    
    if ($stmt->execute()) {
        // Update data session
        $user['name'] = $name;
        $user['email'] = $email;
        $user['phone'] = $phone;
        $user['birthdate'] = $birthdate;
        $user['address'] = $address;
        
        $success = "Profil berhasil diperbarui!";
    } else {
        $error = "Gagal memperbarui profil: " . $conn->error;
    }
}

// Handle update pengaturan
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_settings'])) {
    $email_notif = isset($_POST['email_notif']) ? 1 : 0;
    $sms_notif = isset($_POST['sms_notif']) ? 1 : 0;
    $promo_notif = isset($_POST['promo_notif']) ? 1 : 0;
    $theme = $_POST['theme'];
    $language = $_POST['language'];
    
    $stmt = $conn->prepare("UPDATE users SET 
                          email_notifications = ?, 
                          sms_notifications = ?, 
                          promo_notifications = ?, 
                          theme_preference = ?, 
                          language_preference = ? 
                          WHERE id = ?");
    $stmt->bind_param("iissi", $email_notif, $sms_notif, $promo_notif, $theme, $language, $user_id);
    
    if ($stmt->execute()) {
        $success = "Pengaturan berhasil diperbarui!";
    } else {
        $error = "Gagal memperbarui pengaturan: " . $conn->error;
    }
}

// Tentukan konten aktif berdasarkan parameter URL
$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'profile';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Saya</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome untuk ikon -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <!-- Notifikasi -->
        <?php if (!empty($error)): ?>
            <div class="mb-4 p-4 bg-red-100 text-red-700 rounded-md">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($success)): ?>
            <div class="mb-4 p-4 bg-green-100 text-green-700 rounded-md">
                <?php echo $success; ?>
            </div>
        <?php endif; ?>

        <div class="flex flex-col md:flex-row gap-6">
            <!-- Sidebar Profil -->
            <div class="w-full md:w-1/4 lg:w-1/5 bg-white rounded-lg shadow-md p-6">
                <div class="flex flex-col items-center mb-6">
                    <div class="relative mb-4">
                        <img src="<?php echo !empty($user['profile_picture']) ? htmlspecialchars($user['profile_picture']) : 'default_profile.jpg'; ?>" 
                             alt="Foto Profil" 
                             class="w-24 h-24 rounded-full object-cover border-4 border-gray-200">
                        <form method="post" enctype="multipart/form-data" class="mt-2">
                            <label for="profile_picture" class="block text-sm font-medium text-gray-700 mb-1">Ubah Foto</label>
                            <input type="file" name="profile_picture" id="profile_picture" 
                                   class="block w-full text-sm text-gray-500
                                          file:mr-4 file:py-2 file:px-4
                                          file:rounded-md file:border-0
                                          file:text-sm file:font-semibold
                                          file:bg-blue-50 file:text-blue-700
                                          hover:file:bg-blue-100">
                            <button type="submit" 
                                    class="mt-2 w-full bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded-md text-sm">
                                Simpan Foto
                            </button>
                        </form>
                    </div>
                    <h2 class="text-xl font-bold text-gray-800"><?php echo htmlspecialchars($user['name']); ?></h2>
                    <p class="text-gray-600 text-sm"><?php echo htmlspecialchars($user['email']); ?></p>
                </div>

                <nav class="space-y-1">
                    <a href="?tab=profile" 
                       class="<?php echo $active_tab === 'profile' ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:bg-gray-100'; ?> 
                              block px-4 py-2 rounded-md font-medium">
                        <i class="fas fa-user-circle mr-2"></i> Informasi Profil
                    </a>
                    <a href="?tab=transactions" 
                       class="<?php echo $active_tab === 'transactions' ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:bg-gray-100'; ?> 
                              block px-4 py-2 rounded-md font-medium">
                        <i class="fas fa-history mr-2"></i> Riwayat Transaksi
                    </a>
                    <a href="?tab=membership" 
                       class="<?php echo $active_tab === 'membership' ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:bg-gray-100'; ?> 
                              block px-4 py-2 rounded-md font-medium">
                        <i class="fas fa-crown mr-2"></i> Keanggotaan
                    </a>
                    <a href="?tab=coupons" 
                       class="<?php echo $active_tab === 'coupons' ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:bg-gray-100'; ?> 
                              block px-4 py-2 rounded-md font-medium">
                        <i class="fas fa-tag mr-2"></i> Kupon Saya
                    </a>
                    <a href="?tab=settings" 
                       class="<?php echo $active_tab === 'settings' ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:bg-gray-100'; ?> 
                              block px-4 py-2 rounded-md font-medium">
                        <i class="fas fa-cog mr-2"></i> Pengaturan
                    </a>
                </nav>
            </div>

            <!-- Konten Utama -->
            <div class="w-full md:w-3/4 lg:w-4/5 bg-white rounded-lg shadow-md p-6">
                <!-- Konten Profil -->
                <?php if ($active_tab === 'profile'): ?>
                    <h1 class="text-2xl font-bold text-gray-800 mb-6 border-b pb-2">Informasi Profil</h1>
                    <form method="post" class="space-y-4">
                        <input type="hidden" name="update_profile" value="1">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap</label>
                                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Nomor Telepon</label>
                                <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>" 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            <div>
                                <label for="birthdate" class="block text-sm font-medium text-gray-700 mb-1">Tanggal Lahir</label>
                                <input type="date" id="birthdate" name="birthdate" value="<?php echo htmlspecialchars($user['birthdate']); ?>" 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            </div>
                        </div>
                        <div>
                            <label for="address" class="block text-sm font-medium text-gray-700 mb-1">Alamat</label>
                            <textarea id="address" name="address" rows="3" 
                                      class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"><?php echo htmlspecialchars($user['address']); ?></textarea>
                        </div>
                        <div class="flex justify-end">
                            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white py-2 px-6 rounded-md">
                                Simpan Perubahan
                            </button>
                        </div>
                    </form>

                <!-- Konten Riwayat Transaksi -->
                <?php elseif ($active_tab === 'transactions'): ?>
                    <h1 class="text-2xl font-bold text-gray-800 mb-6 border-b pb-2">Riwayat Transaksi</h1>
                    <?php if (empty($transactions)): ?>
                        <div class="text-center py-8">
                            <i class="fas fa-history text-4xl text-gray-300 mb-4"></i>
                            <p class="text-gray-500">Anda belum memiliki transaksi</p>
                        </div>
                    <?php else: ?>
                        <div class="space-y-4">
                            <?php foreach ($transactions as $transaction): ?>
                                <div class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50">
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <h3 class="font-medium text-gray-800">Transaksi #<?php echo $transaction['id']; ?></h3>
                                            <p class="text-sm text-gray-500"><?php echo date('d M Y', strtotime($transaction['transaction_date'])); ?></p>
                                        </div>
                                        <span class="px-2 py-1 text-xs rounded-full 
                                                    <?php echo $transaction['status'] === 'Completed' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'; ?>">
                                            <?php echo $transaction['status']; ?>
                                        </span>
                                    </div>
                                    <div class="mt-2 flex justify-between items-center">
                                        <p class="text-gray-600">Total Pembayaran</p>
                                        <p class="font-medium">Rp<?php echo number_format($transaction['amount'], 0, ',', '.'); ?></p>
                                    </div>
                                    <div class="mt-3 flex justify-end">
                                        <button class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                            Lihat Detail <i class="fas fa-chevron-right ml-1"></i>
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                <!-- Konten Keanggotaan -->
                <?php elseif ($active_tab === 'membership'): ?>
                    <h1 class="text-2xl font-bold text-gray-800 mb-6 border-b pb-2">Keanggotaan</h1>
                    <div class="bg-gradient-to-r from-yellow-400 to-yellow-500 rounded-xl p-6 text-center text-white mb-6">
                        <div class="inline-block bg-white text-yellow-600 px-3 py-1 rounded-full text-xs font-bold mb-4">
                            <?php echo $membership['level_name']; ?> MEMBER
                        </div>
                        <h2 class="text-2xl font-bold mb-2">Selamat! Anda Member <?php echo $membership['level_name']; ?></h2>
                        <p class="mb-4">Nikmati diskon <?php echo $membership['discount_percentage']; ?>% untuk semua transaksi</p>
                        <div class="text-4xl font-bold mb-2"><?php echo number_format($user['points']); ?> Poin</div>
                        <p class="text-sm">Total poin yang Anda miliki</p>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                        <div class="border border-gray-200 rounded-lg p-4 text-center">
                            <div class="text-blue-600 text-2xl mb-2">
                                <i class="fas fa-truck"></i>
                            </div>
                            <h3 class="font-medium mb-1">Gratis Ongkir</h3>
                            <p class="text-sm text-gray-600">Gratis ongkir tanpa minimum pembelian</p>
                        </div>
                        <div class="border border-gray-200 rounded-lg p-4 text-center">
                            <div class="text-blue-600 text-2xl mb-2">
                                <i class="fas fa-gift"></i>
                            </div>
                            <h3 class="font-medium mb-1">Diskon Spesial</h3>
                            <p class="text-sm text-gray-600">Diskon hingga <?php echo $membership['discount_percentage']; ?>%</p>
                        </div>
                        <div class="border border-gray-200 rounded-lg p-4 text-center">
                            <div class="text-blue-600 text-2xl mb-2">
                                <i class="fas fa-clock"></i>
                            </div>
                            <h3 class="font-medium mb-1">Akses Early</h3>
                            <p class="text-sm text-gray-600">Akses lebih awal ke produk baru</p>
                        </div>
                    </div>
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h3 class="font-medium mb-2">Tingkatkan Keanggotaan</h3>
                        <p class="text-sm text-gray-600 mb-3">Butuh <?php echo ($membership['min_points'] + 1000) - $user['points']; ?> poin lagi untuk menjadi <?php echo getNextMembershipLevel($membership['level_name']); ?> Member</p>
                        <div class="w-full bg-gray-200 rounded-full h-2.5">
                            <div class="bg-yellow-500 h-2.5 rounded-full" style="width: <?php echo ($user['points'] / ($membership['min_points'] + 1000)) * 100; ?>%"></div>
                        </div>
                    </div>

                <!-- Konten Kupon -->
                <?php elseif ($active_tab === 'coupons'): ?>
                    <h1 class="text-2xl font-bold text-gray-800 mb-6 border-b pb-2">Kupon Saya</h1>
                    <?php if (empty($coupons)): ?>
                        <div class="text-center py-8">
                            <i class="fas fa-tag text-4xl text-gray-300 mb-4"></i>
                            <p class="text-gray-500">Anda belum memiliki kupon aktif</p>
                            <a href="#" class="mt-4 inline-block bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded-md">
                                Dapatkan Kupon
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <?php foreach ($coupons as $coupon): ?>
                                <div class="border-2 border-dashed border-blue-300 rounded-lg p-4 bg-blue-50">
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <h3 class="font-bold text-blue-800 text-lg"><?php echo $coupon['code']; ?></h3>
                                            <p class="text-blue-600 font-medium">
                                                <?php echo $coupon['discount_type'] === 'percentage' ? $coupon['discount_value'] . '%' : 'Rp' . number_format($coupon['discount_value'], 0, ',', '.'); ?> OFF
                                            </p>
                                            <p class="text-sm text-blue-700 mt-1"><?php echo $coupon['description']; ?></p>
                                        </div>
                                        <div class="text-right">
                                            <span class="inline-block bg-blue-200 text-blue-800 text-xs px-2 py-1 rounded">Aktif</span>
                                            <p class="text-xs text-gray-500 mt-1">Berlaku hingga <?php echo date('d M Y', strtotime($coupon['expiry_date'])); ?></p>
                                        </div>
                                    </div>
                                    <div class="mt-4 pt-3 border-t border-blue-200">
                                        <button class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded-md text-sm">
                                            Gunakan Kupon
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="mt-6 p-4 bg-gray-50 rounded-lg">
                            <h3 class="font-medium mb-2">Ingin kupon lebih banyak?</h3>
                            <p class="text-sm text-gray-600 mb-3">Dapatkan kupon dengan menyelesaikan misi atau membeli produk tertentu</p>
                            <button class="bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded-md text-sm">
                                Lihat Misi <i class="fas fa-arrow-right ml-1"></i>
                            </button>
                        </div>
                    <?php endif; ?>

                <!-- Konten Pengaturan -->
                <?php elseif ($active_tab === 'settings'): ?>
                    <h1 class="text-2xl font-bold text-gray-800 mb-6 border-b pb-2">Pengaturan</h1>
                    <form method="post" class="space-y-6">
                        <input type="hidden" name="update_settings" value="1">
                        <div>
                            <h2 class="text-lg font-medium text-gray-800 mb-4">Notifikasi</h2>
                            <div class="space-y-3">
                                <div class="flex items-center">
                                    <input type="checkbox" id="email_notif" name="email_notif" 
                                           <?php echo $user['email_notifications'] ? 'checked' : ''; ?> 
                                           class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                    <label for="email_notif" class="ml-2 block text-sm text-gray-700">Email Notifikasi</label>
                                </div>
                                <div class="flex items-center">
                                    <input type="checkbox" id="sms_notif" name="sms_notif" 
                                           <?php echo $user['sms_notifications'] ? 'checked' : ''; ?> 
                                           class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                    <label for="sms_notif" class="ml-2 block text-sm text-gray-700">SMS Notifikasi</label>
                                </div>
                                <div class="flex items-center">
                                    <input type="checkbox" id="promo_notif" name="promo_notif" 
                                           <?php echo $user['promo_notifications'] ? 'checked' : ''; ?> 
                                           class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                    <label for="promo_notif" class="ml-2 block text-sm text-gray-700">Promo & Penawaran</label>
                                </div>
                            </div>
                        </div>

                        <div>
                            <h2 class="text-lg font-medium text-gray-800 mb-4">Tema</h2>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <label class="border rounded-lg p-4 cursor-pointer hover:border-blue-500 
                                            <?php echo $user['theme_preference'] === 'light' ? 'border-blue-500 bg-blue-50' : 'border-gray-200'; ?>">
                                    <input type="radio" name="theme" value="light" 
                                           <?php echo $user['theme_preference'] === 'light' ? 'checked' : ''; ?> 
                                           class="sr-only">
                                    <div class="flex items-center">
                                        <div class="h-4 w-4 border-2 border-gray-300 rounded-full mr-2 
                                                    <?php echo $user['theme_preference'] === 'light' ? 'border-blue-500 bg-blue-500' : ''; ?>"></div>
                                        <span>Light Mode</span>
                                    </div>
                                </label>
                                <label class="border rounded-lg p-4 cursor-pointer hover:border-blue-500 
                                            <?php echo $user['theme_preference'] === 'dark' ? 'border-blue-500 bg-blue-50' : 'border-gray-200'; ?>">
                                    <input type="radio" name="theme" value="dark" 
                                           <?php echo $user['theme_preference'] === 'dark' ? 'checked' : ''; ?> 
                                           class="sr-only">
                                    <div class="flex items-center">
                                        <div class="h-4 w-4 border-2 border-gray-300 rounded-full mr-2 
                                                    <?php echo $user['theme_preference'] === 'dark' ? 'border-blue-500 bg-blue-500' : ''; ?>"></div>
                                        <span>Dark Mode</span>
                                    </div>
                                </label>
                                <label class="border rounded-lg p-4 cursor-pointer hover:border-blue-500 
                                            <?php echo $user['theme_preference'] === 'system' ? 'border-blue-500 bg-blue-50' : 'border-gray-200'; ?>">
                                    <input type="radio" name="theme" value="system" 
                                           <?php echo $user['theme_preference'] === 'system' ? 'checked' : ''; ?> 
                                           class="sr-only">
                                    <div class="flex items-center">
                                        <div class="h-4 w-4 border-2 border-gray-300 rounded-full mr-2 
                                                    <?php echo $user['theme_preference'] === 'system' ? 'border-blue-500 bg-blue-500' : ''; ?>"></div>
                                        <span>Sesuai Sistem</span>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <div>
                            <h2 class="text-lg font-medium text-gray-800 mb-4">Bahasa</h2>
                            <select name="language" 
                                    class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md">
                                <option value="indonesia" <?php echo $user['language_preference'] === 'indonesia' ? 'selected' : ''; ?>>Bahasa Indonesia</option>
                                <option value="english" <?php echo $user['language_preference'] === 'english' ? 'selected' : ''; ?>>English</option>
                            </select>
                        </div>

                        <div class="flex justify-end">
                            <button type="submit" 
                                    class="bg-blue-600 hover:bg-blue-700 text-white py-2 px-6 rounded-md">
                                Simpan Pengaturan
                            </button>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>

<?php
// Fungsi helper untuk mendapatkan level keanggotaan berikutnya
function getNextMembershipLevel($current_level) {
    $levels = [
        'Bronze' => 'Silver',
        'Silver' => 'Gold',
        'Gold' => 'Platinum',
        'Platinum' => 'Diamond'
    ];
    return $levels[$current_level] ?? 'Premium';
}

// Tutup koneksi database
$conn->close();
?>