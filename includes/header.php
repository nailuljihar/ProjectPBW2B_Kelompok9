<?php
// Memastikan session dimulai di awal
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// **PERBAIKAN KUNCI: Mendefinisikan Base URL untuk Path Absolut**
// Ini membuat semua link navigasi konsisten di seluruh situs.
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$host = $_SERVER['HTTP_HOST'];
// Sesuaikan '/uas-ecommerce' dengan nama folder proyek Anda jika berbeda.
$project_folder = '/uas-ecommerce'; 
$base_url = $protocol . "://" . $host . $project_folder;

// Menggunakan require_once dengan path absolut dari root dokumen
require_once($_SERVER['DOCUMENT_ROOT'] . $project_folder . '/includes/connect.php');
require_once($_SERVER['DOCUMENT_ROOT'] . $project_folder . '/functions/common_functions.php');

// Memproses penambahan item ke keranjang sebelum HTML di-render
cart();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Menggunakan CDN Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Menggunakan CDN Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>
    <title>LangkahKu - Toko Sepatu Online</title>
    <style>
        /* Style untuk counter pada ikon keranjang */
        .cart-icon sup {
            background-color: #ef4444; /* red-500 */
            color: white;
            border-radius: 9999px;
            padding: 0.1em 0.45em;
            font-size: 0.7rem;
            position: absolute;
            top: -5px;
            right: -10px;
            line-height: 1;
        }
    </style>
</head>
<body class="bg-gray-50 font-sans">
    <!-- Promo Banner -->
    <div class="bg-black text-white text-center py-2 px-4 text-sm font-medium">
        Gratis Ongkir Untuk Pembelian Di Atas Rp 500.000!
    </div>

    <!-- Navbar Utama -->
    <nav class="bg-white shadow-md sticky top-0 z-50">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <!-- Logo -->
                <div class="flex-shrink-0">
                    <a href="<?php echo $base_url; ?>/index.php" class="text-3xl font-extrabold text-gray-900 tracking-tight">LangkahKu.</a>
                </div>

                <!-- Link Navigasi (Desktop) -->
                <div class="hidden md:flex md:items-center md:space-x-8">
                    <a href="<?php echo $base_url; ?>/index.php" class="text-gray-600 hover:text-blue-600 transition duration-150 ease-in-out font-medium">Home</a>
                    <a href="<?php echo $base_url; ?>/products.php" class="text-gray-600 hover:text-blue-600 transition duration-150 ease-in-out font-medium">Semua Produk</a>
                    <?php
                    if (isset($_SESSION['username'])) {
                        echo "<a href='{$base_url}/users_area/profile.php' class='text-gray-600 hover:text-blue-600 transition duration-150 ease-in-out font-medium'>Akun Saya</a>";
                    } else {
                        echo "<a href='{$base_url}/users_area/user_registration.php' class='text-gray-600 hover:text-blue-600 transition duration-150 ease-in-out font-medium'>Daftar</a>";
                    }
                    ?>
                </div>

                <!-- Ikon, Pencarian, dan Login -->
                <div class="flex items-center space-x-4">
                    <form action="<?php echo $base_url; ?>/search_product.php" method="get" class="hidden lg:flex items-center">
                        <input type="search" name="search_data" placeholder="Cari sepatu..." class="bg-gray-100 border-gray-200 border px-3 py-1.5 rounded-l-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm w-48">
                        <button type="submit" name="search_data_product" class="bg-blue-600 text-white px-3 py-1.5 rounded-r-md hover:bg-blue-700 transition-colors"><i class="fa fa-search"></i></button>
                    </form>

                    <a href="<?php echo $base_url; ?>/cart.php" class="relative cart-icon text-gray-600 hover:text-blue-600">
                        <i class="fas fa-shopping-cart fa-lg"></i>
                        <sup><?php cart_item(); ?></sup>
                    </a>
                    
                    <div class="hidden sm:flex items-center space-x-2">
                         <?php
                        if (!isset($_SESSION['username'])) {
                            echo "<a href='{$base_url}/users_area/user_login.php' class='bg-blue-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-blue-700 transition-colors'>Login</a>";
                        } else {
                            echo "<div class='text-sm'><span>Halo, <strong>" . htmlspecialchars($_SESSION['username']) . "</strong></span> <a href='{$base_url}/users_area/logout.php' class='text-red-500 hover:underline ml-2'>Logout</a></div>";
                        }
                        ?>
                    </div>
                     <!-- Tombol Menu Mobile -->
                     <div class="md:hidden">
                        <button id="mobile-menu-button" class="text-gray-500 hover:text-gray-700 focus:outline-none">
                            <i class="fas fa-bars fa-lg"></i>
                        </button>
                    </div>
                </div>
            </div>
             <!-- Menu Mobile (Tersembunyi secara default) -->
            <div id="mobile-menu" class="hidden md:hidden pb-4">
                <a href="<?php echo $base_url; ?>/index.php" class="block py-2 px-4 text-sm text-gray-700 hover:bg-gray-100 rounded">Home</a>
                <a href="<?php echo $base_url; ?>/products.php" class="block py-2 px-4 text-sm text-gray-700 hover:bg-gray-100 rounded">Semua Produk</a>
                 <?php
                    if (isset($_SESSION['username'])) {
                        echo "<a href='{$base_url}/users_area/profile.php' class='block py-2 px-4 text-sm text-gray-700 hover:bg-gray-100 rounded'>Akun Saya</a>";
                        echo "<a href='{$base_url}/users_area/logout.php' class='block py-2 px-4 text-sm text-red-600 hover:bg-gray-100 rounded'>Logout</a>";
                    } else {
                        echo "<a href='{$base_url}/users_area/user_registration.php' class='block py-2 px-4 text-sm text-gray-700 hover:bg-gray-100 rounded'>Daftar</a>";
                         echo "<a href='{$base_url}/users_area/user_login.php' class='block py-2 px-4 text-sm text-gray-700 hover:bg-gray-100 rounded'>Login</a>";
                    }
                ?>
                 <form action="<?php echo $base_url; ?>/search_product.php" method="get" class="flex items-center mt-4">
                    <input type="search" name="search_data" placeholder="Cari sepatu..." class="bg-gray-100 border-gray-200 border px-3 py-1.5 rounded-l-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm w-full">
                    <button type="submit" name="search_data_product" class="bg-blue-600 text-white px-3 py-1.5 rounded-r-md hover:bg-blue-700 transition-colors"><i class="fa fa-search"></i></button>
                </form>
            </div>
        </div>
    </nav>

    <script>
        // Script untuk toggle menu mobile
        document.getElementById('mobile-menu-button').addEventListener('click', function() {
            var menu = document.getElementById('mobile-menu');
            menu.classList.toggle('hidden');
        });
    </script>
<!-- AWAL KONTEN HALAMAN -->
<main class="min-h-screen">