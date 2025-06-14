<?php
// Set pelaporan error untuk menampilkan semua error selama pengembangan
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Konfigurasi koneksi database
$hostname = 'localhost';
$username = 'root';
$password = '';
$database = 'uas_ecommerce'; // Sesuai permintaan Anda

// Membuat koneksi menggunakan MySQLi Object-Oriented style
$con = new mysqli($hostname, $username, $password, $database);

// Memeriksa koneksi
if ($con->connect_error) {
    // Menghentikan eksekusi dan menampilkan pesan error jika koneksi gagal
    // Ini lebih informatif daripada die(mysqli_error($con))
    die("Koneksi Gagal: " . $con->connect_error);
}

// Mengatur character set ke utf8mb4 untuk mendukung berbagai karakter
if (!$con->set_charset("utf8mb4")) {
    printf("Error loading character set utf8mb4: %s\n", $con->error);
    exit();
}
?>