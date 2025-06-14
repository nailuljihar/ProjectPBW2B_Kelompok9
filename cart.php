<?php
// Menggunakan require_once untuk keamanan dan konsistensi
require_once('./includes/connect.php');
require_once('./functions/common_functions.php');

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$ip_address = getIPAddress();

// Logika untuk memperbarui kuantitas item (hanya perlu me-reload halaman)
if (isset($_POST['update_cart'])) {
    if (!empty($_POST['qty']) && is_array($_POST['qty'])) {
        foreach ($_POST['qty'] as $product_id => $quantity) {
            $product_id = (int)$product_id;
            $quantity = (int)$quantity;

            if ($quantity > 0) {
                $update_stmt = $con->prepare("UPDATE `cart_details` SET quantity = ? WHERE ip_address = ? AND product_id = ?");
                $update_stmt->bind_param("isi", $quantity, $ip_address, $product_id);
                $update_stmt->execute();
                $update_stmt->close();
            } else { 
                $delete_stmt = $con->prepare("DELETE FROM `cart_details` WHERE ip_address = ? AND product_id = ?");
                $delete_stmt->bind_param("si", $ip_address, $product_id);
                $delete_stmt->execute();
                $delete_stmt->close();
            }
        }
    }
    echo "<script>window.open('cart.php', '_self');</script>";
    exit();
}

// Mengambil data keranjang dengan satu query JOIN yang efisien
$cart_items = [];
$select_query = "
    SELECT 
        p.product_id, 
        p.product_title, 
        p.product_image_one, 
        p.product_price, 
        c.quantity 
    FROM `cart_details` c 
    JOIN `products` p ON c.product_id = p.product_id 
    WHERE c.ip_address = ?";
$stmt = $con->prepare($select_query);
$stmt->bind_param("s", $ip_address);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $cart_items[] = $row;
}
$stmt->close();

// Include header setelah semua logika selesai
include('./includes/header.php'); 
?>

<div class="container mx-auto px-4 py-12">
    <h1 class="text-3xl font-bold text-center text-gray-800 mb-8">Keranjang Belanja Anda</h1>
    
    <?php if (count($cart_items) > 0): ?>
    <form action="users_area/checkout.php" method="post" id="cartForm">
        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead class="bg-gray-200 text-gray-600 uppercase text-sm leading-normal">
                        <tr>
                            <th class="py-3 px-6 text-center">
                                <input type="checkbox" id="selectAll" class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            </th>
                            <th class="py-3 px-6 text-left">Produk</th>
                            <th class="py-3 px-6 text-center">Kuantitas</th>
                            <th class="py-3 px-6 text-center">Harga Satuan</th>
                            <th class="py-3 px-6 text-center">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-600 text-sm font-light">
                        <?php foreach ($cart_items as $item): 
                            $subtotal = $item['product_price'] * $item['quantity'];
                        ?>
                        <tr class="border-b border-gray-200 hover:bg-gray-50 item-row" data-price="<?php echo $item['product_price']; ?>" data-quantity="<?php echo $item['quantity']; ?>">
                            <td class="py-3 px-6 text-center">
                                <input type="checkbox" name="selected_items[]" value="<?php echo $item['product_id']; ?>" class="item-checkbox h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            </td>
                            <td class="py-3 px-6 text-left whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="mr-4 flex-shrink-0">
                                        <img src="./admin/product_images/<?php echo htmlspecialchars($item['product_image_one']); ?>" class="w-16 h-16 object-cover rounded" alt="<?php echo htmlspecialchars($item['product_title']); ?>">
                                    </div>
                                    <span class="font-medium"><?php echo htmlspecialchars($item['product_title']); ?></span>
                                </div>
                            </td>
                            <td class="py-3 px-6 text-center">
                                <!-- Form terpisah untuk update, agar tidak konflik dengan checkout -->
                                <form action="cart.php" method="post" class="inline">
                                    <input type="number" name="qty[<?php echo $item['product_id']; ?>]" value="<?php echo $item['quantity']; ?>" min="1" class="w-20 border text-center rounded focus:outline-none focus:ring-2 focus:ring-blue-500 quantity-input">
                                    <button type="submit" name="update_cart" class="text-xs text-blue-500 hover:underline ml-1">Update</button>
                                </form>
                            </td>
                            <td class="py-3 px-6 text-center">Rp <?php echo number_format($item['product_price']); ?></td>
                            <td class="py-3 px-6 text-center font-semibold">Rp <span class="subtotal-text"><?php echo number_format($subtotal); ?></span></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-8 flex flex-col md:flex-row justify-between items-start gap-6">
            <a href="index.php" class="bg-gray-200 text-gray-800 px-6 py-3 rounded-md font-semibold hover:bg-gray-300 transition-colors">Lanjut Belanja</a>
            <div class="bg-gray-50 p-6 rounded-lg shadow-inner w-full md:w-1/3">
                <h3 class="text-xl font-bold mb-4">Ringkasan Belanja</h3>
                <div class="flex justify-between mb-2">
                    <span class="text-gray-600">Total Terpilih</span>
                    <span id="total-price" class="font-bold text-lg text-blue-600">Rp 0</span>
                </div>
                <p class="text-gray-500 text-xs mt-2 mb-4">Total akan dihitung berdasarkan item yang Anda pilih.</p>
                <button type="submit" name="checkout_selected" id="checkout-btn" class="w-full bg-green-500 text-white font-semibold py-3 rounded-md hover:bg-green-600 transition-colors disabled:bg-gray-400 disabled:cursor-not-allowed" disabled>
                    Checkout Item Terpilih
                </button>
            </div>
        </div>
    </form>
    <?php else: ?>
        <div class="text-center py-16">
            <i class="fas fa-shopping-cart fa-4x text-gray-300 mb-4"></i>
            <h2 class="text-2xl font-semibold text-gray-700 mb-2">Keranjang Anda Kosong</h2>
            <p class="text-gray-500 mb-6">Sepertinya Anda belum menambahkan produk apapun.</p>
            <a href="products.php" class="bg-blue-600 text-white px-8 py-3 rounded-md font-semibold hover:bg-blue-700 transition-colors">Mulai Belanja</a>
        </div>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectAllCheckbox = document.getElementById('selectAll');
    const itemCheckboxes = document.querySelectorAll('.item-checkbox');
    const checkoutBtn = document.getElementById('checkout-btn');
    const totalPriceEl = document.getElementById('total-price');

    function updateTotals() {
        let total = 0;
        let selectedCount = 0;
        itemCheckboxes.forEach(checkbox => {
            if (checkbox.checked) {
                const row = checkbox.closest('.item-row');
                const price = parseFloat(row.dataset.price);
                const quantity = parseInt(row.dataset.quantity);
                total += price * quantity;
                selectedCount++;
            }
        });

        totalPriceEl.textContent = 'Rp ' + total.toLocaleString('id-ID');
        checkoutBtn.disabled = selectedCount === 0;
    }

    selectAllCheckbox.addEventListener('change', function() {
        itemCheckboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
        updateTotals();
    });

    itemCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            if (!this.checked) {
                selectAllCheckbox.checked = false;
            } else {
                // Cek apakah semua item terpilih
                const allChecked = Array.from(itemCheckboxes).every(cb => cb.checked);
                selectAllCheckbox.checked = allChecked;
            }
            updateTotals();
        });
    });

    // Panggil sekali di awal untuk inisialisasi
    updateTotals();
});
</script>

<?php include('./includes/footer.php'); ?>