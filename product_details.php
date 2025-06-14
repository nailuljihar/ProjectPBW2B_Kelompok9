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
    <title>Detail Produk - Langkahku</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" xintegrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        /* Custom CSS untuk Bintang Rating Interaktif */
        .rating-css {
            display: flex;
            flex-direction: row-reverse;
            justify-content: start;
        }
        .rating-css input {
            display: none;
        }
        .rating-css label {
            font-size: 2.5rem;
            color: #ccc;
            cursor: pointer;
            transition: color 0.2s;
        }
        .rating-css input:checked ~ label,
        .rating-css label:hover,
        .rating-css label:hover ~ label {
            color: #ffc107;
        }
    </style>
</head>

<body class="bg-gray-50">
    <!-- Navbar -->
    <?php include('./includes/header.php'); ?>

    <!-- Konten Detail Produk -->
    <div class="container mx-auto px-4 py-12">
        <div class="bg-white p-8 rounded-lg shadow-lg">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
                <?php
                if (isset($_GET['product_id'])) {
                    $product_id = (int)$_GET['product_id'];
                    $select_query = "SELECT * FROM `products` WHERE product_id = ?";
                    $stmt = $con->prepare($select_query);
                    $stmt->bind_param("i", $product_id);
                    $stmt->execute();
                    $result_query = $stmt->get_result();
                    $row = $result_query->fetch_assoc();

                    if ($row) {
                        $product_title = htmlspecialchars($row['product_title']);
                        $product_description = htmlspecialchars($row['product_description']);
                        $product_price = $row['product_price'];
                        
                        // --- PERBAIKAN FINAL NAMA KOLOM GAMBAR ---
                        $image_one = isset($row['product_image_one']) && !empty($row['product_image_one']) ? htmlspecialchars($row['product_image_one']) : 'placeholder.png';
                        $image_two = isset($row['product_image_two']) && !empty($row['product_image_two']) ? htmlspecialchars($row['product_image_two']) : 'placeholder.png';
                        $image_three = isset($row['product_image_three']) && !empty($row['product_image_three']) ? htmlspecialchars($row['product_image_three']) : 'placeholder.png';

                        // Fungsi untuk membuat path gambar atau placeholder
                        function get_image_path($img_name, $title) {
                            $path = './admin/product_images/' . $img_name;
                            if ($img_name === 'placeholder.png' || !file_exists($path)) {
                                return "https://placehold.co/600x600/f0f0f0/999999?text=" . urlencode($title);
                            }
                            return $path;
                        }
                        
                        $main_image_src = get_image_path($image_one, $product_title);
                        $thumb1_src = get_image_path($image_one, $product_title);
                        $thumb2_src = get_image_path($image_two, $product_title);
                        $thumb3_src = get_image_path($image_three, $product_title);

                        // Bagian Kiri: Galeri Gambar
                        echo "
                        <div>
                            <div class='mb-4'>
                                <img src='$main_image_src' alt='$product_title' id='mainProductImage' class='w-full h-auto max-h-[500px] object-contain rounded-lg border border-gray-200 p-4'>
                            </div>
                            <div class='grid grid-cols-3 gap-4'>
                                <img src='$thumb1_src' class='w-full h-24 object-contain border-2 border-blue-500 rounded-lg p-2 cursor-pointer' onclick=\"document.getElementById('mainProductImage').src=this.src\">
                                <img src='$thumb2_src' class='w-full h-24 object-contain border border-gray-200 hover:border-blue-500 rounded-lg p-2 cursor-pointer' onclick=\"document.getElementById('mainProductImage').src=this.src\">
                                <img src='$thumb3_src' class='w-full h-24 object-contain border border-gray-200 hover:border-blue-500 rounded-lg p-2 cursor-pointer' onclick=\"document.getElementById('mainProductImage').src=this.src\">
                            </div>
                        </div>";

                        // Bagian Kanan: Detail Info
                        echo "
                        <div>
                            <h1 class='text-3xl lg:text-4xl font-bold text-gray-800 mb-3'>$product_title</h1>
                            <div class='mb-4'>
                                " . get_average_rating($product_id) . "
                            </div>
                            <p class='text-gray-600 mb-6 leading-relaxed'>$product_description</p>
                            <p class='text-4xl font-bold text-red-600 mb-6'>Rp " . number_format($product_price, 0, ',', '.') . "</p>
                            <div class='flex items-center gap-4'>
                                <a href='index.php?add_to_cart=$product_id' class='bg-blue-500 hover:bg-blue-600 text-white font-bold py-3 px-8 rounded-lg transition-colors'>
                                    <i class='fas fa-cart-shopping mr-2'></i>Tambah ke Keranjang
                                </a>
                                <a href='index.php' class='bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-3 px-8 rounded-lg transition-colors'>
                                    Kembali
                                </a>
                            </div>
                        </div>";
                    } else {
                         echo "<p class='col-span-2 text-center text-red-500'>Produk tidak ditemukan.</p>";
                    }
                    $stmt->close();
                } else {
                    echo "<p class='col-span-2 text-center text-red-500'>ID Produk tidak valid.</p>";
                }
                ?>
            </div>

            <!-- Bagian Rating dan Ulasan -->
            <div class="mt-16 pt-8 border-t border-gray-200">
                <h3 class="text-2xl font-bold text-gray-800 mb-6">Rating dan Ulasan</h3>

                <!-- Form untuk Memberikan Rating -->
                <div class="bg-gray-100 p-6 rounded-lg mb-8">
                     <?php if (isset($_SESSION['user_id'])): ?>
                        <h4 class="text-xl font-semibold mb-4">Beri Ulasan Anda</h4>
                        <form id="ratingForm">
                            <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
                            <div class="rating-css mb-3">
                                <input type="radio" name="rating" id="rating5" value="5" required><label for="rating5" class="fa fa-star"></label>
                                <input type="radio" name="rating" id="rating4" value="4"><label for="rating4" class="fa fa-star"></label>
                                <input type="radio" name="rating" id="rating3" value="3"><label for="rating3" class="fa fa-star"></label>
                                <input type="radio" name="rating" id="rating2" value="2"><label for="rating2" class="fa fa-star"></label>
                                <input type="radio" name="rating" id="rating1" value="1"><label for="rating1" class="fa fa-star"></label>
                            </div>
                            <div class="mb-4">
                                <input type="text" class="w-full p-3 border border-gray-300 rounded-lg" id="review_title" name="review_title" placeholder="Judul Ulasan Anda (Contoh: Produk hebat!)">
                            </div>
                            <div class="mb-4">
                                <textarea class="w-full p-3 border border-gray-300 rounded-lg" id="review" name="review" rows="4" placeholder="Tuliskan pengalaman Anda menggunakan produk ini..."></textarea>
                            </div>
                            <button type="submit" class="bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-6 rounded-lg transition-colors">Kirim Ulasan</button>
                        </form>
                        <div id="form-message" class="mt-4"></div>
                     <?php else: ?>
                        <div class="bg-blue-100 border-l-4 border-blue-500 text-blue-700 p-4 rounded-md">
                           <p>Silakan <a href="./users_area/user_login.php" class="font-bold hover:underline">login</a> untuk memberikan ulasan.</p>
                        </div>
                     <?php endif; ?>
                </div>

                <!-- Daftar Ulasan Pelanggan -->
                <div>
                    <h4 class="text-xl font-semibold mb-4">Ulasan dari Pelanggan Lain</h4>
                    <div id="reviews-container" class="space-y-6">
                        <?php
                            if(isset($product_id)){
                                $get_reviews_query = "SELECT r.*, u.username FROM `ratings` r JOIN `user_table` u ON r.user_id = u.user_id WHERE r.product_id = ? ORDER BY r.created_at DESC";
                                $stmt_reviews = $con->prepare($get_reviews_query);
                                $stmt_reviews->bind_param("i", $product_id);
                                $stmt_reviews->execute();
                                $result_reviews = $stmt_reviews->get_result();

                                if ($result_reviews->num_rows > 0) {
                                    while($review = $result_reviews->fetch_assoc()) {
                                        echo "<div class='border-b border-gray-200 pb-6'>
                                                <div class='flex items-center mb-2'>
                                                    <span class='font-bold text-gray-800'>" . htmlspecialchars($review['username']) . "</span>
                                                    <span class='text-gray-500 text-sm ml-auto'>" . date('d M Y', strtotime($review['created_at'])) . "</span>
                                                </div>
                                                <div class='mb-2'>" . display_star_rating($review['rating']) . "</div>
                                                <h6 class='font-semibold text-lg mt-1'>" . htmlspecialchars($review['review_title']) . "</h6>
                                                <p class='text-gray-600'>" . nl2br(htmlspecialchars($review['review'])) . "</p>
                                              </div>";
                                    }
                                } else {
                                    echo "<p class='text-gray-500'>Belum ada ulasan untuk produk ini. Jadilah yang pertama!</p>";
                                }
                                $stmt_reviews->close();
                            }
                        ?>
                     </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <?php include('./includes/footer.php'); ?>

    <!-- Script untuk Submit Rating -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const ratingForm = document.getElementById('ratingForm');
            if (ratingForm) {
                ratingForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    const messageDiv = document.getElementById('form-message');
                    const formData = new FormData(this);
                    
                    fetch('submit_rating.php', { method: 'POST', body: formData })
                    .then(response => response.json())
                    .then(data => {
                        let alertClass = data.status === 'success' ? 'bg-green-100 border-green-500 text-green-700' : 'bg-red-100 border-red-500 text-red-700';
                        messageDiv.innerHTML = `<div class="border-l-4 p-4 rounded-md ${alertClass}">${data.message}</div>`;
                        if (data.status === 'success') {
                            setTimeout(() => location.reload(), 1500);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        messageDiv.innerHTML = `<div class="border-l-4 p-4 rounded-md bg-red-100 border-red-500 text-red-700">Terjadi kesalahan. Silakan coba lagi.</div>`;
                    });
                });
            }
        });
    </script>
</body>
</html>