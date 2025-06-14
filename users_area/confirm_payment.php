<!-- =================================================================== -->
<!-- Ganti seluruh isi file: uas-ecommerce/users_area/confirm_payment.php -->
<!-- =================================================================== -->
<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once('../includes/connect.php');
require_once('../functions/common_functions.php');

if (!isset($_SESSION['user_id'])) {
    header('Location: user_login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$invoice_to_confirm = '';
$amount_due = 0;
$errors = [];

if (isset($_GET['invoice_number'])) {
    $invoice_to_confirm = htmlspecialchars($_GET['invoice_number']);
    
    $stmt = $con->prepare("SELECT amount_due FROM `user_orders` WHERE invoice_number = ? AND user_id = ?");
    if($stmt){
        $stmt->bind_param("si", $invoice_to_confirm, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $amount_due = $row['amount_due'];
        } else {
            header('Location: profile.php?my_orders');
            exit();
        }
        $stmt->close();
    }
} else {
     header('Location: profile.php?my_orders');
     exit();
}

if (isset($_POST['confirm_payment'])) {
    $invoice_number = $_POST['invoice_number'];
    $amount = $_POST['amount'];
    // **PERBAIKAN KUNCI:** Menggunakan nama yang benar dari form
    $payment_method = $_POST['payment_method_select']; 
    $bukti_pembayaran = $_FILES['bukti_pembayaran'];

    if (empty($invoice_number) || empty($amount) || empty($payment_method)) {
        $errors[] = "Semua kolom wajib diisi.";
    }
    if ($bukti_pembayaran['error'] !== UPLOAD_ERR_OK) {
        $errors[] = "Anda harus mengunggah bukti pembayaran.";
    } else {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($bukti_pembayaran['type'], $allowed_types) || $bukti_pembayaran['size'] > 2000000) {
            $errors[] = "File harus berupa gambar (JPG, PNG, GIF) dan maksimal 2MB.";
        }
    }

    if (empty($errors)) {
        $bukti_nama_unik = time() . "_" . uniqid() . "_" . basename($bukti_pembayaran['name']);
        $target_dir = "bukti_pembayaran/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $target_file = $target_dir . $bukti_nama_unik;

        if (move_uploaded_file($bukti_pembayaran['tmp_name'], $target_file)) {
            $stmt_get_order = $con->prepare("SELECT order_id FROM `user_orders` WHERE invoice_number = ?");
            $stmt_get_order->bind_param("s", $invoice_number);
            $stmt_get_order->execute();
            $result_order = $stmt_get_order->get_result();
            
            if($result_order->num_rows > 0) {
                $order_data = $result_order->fetch_assoc();
                $order_id = $order_data['order_id'];
                
                // Menggunakan 'payment_method' sesuai nama kolom database
                $insert_stmt = $con->prepare("INSERT INTO `user_payments` (order_id, invoice_number, amount, payment_method, bukti, date) VALUES (?, ?, ?, ?, ?, NOW())");
                $insert_stmt->bind_param("isdss", $order_id, $invoice_number, $amount, $payment_method, $bukti_nama_unik);
                
                $update_stmt = $con->prepare("UPDATE `user_orders` SET order_status = 'Menunggu Konfirmasi' WHERE invoice_number = ?");
                $update_stmt->bind_param("s", $invoice_number);

                if ($insert_stmt->execute() && $update_stmt->execute()) {
                    echo "<script>alert('Terima kasih. Pembayaran Anda sedang menunggu konfirmasi dari admin.');</script>";
                    echo "<script>window.open('profile.php?my_orders','_self');</script>";
                    exit();
                } else {
                    $errors[] = "Gagal menyimpan data pembayaran: " . $con->error;
                }
                $insert_stmt->close();
                $update_stmt->close();
            } else {
                 $errors[] = "Nomor invoice tidak valid.";
            }
            $stmt_get_order->close();
        } else {
            $errors[] = "Gagal mengunggah file bukti pembayaran.";
        }
    }
}

$project_root = $_SERVER['DOCUMENT_ROOT'] . '/uas-ecommerce';
include($project_root . '/includes/header.php');
?>
    
<div class="container mx-auto my-12 px-4">
    <div class="max-w-2xl mx-auto">
        <h1 class="text-center text-3xl font-bold text-gray-800 mb-2">Konfirmasi Pembayaran</h1>
        <p class="text-center text-gray-500 mb-8">Selesaikan pesanan Anda dengan mengonfirmasi pembayaran.</p>
        <div class="bg-blue-50 border-l-4 border-blue-500 text-blue-800 p-6 rounded-lg mb-8 shadow">
            <h4 class="font-bold text-lg mb-2">Instruksi Pembayaran</h4>
            <p class="mb-3">Silakan lakukan transfer sejumlah <strong class="text-xl">Rp <?php echo number_format($amount_due, 0, ',', '.'); ?></strong> ke salah satu rekening berikut:</p>
            <ul class="list-disc list-inside space-y-1">
                <li><strong>Bank BCA:</strong> <span class="font-mono">123-456-7890</span> (a.n. PT LangkahKu Sejahtera)</li>
                <li><strong>Bank Mandiri:</strong> <span class="font-mono">098-765-4321</span> (a.n. PT LangkahKu Sejahtera)</li>
            </ul>
            <p class="text-sm text-red-600 mt-4 font-semibold">Penting: Pastikan Anda mentransfer sesuai dengan jumlah tagihan.</p>
        </div>
        <div class="bg-white p-8 rounded-lg shadow-lg">
            <form action="" method="post" enctype="multipart/form-data" class="space-y-6">
                 <?php if(!empty($errors)): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg" role="alert">
                        <?php foreach($errors as $error): ?>
                            <p><?php echo htmlspecialchars($error); ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                <div>
                    <label for="invoice_number" class="block text-sm font-medium text-gray-700">Nomor Invoice</label>
                    <input type="text" class="mt-1 block w-full px-3 py-2 bg-gray-100 border border-gray-300 rounded-md shadow-sm" id="invoice_number" name="invoice_number" value="<?php echo $invoice_to_confirm; ?>" required readonly>
                </div>
                 <div>
                    <label for="amount" class="block text-sm font-medium text-gray-700">Jumlah yang Ditransfer</label>
                    <input type="number" class="mt-1 block w-full px-3 py-2 bg-gray-100 border border-gray-300 rounded-md shadow-sm" id="amount" name="amount" value="<?php echo $amount_due; ?>" required readonly>
                </div>
                 <div>
                    <label for="payment_method_select" class="block text-sm font-medium text-gray-700">Metode Transfer</label>
                    <!-- **PERBAIKAN KUNCI:** Mengganti nama input menjadi 'payment_method_select' -->
                    <select name="payment_method_select" id="payment_method_select" class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500" required>
                        <option value="">-- Pilih Bank --</option>
                        <option value="BCA">BCA</option>
                        <option value="Mandiri">Mandiri</option>
                        <option value="BRI">BRI</option>
                        <option value="BNI">BNI</option>
                        <option value="Lainnya">Bank Lainnya</option>
                    </select>
                </div>
                <div>
                     <label for="bukti_pembayaran" class="block text-sm font-medium text-gray-700">Upload Bukti Transfer</label>
                     <input type="file" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" name="bukti_pembayaran" required>
                     <p class="text-xs text-gray-500 mt-1">Format: JPG, PNG, GIF. Ukuran maks: 2MB.</p>
                </div>
                <div class="text-center pt-4">
                    <button type="submit" name="confirm_payment" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-lg focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                        Konfirmasi Pembayaran
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php include($project_root . '/includes/footer.php'); ?>