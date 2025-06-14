<?php
// Menggunakan require_once dengan path absolut
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
$project_root = $_SERVER['DOCUMENT_ROOT'] . '/uas-ecommerce';
require_once($project_root . '/includes/connect.php');
require_once($project_root . '/functions/common_functions.php');

// Validasi akses
if (!isset($_GET['user_id']) || !isset($_GET['payment_method']) || !isset($_SESSION['user_id']) || !isset($_SESSION['selected_for_checkout'])) {
    // Alihkan ke halaman utama jika akses tidak sah
    header('Location: ../index.php');
    exit();
}

$user_id = (int)$_SESSION['user_id'];
$payment_method = htmlspecialchars($_GET['payment_method']);
$selected_items = $_SESSION['selected_for_checkout'];

// Pastikan item yang dipilih tidak kosong
if (empty($selected_items)) {
    echo "<script>alert('Tidak ada item yang dipilih untuk checkout.'); window.location.href='../cart.php';</script>";
    exit();
}

// Menghitung total harga dan jumlah item HANYA dari item yang dipilih
$placeholders = implode(',', array_fill(0, count($selected_items), '?'));
$total_price = 0;
$count_products = count($selected_items);

$ip_address = getIPAddress();
$types = 's' . str_repeat('i', $count_products);
$params = array_merge([$ip_address], $selected_items);

$cart_query = "
    SELECT p.product_price, c.quantity 
    FROM `cart_details` c
    JOIN `products` p ON c.product_id = p.product_id
    WHERE c.ip_address = ? AND c.product_id IN ($placeholders)";
$stmt = $con->prepare($cart_query);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $total_price += ($row['product_price'] * $row['quantity']);
}
$stmt->close();

if ($total_price <= 0) {
    echo "<script>alert('Terjadi kesalahan saat menghitung total.'); window.location.href='../cart.php';</script>";
    exit();
}

// Menentukan status pesanan
$status = ($payment_method == 'cod') ? 'COD - Belum Dibayar' : 'pending';
$invoice_number = mt_rand();

// Menyimpan pesanan ke database
$insert_orders_stmt = $con->prepare("INSERT INTO `user_orders` (user_id, amount_due, invoice_number, total_products, order_date, order_status) VALUES (?, ?, ?, ?, NOW(), ?)");
$insert_orders_stmt->bind_param("idsis", $user_id, $total_price, $invoice_number, $count_products, $status);

if ($insert_orders_stmt->execute()) {
    // Menghapus item yang sudah di-checkout dari keranjang
    $delete_cart_stmt = $con->prepare("DELETE FROM `cart_details` WHERE ip_address = ? AND product_id IN ($placeholders)");
    $delete_cart_stmt->bind_param($types, ...$params);
    $delete_cart_stmt->execute();
    $delete_cart_stmt->close();

    // Membersihkan session checkout
    unset($_SESSION['selected_for_checkout']);

    if ($payment_method == 'cod') {
        echo "<script>alert('Pesanan COD Anda berhasil dibuat.'); window.location.href='profile.php?my_orders';</script>";
    } else {
        echo "<script>alert('Pesanan berhasil dibuat. Silakan lanjutkan ke konfirmasi pembayaran.'); window.location.href='confirm_payment.php?invoice_number=$invoice_number';</script>";
    }
} else {
    echo "<script>alert('Gagal membuat pesanan. Silakan coba lagi.'); window.location.href='../cart.php';</script>";
}
$insert_orders_stmt->close();
?>