<?php
// Memastikan session dimulai, ini penting untuk fitur login dan keranjang
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
// Tidak perlu include connect.php dan common_functions.php di sini
// karena keduanya sudah di-include di dalam header.php untuk efisiensi.
include('./includes/header.php');
?>

<!-- Konten Utama Halaman Depan -->
<div class="container mx-auto px-4 sm:px-6 lg:px-8 py-8 md:py-12">

    <!-- Hero Banner Section -->
    <div class="bg-gray-900 text-white p-8 md:p-12 rounded-lg mb-12 flex flex-col md:flex-row items-center justify-between gap-8 shadow-2xl">
        <div class="text-center md:text-left md:w-1/2">
            <h1 class="text-3xl md:text-5xl font-extrabold mb-4 leading-tight">
                Koleksi Terbaik, <span class="text-blue-400">Gaya Sempurna.</span>
            </h1>
            <p class="text-lg text-gray-300 font-light mb-6">
                Temukan sepatu yang tepat untuk setiap momen. Kualitas premium, desain modern, dan kenyamanan tak tertandingi.
            </p>
            <a href="products.php" class="inline-block bg-blue-600 text-white font-bold py-3 px-8 rounded-lg hover:bg-blue-700 transition-transform transform hover:scale-105 shadow-lg">
                Belanja Sekarang
            </a>
        </div>
        <div class="mt-8 md:mt-0 md:w-1/2 flex justify-center">
            <!-- Ganti dengan URL gambar produk sepatu unggulan Anda. Ukuran ideal ~600x400px -->
            <img src="./assets\images\product\Header.png" alt="Banner Sepatu" class="rounded-lg shadow-xl">
        </div>
    </div>


    <div class="mb-16">
        <div class="flex items-center mb-6">
            <span class="w-4 h-8 bg-blue-600 rounded"></span>
            <h2 class="ml-4 text-xl text-blue-600 font-semibold">Jelajahi Brand</h2>
        </div>
        <h3 class="text-3xl font-bold text-gray-800 mb-8">Brand Sepatu Populer</h3>
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
            <?php
            // Memanggil fungsi baru untuk menampilkan brand di halaman utama.
            // Anda perlu menambahkan logo pada tabel `brands` di database.
            get_brands_for_homepage();
            ?>
        </div>
    </div>

    <!-- Produk Unggulan Section -->
    <div>
        <div class="flex items-center mb-6">
            <span class="w-4 h-8 bg-blue-600 rounded"></span>
            <h2 class="ml-4 text-xl text-blue-600 font-semibold">Produk Pilihan</h2>
        </div>
        <h3 class="text-3xl font-bold text-gray-800 mb-8">Paling Banyak Dicari</h3>
        <!-- Grid untuk menampilkan produk -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8">
            <?php
            // **PERBAIKAN:** Menggunakan nama fungsi yang benar `get_featured_products()`
            get_featured_products(8); // Menampilkan 8 produk unggulan secara acak.
            ?>
        </div>
        <div class="text-center mt-12">
            <a href="products.php" class="bg-gray-800 text-white font-bold py-3 px-10 rounded-lg hover:bg-gray-900 transition-colors">
                Lihat Semua Produk
            </a>
        </div>
    </div>
</div>

<?php
// Menyertakan footer global
include('./includes/footer.php');
?>
