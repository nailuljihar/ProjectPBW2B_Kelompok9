<?php
include('../includes/connect.php');

if (isset($_POST['insert_brand'])) {
    $brand_title = trim($_POST['brand_title']);
    $brand_logo = $_FILES['brand_logo'];

    // Validasi dasar
    if (empty($brand_title)) {
        echo "<script>alert('Nama brand tidak boleh kosong!');</script>";
    } else {
        // Cek duplikasi brand
        $select_stmt = $con->prepare("SELECT * FROM `brands` WHERE brand_title = ?");
        $select_stmt->bind_param("s", $brand_title);
        $select_stmt->execute();
        $result = $select_stmt->get_result();

        if ($result->num_rows > 0) {
            echo "<script>alert('Brand ini sudah ada di database.');</script>";
        } else {
            $logo_name = '';
            // Proses upload logo jika ada
            if (isset($brand_logo) && $brand_logo['error'] == UPLOAD_ERR_OK) {
                $logo_name = time() . '_' . $brand_logo['name'];
                $target_dir = "./brand_images/";
                $target_file = $target_dir . basename($logo_name);

                // Buat direktori jika belum ada
                if (!is_dir($target_dir)) {
                    mkdir($target_dir, 0777, true);
                }

                if (!move_uploaded_file($brand_logo['tmp_name'], $target_file)) {
                    echo "<script>alert('Gagal mengunggah logo.');</script>";
                    $logo_name = ''; // Kosongkan nama logo jika gagal
                }
            }

            // Insert ke database
            $insert_stmt = $con->prepare("INSERT INTO `brands` (brand_title, brand_logo) VALUES (?, ?)");
            $insert_stmt->bind_param("ss", $brand_title, $logo_name);
            if ($insert_stmt->execute()) {
                echo "<script>alert('Brand berhasil ditambahkan.');</script>";
            } else {
                echo "<script>alert('Gagal menambahkan brand.');</script>";
            }
            $insert_stmt->close();
        }
        $select_stmt->close();
    }
}
?>

<div class="container mx-auto px-4 py-8">
    <h2 class="text-2xl font-bold mb-6 text-center">Insert New Brand</h2>
    <!-- Menggunakan enctype untuk upload file -->
    <form action="" method="POST" class="max-w-md mx-auto bg-white p-6 rounded-lg shadow-md" enctype="multipart/form-data">
        <div class="mb-4">
            <label for="brand_title" class="block text-gray-700 text-sm font-bold mb-2">Brand Title:</label>
            <input type="text" id="brand_title" name="brand_title" placeholder="Enter Brand Title" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
        </div>
        <div class="mb-6">
            <label for="brand_logo" class="block text-gray-700 text-sm font-bold mb-2">Brand Logo:</label>
            <input type="file" id="brand_logo" name="brand_logo" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
            <p class="text-xs text-gray-500 mt-1">PNG, JPG, GIF up to 2MB.</p>
        </div>
        <div class="flex items-center justify-center">
            <button type="submit" name="insert_brand" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded focus:outline-none focus:shadow-outline">
                Insert Brand
            </button>
        </div>
    </form>
</div>