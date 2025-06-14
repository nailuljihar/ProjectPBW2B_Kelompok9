<?php
// Memastikan sesi dan koneksi siap
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$project_root = $_SERVER['DOCUMENT_ROOT'] . '/uas-ecommerce';
require_once($project_root . "/includes/connect.php");
require_once($project_root . "/functions/common_functions.php");

// Jika pengguna belum login, alihkan ke halaman login
if (!isset($_SESSION['username'])) {
    header('Location: user_login.php');
    exit();
}

// Mengambil data pengguna untuk ditampilkan di sidebar
$username = $_SESSION['username'];
$user_id = $_SESSION['user_id']; // Asumsi user_id disimpan saat login

$select_user_stmt = $con->prepare("SELECT * FROM `user_table` WHERE user_id = ?");
$select_user_stmt->bind_param("i", $user_id);
$select_user_stmt->execute();
$user_result = $select_user_stmt->get_result();
$user_data = $user_result->fetch_assoc();
$user_image = htmlspecialchars($user_data['user_image']);
$select_user_stmt->close();

// **PERBAIKAN KUNCI: Gunakan path absolut juga di sini**
include($project_root . '/includes/header.php');
?>

<div class="container mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="flex flex-col md:flex-row gap-8">
        <!-- Sidebar Profil -->
        <aside class="w-full md:w-1/4 lg:w-1/5 flex-shrink-0">
            <div class="bg-white p-4 rounded-lg shadow-md">
                <div class="text-center mb-4">
                    <img src="./user_images/<?php echo $user_image; ?>" alt="Foto Profil <?php echo htmlspecialchars($username); ?>" class="w-24 h-24 rounded-full mx-auto object-cover border-4 border-blue-200">
                    <h3 class="mt-2 text-lg font-bold text-gray-800"><?php echo htmlspecialchars($username); ?></h3>
                </div>
                <nav class="space-y-1">
                    <a href="profile.php" class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-md transition-colors <?php if(!isset($_GET['my_orders']) && !isset($_GET['edit_account']) && !isset($_GET['delete_account'])) echo 'bg-blue-100 text-blue-700 font-semibold'; ?>">
                        <i class="fas fa-tachometer-alt w-5 mr-3"></i> Dashboard
                    </a>
                    <a href="profile.php?my_orders" class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-md transition-colors <?php if(isset($_GET['my_orders'])) echo 'bg-blue-100 text-blue-700 font-semibold'; ?>">
                        <i class="fas fa-box w-5 mr-3"></i> Pesanan Saya
                    </a>
                    <a href="profile.php?edit_account" class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-md transition-colors <?php if(isset($_GET['edit_account'])) echo 'bg-blue-100 text-blue-700 font-semibold'; ?>">
                        <i class="fas fa-user-edit w-5 mr-3"></i> Edit Akun
                    </a>
                    <a href="profile.php?delete_account" class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-md transition-colors <?php if(isset($_GET['delete_account'])) echo 'bg-blue-100 text-blue-700 font-semibold'; ?>">
                        <i class="fas fa-trash-alt w-5 mr-3"></i> Hapus Akun
                    </a>
                    <div class="pt-2 border-t mt-2">
                        <a href="logout.php" class="flex items-center px-4 py-2 text-red-600 hover:bg-red-50 rounded-md transition-colors">
                            <i class="fas fa-sign-out-alt w-5 mr-3"></i> Logout
                        </a>
                    </div>
                </nav>
            </div>
        </aside>

        <!-- Konten Utama -->
        <main class="w-full bg-white p-6 md:p-8 rounded-lg shadow-md">
            <?php
            // Memuat konten secara dinamis berdasarkan URL
            // Path include di sini sudah benar karena relatif terhadap profile.php
            if (isset($_GET['my_orders'])) {
                include('user_orders.php');
            } elseif (isset($_GET['edit_account'])) {
                include('edit_account.php');
            } elseif (isset($_GET['delete_account'])) {
                include('delete_account.php');
            } else {
                // Tampilan default dashboard profil
                get_user_order_details();
            }
            ?>
        </main>
    </div>
</div>

<?php
include($project_root . '/includes/footer.php');
?>