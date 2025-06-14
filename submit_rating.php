<?php
session_start();
include('./includes/connect.php');

// Periksa apakah pengguna sudah login
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Anda harus login untuk memberikan rating.']);
    exit;
}

// Periksa apakah metode request adalah POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['user_id'];
    $product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
    $rating = isset($_POST['rating']) ? (int)$_POST['rating'] : 0;
    $review_title = isset($_POST['review_title']) ? htmlspecialchars($_POST['review_title']) : '';
    $review = isset($_POST['review']) ? htmlspecialchars($_POST['review']) : '';

    if ($product_id > 0 && $rating > 0) {
        // Periksa apakah pengguna sudah pernah memberikan rating untuk produk ini sebelumnya
        $check_query = "SELECT * FROM `ratings` WHERE user_id = ? AND product_id = ?";
        $stmt_check = $con->prepare($check_query);
        $stmt_check->bind_param("ii", $user_id, $product_id);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();

        if ($result_check->num_rows > 0) {
            // Jika sudah ada, update rating yang ada
            $update_query = "UPDATE `ratings` SET rating = ?, review_title = ?, review = ? WHERE user_id = ? AND product_id = ?";
            $stmt_update = $con->prepare($update_query);
            $stmt_update->bind_param("issii", $rating, $review_title, $review, $user_id, $product_id);
            if ($stmt_update->execute()) {
                echo json_encode(['status' => 'success', 'message' => 'Rating Anda berhasil diperbarui.']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Gagal memperbarui rating.']);
            }
            $stmt_update->close();
        } else {
            // Jika belum ada, masukkan rating baru
            $insert_query = "INSERT INTO `ratings` (product_id, user_id, rating, review_title, review) VALUES (?, ?, ?, ?, ?)";
            $stmt_insert = $con->prepare($insert_query);
            $stmt_insert->bind_param("iiiss", $product_id, $user_id, $rating, $review_title, $review);
            if ($stmt_insert->execute()) {
                echo json_encode(['status' => 'success', 'message' => 'Terima kasih atas rating Anda!']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Gagal menyimpan rating.']);
            }
            $stmt_insert->close();
        }
        $stmt_check->close();
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Data tidak lengkap.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Metode request tidak valid.']);
}

$con->close();
?>