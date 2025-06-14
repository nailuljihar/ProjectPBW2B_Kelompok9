<?php
// error_reporting(E_ALL);
// ini_set('display_errors', 1);

// if (session_status() == PHP_SESSION_NONE) {
//     session_start();
// }

// include_once('includes/connect.php');

// Fungsi untuk mendapatkan alamat IP pengguna secara aman
function getIPAddress() {  
    if(!empty($_SERVER['HTTP_CLIENT_IP'])) {  
        return $_SERVER['HTTP_CLIENT_IP'];  
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {  
        return $_SERVER['HTTP_X_FORWARDED_FOR'];  
    } else {  
        return $_SERVER['REMOTE_ADDR'];  
    }  
}

/**
 * Fungsi untuk menampilkan kartu produk dengan Tailwind CSS.
 * Menggunakan htmlspecialchars untuk mencegah XSS.
 * @param array $row_data Data produk dari database.
 */
function display_product_card($row_data) {
    global $con;
    $product_id = (int)$row_data['product_id'];
    $product_title = htmlspecialchars($row_data['product_title']);
    $product_description = htmlspecialchars($row_data['product_description']);
    $product_price = $row_data['product_price'];
    $image_name = htmlspecialchars($row_data['product_image_one']);
    $image_path = "./admin/product_images/$image_name";
    
    // Gunakan placeholder jika gambar tidak ada
    $image_source = file_exists($image_path) ? $image_path : "https://placehold.co/500x500/f0f0f0/999999?text=LangkahKu";

    echo "
    <div class='bg-white rounded-lg shadow-md overflow-hidden transform hover:scale-105 transition-transform duration-300 flex flex-col group'>
        <div class='relative'>
            <a href='product_details.php?product_id=$product_id'>
                <img src='$image_source' alt=\"$product_title\" class='w-full h-64 object-cover'>
            </a>
            <div class='absolute bottom-0 left-0 right-0 bg-black bg-opacity-50 p-2 opacity-0 group-hover:opacity-100 transition-opacity duration-300'>
                <a href='product_details.php?product_id=$product_id' class='text-white text-sm text-center block'>Lihat Detail</a>
            </div>
        </div>
        <div class='p-4 flex flex-col flex-grow'>
            <h3 class='text-lg font-semibold text-gray-800 truncate' title=\"$product_title\">$product_title</h3>
            <div class='my-2'>
                " . get_average_rating($product_id) . "
            </div>
            <p class='text-xl font-bold text-gray-900 mt-auto'>Rp " . number_format($product_price, 0, ',', '.') . "</p>
            <a href='index.php?add_to_cart=$product_id' class='w-full mt-4 text-center bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-3 rounded-md transition-colors text-sm'>
                <i class='fa-solid fa-cart-shopping'></i> Tambah ke Keranjang
            </a>
        </div>
    </div>";
}

/**
 * Fungsi untuk mendapatkan dan menampilkan produk di halaman utama.
 * @param int $limit Jumlah produk yang ingin ditampilkan.
 */
function get_featured_products($limit = 8) {
    global $con;
    $select_query = "SELECT * FROM `products` ORDER BY rand() LIMIT ?";
    $stmt = $con->prepare($select_query);
    if ($stmt === false) { die("Prepare failed: " . $con->error); }
    $stmt->bind_param("i", $limit);
    $stmt->execute();
    $result_query = $stmt->get_result();
    while ($row = $result_query->fetch_assoc()) {
        display_product_card($row);
    }
    $stmt->close();
}

/**
 * Fungsi untuk menampilkan semua produk (digunakan di halaman products.php).
 */
function get_all_products(){
    global $con;
    // Hanya berjalan jika tidak ada filter kategori atau brand yang aktif
    if (!isset($_GET['category']) && !isset($_GET['brand'])) {
        $select_query = "SELECT * FROM `products` ORDER BY date DESC";
        $result_query = mysqli_query($con, $select_query);
        if(!$result_query){ die("Error: " . mysqli_error($con)); }
        while ($row = mysqli_fetch_assoc($result_query)) {
            display_product_card($row);
        }
    }
}

/**
 * Fungsi untuk menampilkan produk berdasarkan kategori yang dipilih.
 */
function get_unique_categories(){
    global $con;
    if (isset($_GET['category'])) {
        $category_id = (int)$_GET['category'];
        $select_query = "SELECT * FROM `products` WHERE category_id = ?";
        $stmt = $con->prepare($select_query);
        if ($stmt === false) { die("Prepare failed: " . $con->error); }
        $stmt->bind_param("i", $category_id);
        $stmt->execute();
        $result_query = $stmt->get_result();
        
        if ($result_query->num_rows == 0) {
            echo "<h2 class='text-center text-red-500 font-bold col-span-full'>Tidak ada stok untuk kategori ini.</h2>";
        }
        while ($row = $result_query->fetch_assoc()) {
            display_product_card($row);
        }
        $stmt->close();
    }
}

/**
 * Fungsi untuk menampilkan produk berdasarkan brand yang dipilih.
 */
function get_unique_brands(){
    global $con;
    if (isset($_GET['brand'])) {
        $brand_id = (int)$_GET['brand'];
        $select_query = "SELECT * FROM `products` WHERE brand_id = ?";
        $stmt = $con->prepare($select_query);
        if ($stmt === false) { die("Prepare failed: " . $con->error); }
        $stmt->bind_param("i", $brand_id);
        $stmt->execute();
        $result_query = $stmt->get_result();

        if ($result_query->num_rows == 0) {
            echo "<h2 class='text-center text-red-500 font-bold col-span-full'>Merek ini tidak tersedia saat ini.</h2>";
        }
        while ($row = $result_query->fetch_assoc()) {
           display_product_card($row);
        }
        $stmt->close();
    }
}

/**
 * Fungsi untuk menampilkan daftar brand di sidebar.
 */
function getbrands() {
    global $con;
    $select_brands = "SELECT * FROM `brands` ORDER BY brand_title ASC";
    $result_brands = mysqli_query($con, $select_brands);
    while ($row_data = mysqli_fetch_assoc($result_brands)) {
        $brand_title = htmlspecialchars($row_data['brand_title']);
        $brand_id = (int)$row_data['brand_id'];
        echo "<li><a href='products.php?brand=$brand_id' class='block px-2 py-1 text-gray-700 hover:bg-gray-100 rounded-md'>$brand_title</a></li>";
    }
}

/**
 * Fungsi untuk menampilkan brand di Halaman Utama sesuai permintaan.
 */
function get_brands_for_homepage() {
    global $con;
    $select_brands = "SELECT * FROM `brands` WHERE brand_logo IS NOT NULL AND brand_logo != '' LIMIT 6";
    $result_brands = mysqli_query($con, $select_brands);
    while ($row_data = mysqli_fetch_assoc($result_brands)) {
        $brand_title = htmlspecialchars($row_data['brand_title']);
        $brand_id = (int)$row_data['brand_id'];
        $brand_logo = htmlspecialchars($row_data['brand_logo']);
        $logo_path = "./assets/images/logo/$brand_logo"; 
        
        $logo_src = file_exists($logo_path) ? $logo_path : "https://placehold.co/150x80/f0f0f0/999999?text=" . urlencode($brand_title);

        echo "
        <a href='products.php?brand=$brand_id' class='border-2 border-gray-200 hover:border-blue-500 hover:shadow-lg transition-all p-6 rounded-lg flex flex-col items-center justify-center text-center'>
            <img src='$logo_src' alt='$brand_title' class='h-12 object-contain mb-2'>
            <span class='font-semibold text-gray-700'>$brand_title</span>
        </a>";
    }
}

/**
 * Fungsi untuk menampilkan daftar kategori di sidebar.
 */
function getcategories() {
    global $con;
    $select_categories = "SELECT * FROM `categories` ORDER BY category_title ASC";
    $result_categories = mysqli_query($con, $select_categories);
    while ($row_data = mysqli_fetch_assoc($result_categories)) {
        $category_title = htmlspecialchars($row_data['category_title']);
        $category_id = (int)$row_data['category_id'];
        echo "<li><a href='products.php?category=$category_id' class='block px-2 py-1 text-gray-700 hover:bg-gray-100 rounded-md'>$category_title</a></li>";
    }
}

/**
 * Fungsi untuk mencari produk dengan aman.
 */
function search_product() {
    global $con;
    if (isset($_GET['search_data_product'])) {
        $search_data_value = htmlspecialchars($_GET['search_data']);
        $search_query = "SELECT * FROM `products` WHERE product_keywords LIKE ?";
        $search_term = "%" . $search_data_value . "%";
        $stmt = $con->prepare($search_query);
        if ($stmt === false) { die("Prepare failed: " . $con->error); }
        $stmt->bind_param("s", $search_term);
        $stmt->execute();
        $result_query = $stmt->get_result();
        
        if ($result_query->num_rows == 0) {
            echo "<h2 class='text-center text-red-500 font-bold col-span-full'>Hasil tidak ditemukan untuk \"$search_data_value\".</h2>";
        }
        while ($row = $result_query->fetch_assoc()) {
            display_product_card($row);
        }
        $stmt->close();
    }
}

/**
 * Fungsi untuk menambahkan item ke keranjang.
 */
function cart() {
    global $con;
    if (isset($_GET['add_to_cart'])) {
        $get_ip_address = getIPAddress();
        $get_product_id = (int)$_GET['add_to_cart'];

        $select_query = "SELECT * FROM `cart_details` WHERE ip_address = ? AND product_id = ?";
        $stmt = $con->prepare($select_query);
        $stmt->bind_param("si", $get_ip_address, $get_product_id);
        $stmt->execute();
        $result_query = $stmt->get_result();
        
        if ($result_query->num_rows > 0) {
            echo "<script>alert('Item ini sudah ada di keranjang Anda.')</script>";
            echo "<script>window.open('index.php','_self')</script>";
        } else {
            $insert_query = "INSERT INTO `cart_details` (product_id, ip_address, quantity) VALUES (?, ?, 1)";
            $stmt_insert = $con->prepare($insert_query);
            $stmt_insert->bind_param("is", $get_product_id, $get_ip_address);
            $stmt_insert->execute();
            echo "<script>alert('Item berhasil ditambahkan ke keranjang.')</script>";
            echo "<script>window.open('index.php','_self')</script>";
            $stmt_insert->close();
        }
        $stmt->close();
    }
}

/**
 * Fungsi untuk menghitung jumlah item di keranjang.
 */
function cart_item() {
    global $con;
    $get_ip_address = getIPAddress();
    $select_query = "SELECT * FROM `cart_details` WHERE ip_address = ?";
    $stmt = $con->prepare($select_query);
    if ($stmt === false) { echo "0"; return; }
    $stmt->bind_param("s", $get_ip_address);
    $stmt->execute();
    $stmt->store_result();
    $count_cart_items = $stmt->num_rows;
    $stmt->close();
    echo $count_cart_items;
}

/**
 * Fungsi untuk menghitung total harga di keranjang.
 */
function total_cart_price() {
    global $con;
    $get_ip_address = getIPAddress();
    $total_price = 0;
    
    $cart_query = "
        SELECT p.product_price, c.quantity 
        FROM `cart_details` c
        JOIN `products` p ON c.product_id = p.product_id
        WHERE c.ip_address = ?";

    $stmt = $con->prepare($cart_query);
    if ($stmt === false) { echo "0"; return; }
    $stmt->bind_param("s", $get_ip_address);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $total_price += ($row['product_price'] * $row['quantity']);
    }
    $stmt->close();
    echo number_format($total_price, 0, ',', '.');
}

/**
 * Fungsi untuk menampilkan status pesanan di profil pengguna.
 */
function get_user_order_details(){
    global $con;
    if (isset($_SESSION['user_id'])) {
        $user_id = (int)$_SESSION['user_id'];
        // Hanya tampilkan jika tidak ada aksi lain di halaman profil
        if (!isset($_GET['edit_account']) && !isset($_GET['my_orders']) && !isset($_GET['delete_account'])) {
            $get_orders = "SELECT * FROM `user_orders` WHERE user_id = ? AND order_status = 'pending'";
            $stmt = $con->prepare($get_orders);
            if ($stmt === false) { return; }
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $stmt->store_result();
            $row_count = $stmt->num_rows;

            if ($row_count > 0) {
                echo "<div class='text-center p-4 bg-yellow-100 border border-yellow-300 rounded-lg'>
                        <h3 class='text-lg font-semibold text-yellow-800'>Anda memiliki <span class='font-bold'>$row_count</span> pesanan yang belum selesai.</h3>
                        <a href='profile.php?my_orders' class='text-blue-600 hover:underline mt-2 inline-block'>Lihat Detail Pesanan</a>
                      </div>";
            } else {
                 echo "<div class='text-center p-4 bg-green-100 border border-green-300 rounded-lg'>
                        <h3 class='text-lg font-semibold text-green-800'>Tidak ada pesanan yang sedang diproses.</h3>
                        <a href='products.php' class='text-blue-600 hover:underline mt-2 inline-block'>Mulai Belanja Sekarang</a>
                      </div>";
            }
            $stmt->close();
        }
    }
}

// Fungsi untuk rating produk, sudah aman
function get_average_rating($product_id) {
    global $con;
    $product_id = (int)$product_id;
    $query = "SELECT AVG(rating) as avg_rating, COUNT(rating) as total_reviews FROM `ratings` WHERE product_id = ?";
    
    $stmt = $con->prepare($query);
    if ($stmt === false) { return "Error"; }
    
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $data = $result->fetch_assoc();
    $avg_rating = $data['avg_rating'] ? (float)$data['avg_rating'] : 0;
    $total_reviews = $data['total_reviews'] ? (int)$data['total_reviews'] : 0;
    
    $stmt->close();
    
    $output = display_star_rating($avg_rating);
    $output .= " <span class='text-sm text-gray-500 ml-1'>(" . number_format($avg_rating, 1) . " dari " . $total_reviews . " ulasan)</span>";
    
    return $output;
}

function display_star_rating($rating) {
    $output = "<div class='flex items-center text-yellow-400'>";
    $full_stars = floor($rating);
    $half_star = ($rating - $full_stars) >= 0.5 ? 1 : 0;
    $empty_stars = 5 - $full_stars - $half_star;

    for ($i = 0; $i < $full_stars; $i++) {
        $output .= "<i class='fas fa-star'></i>";
    }
    if ($half_star) {
        $output .= "<i class='fas fa-star-half-alt'></i>";
    }
    for ($i = 0; $i < $empty_stars; $i++) {
        $output .= "<i class='far fa-star text-gray-300'></i>";
    }
    $output .= "</div>";
    return $output;
}

?>