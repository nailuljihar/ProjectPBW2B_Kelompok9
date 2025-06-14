<?php
// Pastikan file koneksi di-include sekali saja di awal
if (file_exists('../includes/connect.php')) {
    include_once('../includes/connect.php');
}

// Logika untuk memproses form submission
if (isset($_POST['insert_product'])) {
    // Validasi input yang lebih ketat untuk mencegah error "Undefined array key"
    if (
        !isset($_POST['product_title']) || empty(trim($_POST['product_title'])) ||
        !isset($_POST['product_description']) || empty(trim($_POST['product_description'])) ||
        !isset($_POST['product_keywords']) || empty(trim($_POST['product_keywords'])) ||
        !isset($_POST['product_category']) || empty($_POST['product_category']) ||
        !isset($_POST['product_brand']) || empty($_POST['product_brand']) ||
        !isset($_POST['product_price']) || !is_numeric($_POST['product_price']) ||
        !isset($_FILES['product_image_one']) || $_FILES['product_image_one']['error'] !== UPLOAD_ERR_OK ||
        !isset($_FILES['product_image_two']) || $_FILES['product_image_two']['error'] !== UPLOAD_ERR_OK ||
        !isset($_FILES['product_image_three']) || $_FILES['product_image_three']['error'] !== UPLOAD_ERR_OK
    ) {
        echo "<script>alert('Semua kolom, termasuk ketiga gambar, harus diisi dengan benar.');</script>";
    } else {
        $product_title = trim($_POST['product_title']);
        $product_description = trim($_POST['product_description']);
        $product_keywords = trim($_POST['product_keywords']);
        $product_category = (int)$_POST['product_category'];
        $product_brand = (int)$_POST['product_brand'];
        $product_price = (float)$_POST['product_price'];
        $product_status = 'true';

        // Proses upload gambar
        $image_one_name = time() . '_1_' . $_FILES['product_image_one']['name'];
        $image_two_name = time() . '_2_' . $_FILES['product_image_two']['name'];
        $image_three_name = time() . '_3_' . $_FILES['product_image_three']['name'];

        $target_dir = "./product_images/";
        move_uploaded_file($_FILES['product_image_one']['tmp_name'], $target_dir . $image_one_name);
        move_uploaded_file($_FILES['product_image_two']['tmp_name'], $target_dir . $image_two_name);
        move_uploaded_file($_FILES['product_image_three']['tmp_name'], $target_dir . $image_three_name);

        // Query insert yang aman menggunakan prepared statements
        $insert_stmt = $con->prepare("INSERT INTO `products` (product_title, product_description, product_keywords, category_id, brand_id, product_image_one, product_image_two, product_image_three, product_price, date, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?)");
        $insert_stmt->bind_param("sssiisssds", $product_title, $product_description, $product_keywords, $product_category, $product_brand, $image_one_name, $image_two_name, $image_three_name, $product_price, $product_status);
        
        if ($insert_stmt->execute()) {
            echo "<script>alert('Produk berhasil ditambahkan.');</script>";
        } else {
            echo "<script>alert('Gagal menambahkan produk: " . $insert_stmt->error . "');</script>";
        }
        $insert_stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Insert Products - Admin Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-8">
    <div class="max-w-3xl mx-auto bg-white p-8 rounded-lg shadow-lg">
        <h1 class="text-2xl font-bold text-center mb-8">Insert New Product</h1>
        <form action="" method="post" enctype="multipart/form-data" class="space-y-6">
            <div>
                <label for="product_title" class="block text-sm font-medium text-gray-700">Product Title</label>
                <input type="text" placeholder="Enter Product Title" name="product_title" id="product_title" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" required>
            </div>
            <div class="grid md:grid-cols-3 gap-6">
                <div>
                    <label for="product_description" class="block text-sm font-medium text-gray-700">Description</label>
                    <input type="text" placeholder="Product Description" name="product_description" id="product_description" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm" required>
                </div>
                <div>
                    <label for="product_keywords" class="block text-sm font-medium text-gray-700">Keywords</label>
                    <input type="text" placeholder="e.g., sepatu, lari, nike" name="product_keywords" id="product_keywords" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm" required>
                </div>
                <div>
                    <label for="product_price" class="block text-sm font-medium text-gray-700">Price (Rp)</label>
                    <input type="number" step="0.01" placeholder="e.g., 750000" name="product_price" id="product_price" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm" required>
                </div>
            </div>
            <div class="grid md:grid-cols-2 gap-6">
                 <div>
                    <label for="product_category" class="block text-sm font-medium text-gray-700">Category</label>
                    <select class="mt-1 block w-full px-3 py-2 border border-gray-300 bg-white rounded-md shadow-sm" name="product_category" id="product_category" required>
                        <option value="">Select a Category</option>
                        <?php
                        $result_cat = $con->query('SELECT * FROM `categories` ORDER BY category_title');
                        while ($row = $result_cat->fetch_assoc()) {
                            echo "<option value='{$row['category_id']}'>" . htmlspecialchars($row['category_title']) . "</option>";
                        }
                        ?>
                    </select>
                </div>
                <div>
                    <label for="product_brand" class="block text-sm font-medium text-gray-700">Brand</label>
                    <select class="mt-1 block w-full px-3 py-2 border border-gray-300 bg-white rounded-md shadow-sm" name="product_brand" id="product_brand" required>
                        <option value="">Select a Brand</option>
                        <?php
                        $result_brand = $con->query('SELECT * FROM `brands` ORDER BY brand_title');
                        while ($row = $result_brand->fetch_assoc()) {
                            echo "<option value='{$row['brand_id']}'>" . htmlspecialchars($row['brand_title']) . "</option>";
                        }
                        ?>
                    </select>
                </div>
            </div>
             <div class="grid md:grid-cols-3 gap-6">
                <div>
                    <label for="product_image_one" class="block text-sm font-medium text-gray-700">Image 1 (Main)</label>
                    <input type="file" name="product_image_one" id="product_image_one" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:bg-gray-50 hover:file:bg-gray-100" required>
                </div>
                 <div>
                    <label for="product_image_two" class="block text-sm font-medium text-gray-700">Image 2</label>
                    <input type="file" name="product_image_two" id="product_image_two" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:bg-gray-50 hover:file:bg-gray-100" required>
                </div>
                 <div>
                    <label for="product_image_three" class="block text-sm font-medium text-gray-700">Image 3</label>
                    <input type="file" name="product_image_three" id="product_image_three" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:bg-gray-50 hover:file:bg-gray-100" required>
                </div>
            </div>
            <div class="text-center pt-4">
                <button type="submit" name="insert_product" class="cursor-pointer inline-flex justify-center py-2 px-8 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                    Insert Product
                </button>
            </div>
        </form>
    </div>
</body>
</html>