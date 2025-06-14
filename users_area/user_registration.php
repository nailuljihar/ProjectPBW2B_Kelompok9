<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
// Menggunakan require_once untuk memastikan file penting ada, jika tidak, skrip akan berhenti.
require_once('../includes/connect.php');
require_once('../functions/common_functions.php');

$errors = [];

// Memproses form hanya jika metode request adalah POST
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['user_register'])) {
    // Membersihkan dan mengambil data dari form
    $username = trim($_POST['user_username']);
    $email = trim($_POST['user_email']);
    $password = $_POST['user_password'];
    $conf_password = $_POST['conf_user_password'];
    $address = trim($_POST['user_address']);
    $mobile = trim($_POST['user_mobile']);
    $image = $_FILES['user_image'];
    $ip = getIPAddress();

    // --- Validasi Input ---
    if (empty($username)) $errors[] = "Username tidak boleh kosong.";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Format email tidak valid.";
    if (strlen($password) < 6) $errors[] = "Password minimal 6 karakter.";
    if ($password !== $conf_password) $errors[] = "Konfirmasi password tidak cocok.";
    if (empty($address)) $errors[] = "Alamat tidak boleh kosong.";
    if (!preg_match('/^[0-9]{10,15}$/', $mobile)) $errors[] = "Nomor handphone tidak valid.";
    
    // --- Validasi Gambar ---
    if ($image['error'] !== UPLOAD_ERR_OK) {
        $errors[] = "Gagal mengunggah gambar. Pastikan Anda telah memilih file.";
    } else {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($image['type'], $allowed_types)) {
            $errors[] = "Format gambar harus JPG, PNG, atau GIF.";
        }
        if ($image['size'] > 2000000) { // Batas 2MB
            $errors[] = "Ukuran gambar maksimal 2MB.";
        }
    }

    // --- Cek Duplikasi Data (hanya jika tidak ada error sebelumnya) ---
    if (empty($errors)) {
        $stmt = $con->prepare("SELECT username, user_email FROM `user_table` WHERE username = ? OR user_email = ?");
        
        // **PERBAIKAN KUNCI 1: Menambahkan pengecekan error pada prepare()**
        if ($stmt === false) {
            // Menampilkan error SQL yang sebenarnya untuk debugging
            die('Gagal mempersiapkan query: ' . htmlspecialchars($con->error));
        }
        
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $existing_user = $result->fetch_assoc();
            if ($existing_user['username'] === $username) $errors[] = "Username sudah terdaftar.";
            if ($existing_user['user_email'] === $email) $errors[] = "Email sudah terdaftar.";
        }
        $stmt->close();
    }

    // --- Proses Registrasi (jika semua validasi lolos) ---
    if (empty($errors)) {
        $hash_password = password_hash($password, PASSWORD_DEFAULT);
        // Membuat nama file yang unik untuk mencegah tumpang tindih
        $image_name = time() . '_' . uniqid() . '_' . basename($image['name']);
        $target_path = "./user_images/" . $image_name;

        if (move_uploaded_file($image['tmp_name'], $target_path)) {
            $insert_stmt = $con->prepare("INSERT INTO `user_table` (username, user_email, user_password, user_image, user_ip, user_address, user_mobile) VALUES (?, ?, ?, ?, ?, ?, ?)");
            
            // **PERBAIKAN KUNCI 2: Pengecekan error juga di sini**
            if ($insert_stmt === false) {
                 die('Gagal mempersiapkan query insert: ' . htmlspecialchars($con->error));
            }

            $insert_stmt->bind_param("sssssss", $username, $email, $hash_password, $image_name, $ip, $address, $mobile);
            
            if ($insert_stmt->execute()) {
                // Login otomatis setelah registrasi
                $_SESSION['username'] = $username;
                $_SESSION['user_id'] = $insert_stmt->insert_id;
                echo "<script>alert('Registrasi berhasil! Anda sekarang sudah login.');</script>";
                echo "<script>window.open('../index.php','_self');</script>";
                exit();
            } else {
                $errors[] = "Registrasi gagal. Silakan coba lagi. Error: " . htmlspecialchars($insert_stmt->error);
            }
            $insert_stmt->close();
        } else {
            $errors[] = "Gagal menyimpan gambar ke server.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun - LangkahKu</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen flex items-center justify-center py-12 px-4">
        <div class="w-full max-w-4xl flex bg-white rounded-xl shadow-2xl overflow-hidden">
             <!-- Bagian Form -->
            <div class="w-full md:w-1/2 p-8 md:p-12">
                 <a href="../index.php" class="text-xl font-bold text-gray-500 hover:text-gray-800 transition-colors"><i class="fas fa-arrow-left"></i> Kembali ke Home</a>
                <h2 class="text-4xl font-bold text-gray-800 mt-6">Buat Akun Baru</h2>
                <p class="text-gray-600 mt-2">Daftar dan mulailah perjalanan gaya Anda bersama kami.</p>

                <form action="" method="post" enctype="multipart/form-data" class="mt-8 space-y-4">
                     <!-- Menampilkan pesan error jika ada -->
                    <?php if(!empty($errors)): ?>
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg relative" role="alert">
                            <strong class="font-bold">Oops! Terjadi kesalahan:</strong>
                            <ul class="mt-2 list-disc list-inside text-sm">
                                <?php foreach($errors as $error): ?>
                                    <li><?php echo htmlspecialchars($error); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <input type="text" placeholder="Username" required name="user_username" class="block w-full px-4 py-3 bg-gray-50 border border-gray-300 rounded-lg focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    <input type="email" placeholder="Email" required name="user_email" class="block w-full px-4 py-3 bg-gray-50 border border-gray-300 rounded-lg focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    <input type="password" placeholder="Password (min. 6 karakter)" required name="user_password" class="block w-full px-4 py-3 bg-gray-50 border border-gray-300 rounded-lg focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    <input type="password" placeholder="Konfirmasi Password" required name="conf_user_password" class="block w-full px-4 py-3 bg-gray-50 border border-gray-300 rounded-lg focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    <input type="text" placeholder="Alamat Lengkap" required name="user_address" class="block w-full px-4 py-3 bg-gray-50 border border-gray-300 rounded-lg focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    <input type="tel" placeholder="Nomor Handphone (Contoh: 08123456789)" required name="user_mobile" class="block w-full px-4 py-3 bg-gray-50 border border-gray-300 rounded-lg focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    <div>
                        <label for="user_image" class="block text-sm font-medium text-gray-700">Foto Profil</label>
                        <input type="file" required name="user_image" id="user_image" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                    </div>
                    
                    <button type="submit" name="user_register" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                        Daftar
                    </button>
                </form>

                <p class="mt-6 text-center text-sm text-gray-600">
                    Sudah punya akun?
                    <a href="user_login.php" class="font-medium text-blue-600 hover:text-blue-500">
                        Login di sini
                    </a>
                </p>
            </div>
             <!-- Bagian Gambar -->
            <div class="hidden md:block md:w-1/2 bg-cover bg-center" style="background-image: url('https://placehold.co/600x800/dbeafe/1e3a8a?text=Join+Us&font=source-sans-pro');">
                <!-- Ukuran gambar ideal di sini adalah 600x800 pixels -->
            </div>
        </div>
    </div>
</body>
</html>