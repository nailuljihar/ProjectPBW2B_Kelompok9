<?php
// Menggunakan require_once untuk keamanan dan konsistensi
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
// Definisikan path absolut
$project_root = $_SERVER['DOCUMENT_ROOT'] . '/uas-ecommerce';
require_once($project_root . "/includes/connect.php");
require_once($project_root . "/functions/common_functions.php");

// Logika baru untuk menangani item yang dipilih dari keranjang
if (isset($_POST['checkout_selected']) && !empty($_POST['selected_items'])) {
    // Simpan item yang dipilih ke dalam session untuk digunakan di halaman pembayaran
    $_SESSION['selected_for_checkout'] = $_POST['selected_items'];
} elseif (!isset($_SESSION['selected_for_checkout']) || empty($_SESSION['selected_for_checkout'])) {
    // Jika tidak ada item yang dipilih, coba ambil semua dari keranjang
    $ip_address = getIPAddress();
    $get_cart_items_stmt = $con->prepare("SELECT product_id FROM `cart_details` WHERE ip_address = ?");
    $get_cart_items_stmt->bind_param("s", $ip_address);
    $get_cart_items_stmt->execute();
    $result = $get_cart_items_stmt->get_result();
    $all_cart_ids = [];
    while($row = $result->fetch_assoc()){
        $all_cart_ids[] = $row['product_id'];
    }
    $get_cart_items_stmt->close();
    
    if(empty($all_cart_ids)){
        echo "<script>alert('Keranjang Anda kosong. Silakan tambahkan produk terlebih dahulu.'); window.location.href='../products.php';</script>";
        exit();
    }
    $_SESSION['selected_for_checkout'] = $all_cart_ids;
}

// Termasuk header
include($project_root . '/includes/header.php');
?>

<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <?php
        // Jika pengguna belum login, tampilkan form login
        if (!isset($_SESSION['username'])) {
            include('user_login.php');
        } else {
            // Jika sudah login, tampilkan opsi pembayaran
            include('payment.php');
        }
        ?>
    </div>
</div>