<?php
// Memulai session dan menyertakan file koneksi dan fungsi
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include('../includes/connect.php');
include('../functions/common_functions.php');

$login_error = '';

// Logika untuk memproses form login
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['user_login'])) {
    $user_username = $_POST['user_username'];
    $user_password = $_POST['user_password'];

    // Gunakan prepared statement untuk keamanan
    $select_query = "SELECT * FROM `user_table` WHERE username = ?";
    $stmt = $con->prepare($select_query);
    
    if ($stmt === false) {
        $login_error = "Terjadi kesalahan pada server. Silakan coba lagi nanti.";
    } else {
        $stmt->bind_param("s", $user_username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row_data = $result->fetch_assoc();
            // Verifikasi password
            if (password_verify($user_password, $row_data['user_password'])) {
                // Set session
                $_SESSION['username'] = $row_data['username'];
                $_SESSION['user_id'] = $row_data['user_id'];

                // Cek keranjang belanja pengguna
                $user_ip = getIPAddress();
                $cart_check_query = "SELECT * FROM `cart_details` WHERE ip_address = ?";
                $stmt_cart = $con->prepare($cart_check_query);
                $stmt_cart->bind_param("s", $user_ip);
                $stmt_cart->execute();
                $stmt_cart->store_result();
                $cart_item_count = $stmt_cart->num_rows;
                $stmt_cart->close();

                echo "<script>alert('Login berhasil!');</script>";
                if ($cart_item_count > 0) {
                    echo "<script>window.open('checkout.php','_self');</script>";
                } else {
                    echo "<script>window.open('profile.php','_self');</script>";
                }
                exit();
            } else {
                $login_error = "Nama pengguna atau kata sandi salah.";
            }
        } else {
            $login_error = "Nama pengguna atau kata sandi salah.";
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - LangkahKu</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen flex items-center justify-center">
        <div class="w-full max-w-4xl flex bg-white rounded-xl shadow-2xl overflow-hidden">
            <!-- Bagian Gambar -->
            <div class="hidden md:block md:w-1/2 bg-cover bg-center" style="background-image: url('https://placehold.co/600x800/e0e7ff/3730a3?text=LangkahKu&font=source-sans-pro');">
                <!-- Ukuran gambar ideal di sini adalah 600x800 pixels -->
            </div>

            <!-- Bagian Form -->
            <div class="w-full md:w-1/2 p-8 md:p-12">
                <a href="../index.php" class="text-xl font-bold text-gray-500 hover:text-gray-800 transition-colors"><i class="fas fa-arrow-left"></i> Kembali ke Home</a>
                <h2 class="text-4xl font-bold text-gray-800 mt-6">Selamat Datang Kembali</h2>
                <p class="text-gray-600 mt-2">Silakan masukkan detail Anda untuk melanjutkan.</p>

                <form action="" method="post" class="mt-8 space-y-6">
                    <!-- Menampilkan pesan error jika ada -->
                    <?php if(!empty($login_error)): ?>
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg relative" role="alert">
                            <span class="block sm:inline"><?php echo $login_error; ?></span>
                        </div>
                    <?php endif; ?>

                    <div>
                        <label for="user_username" class="text-sm font-medium text-gray-700">Username</label>
                        <input type="text" placeholder="Masukkan username Anda" autocomplete="username" required name="user_username" id="user_username" class="mt-1 block w-full px-4 py-3 bg-gray-50 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    </div>
                    <div>
                        <label for="user_password" class="text-sm font-medium text-gray-700">Password</label>
                        <input type="password" placeholder="Masukkan password Anda" autocomplete="current-password" required name="user_password" id="user_password" class="mt-1 block w-full px-4 py-3 bg-gray-50 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    </div>
                    
                    <div>
                        <button type="submit" name="user_login" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                            Login
                        </button>
                    </div>
                </form>

                <p class="mt-6 text-center text-sm text-gray-600">
                    Belum punya akun?
                    <a href="user_registration.php" class="font-medium text-blue-600 hover:text-blue-500">
                        Daftar sekarang
                    </a>
                </p>
            </div>
        </div>
    </div>
</body>
</html>