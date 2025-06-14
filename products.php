<?php
include('./includes/connect.php');
include('./functions/common_functions.php');
session_start();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Semua Produk - Langkahku</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" xintegrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>
<body class="bg-gray-100">
    <?php include('./includes/header.php'); ?>
    <div class="container mx-auto px-4 py-8 flex flex-col lg:flex-row gap-8">
        <aside class="w-full lg:w-1/4">
            <!-- Brands -->
            <div class="bg-white p-6 rounded-lg shadow-md mb-8">
                <h3 class="text-xl font-bold text-gray-800 mb-4 border-b pb-2">Brands</h3>
                <ul class="space-y-2">
                    <?php getbrands(); ?>
                </ul>
            </div>
            
            <!-- Categories -->
            <div class="bg-white p-6 rounded-lg shadow-md">
                <h3 class="text-xl font-bold text-gray-800 mb-4 border-b pb-2">Kategori</h3>
                <ul class="space-y-2">
                    <?php getcategories(); ?>
                </ul>
            </div>
        </aside>

        <!-- Konten Utama Produk -->
        <main class="w-full lg:w-3/4">
            <h2 class="text-3xl font-bold text-gray-800 mb-8">Semua Produk</h2>

            <!-- Grid untuk menampilkan produk -->
            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-6">
                <?php
                // Memanggil fungsi yang benar untuk menampilkan semua produk
                get_all_products();
                get_unique_categories();
                get_unique_brands();
                ?>
            </div>
        </main>
    </div>

    <!-- Footer -->
    <?php include('./includes/footer.php'); ?>

</body>
</html>