<?php
// Pastikan variabel koneksi dan sesi sudah tersedia dari profile.php
if (!isset($con) || !isset($_SESSION['user_id'])) {
    exit('Akses langsung tidak diizinkan.');
}

$user_id = $_SESSION['user_id'];
$errors = [];
$success_message = '';

// Mengambil data pengguna saat ini
$stmt = $con->prepare("SELECT * FROM `user_table` WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user_data = $result->fetch_assoc();
$stmt->close();

$username = htmlspecialchars($user_data['username']);
$user_email = htmlspecialchars($user_data['user_email']);
$user_address = htmlspecialchars($user_data['user_address']);
$user_mobile = htmlspecialchars($user_data['user_mobile']);
$user_image_old = htmlspecialchars($user_data['user_image']);

// Logika untuk memproses update data
if (isset($_POST['user_update'])) {
    $updated_username = trim($_POST['user_username']);
    $updated_email = trim($_POST['user_email']);
    $updated_address = trim($_POST['user_address']);
    $updated_mobile = trim($_POST['user_mobile']);
    $updated_image_new = $_FILES['user_image'];

    // Validasi input (bisa ditambahkan sesuai kebutuhan)
    if (empty($updated_username) || empty($updated_email) || empty($updated_address) || empty($updated_mobile)) {
        $errors[] = "Semua kolom harus diisi.";
    }

    $image_to_update = $user_image_old;
    if (isset($updated_image_new) && $updated_image_new['error'] == UPLOAD_ERR_OK) {
        $image_to_update = time() . '_' . basename($updated_image_new['name']);
        move_uploaded_file($updated_image_new['tmp_name'], "./user_images/$image_to_update");
    }

    if (empty($errors)) {
        $update_stmt = $con->prepare("UPDATE `user_table` SET username = ?, user_email = ?, user_image = ?, user_address = ?, user_mobile = ? WHERE user_id = ?");
        $update_stmt->bind_param("sssssi", $updated_username, $updated_email, $image_to_update, $updated_address, $updated_mobile, $user_id);
        
        if ($update_stmt->execute()) {
            $_SESSION['username'] = $updated_username; // Update session
            $success_message = "Data akun berhasil diperbarui!";
            // Refresh data untuk ditampilkan di form
            $username = htmlspecialchars($updated_username);
            $user_email = htmlspecialchars($updated_email);
            $user_address = htmlspecialchars($updated_address);
            $user_mobile = htmlspecialchars($updated_mobile);
            $user_image_old = htmlspecialchars($image_to_update);
        } else {
            $errors[] = "Gagal memperbarui data.";
        }
        $update_stmt->close();
    }
}
?>
<div class="w-full">
    <h3 class="text-2xl font-bold text-gray-800 mb-6">Edit Informasi Akun</h3>

    <?php if (!empty($errors)): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-4" role="alert">
            <?php foreach ($errors as $error): ?>
                <p><?php echo $error; ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if ($success_message): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-4" role="alert">
            <p><?php echo $success_message; ?></p>
        </div>
    <?php endif; ?>

    <form action="profile.php?edit_account" method="post" enctype="multipart/form-data" class="space-y-6">
        <div class="flex items-center space-x-6">
            <div class="shrink-0">
                <img class="h-20 w-20 object-cover rounded-full" src="./user_images/<?php echo $user_image_old; ?>" alt="Current profile photo" />
            </div>
            <label class="block">
                <span class="sr-only">Choose profile photo</span>
                <input type="file" name="user_image" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"/>
            </label>
        </div>
        <div>
            <label for="user_username" class="block text-sm font-medium text-gray-700">Username</label>
            <input type="text" name="user_username" id="user_username" class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500" value="<?php echo $username; ?>">
        </div>
        <div>
            <label for="user_email" class="block text-sm font-medium text-gray-700">Email</label>
            <input type="email" name="user_email" id="user_email" class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500" value="<?php echo $user_email; ?>">
        </div>
        <div>
            <label for="user_address" class="block text-sm font-medium text-gray-700">Alamat</label>
            <input type="text" name="user_address" id="user_address" class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500" value="<?php echo $user_address; ?>">
        </div>
        <div>
            <label for="user_mobile" class="block text-sm font-medium text-gray-700">Nomor Handphone</label>
            <input type="text" name="user_mobile" id="user_mobile" class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500" value="<?php echo $user_mobile; ?>">
        </div>
        <div class="text-right">
            <button type="submit" name="user_update" class="inline-flex justify-center py-2 px-6 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                Simpan Perubahan
            </button>
        </div>
    </form>
</div>