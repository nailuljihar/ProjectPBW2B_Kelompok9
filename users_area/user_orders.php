<?php
// Pastikan variabel koneksi dan sesi sudah tersedia dari profile.php
if (!isset($con) || !isset($_SESSION['user_id'])) {
    exit('Akses langsung tidak diizinkan.');
}
$user_id = $_SESSION['user_id'];
?>

<div class="w-full">
    <h3 class="text-2xl font-bold text-gray-800 mb-6">Riwayat Pesanan Saya</h3>
    <div class="overflow-x-auto bg-white rounded-lg shadow">
        <table class="min-w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="py-3 px-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">No. Invoice</th>
                    <th class="py-3 px-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Jumlah</th>
                    <th class="py-3 px-4 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Jml. Produk</th>
                    <th class="py-3 px-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Tanggal</th>
                    <th class="py-3 px-4 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Status</th>
                    <th class="py-3 px-4 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="text-gray-700 divide-y divide-gray-200">
                <?php
                $get_orders_stmt = $con->prepare("SELECT * FROM `user_orders` WHERE user_id = ? ORDER BY order_date DESC");
                $get_orders_stmt->bind_param("i", $user_id);
                $get_orders_stmt->execute();
                $result_orders = $get_orders_stmt->get_result();
                
                if ($result_orders->num_rows > 0) {
                    while ($row = $result_orders->fetch_assoc()) {
                        $order_id = $row['order_id'];
                        $amount_due = $row['amount_due'];
                        $invoice_number = htmlspecialchars($row['invoice_number']);
                        $total_products = $row['total_products'];
                        $order_date = date("d M Y, H:i", strtotime($row['order_date']));
                        $order_status = htmlspecialchars($row['order_status']);
                ?>
                        <tr class="hover:bg-gray-50">
                            <td class="py-4 px-4 font-mono text-sm font-medium text-gray-900"><?php echo $invoice_number; ?></td>
                            <td class="py-4 px-4 text-sm">Rp <?php echo number_format($amount_due); ?></td>
                            <td class="py-4 px-4 text-sm text-center"><?php echo $total_products; ?></td>
                            <td class="py-4 px-4 text-sm text-gray-500"><?php echo $order_date; ?></td>
                            <td class="py-4 px-4 text-center">
                                <?php
                                $status_bg = 'bg-gray-200 text-gray-800'; // Default
                                if (strpos(strtolower($order_status), 'pending') !== false || strpos(strtolower($order_status), 'menunggu') !== false) {
                                    $status_bg = 'bg-yellow-100 text-yellow-800';
                                } elseif (strpos(strtolower($order_status), 'selesai') !== false || strpos(strtolower($order_status), 'paid') !== false) {
                                    $status_bg = 'bg-green-100 text-green-800';
                                } elseif (strpos(strtolower($order_status), 'cod') !== false) {
                                    $status_bg = 'bg-blue-100 text-blue-800';
                                }
                                echo "<span class='py-1 px-3 rounded-full text-xs font-semibold $status_bg'>$order_status</span>";
                                ?>
                            </td>
                            <td class="py-4 px-4 text-center">
                                <?php
                                if ($order_status == 'pending') {
                                    echo "<a href='confirm_payment.php?invoice_number=$invoice_number' class='bg-blue-500 hover:bg-blue-600 text-white text-xs font-bold py-1 px-3 rounded-md transition-colors'>Konfirmasi</a>";
                                } elseif (strpos($order_status, 'COD') !== false) {
                                    echo "<span class='text-gray-400 text-xs italic'>Bayar di Tempat</span>";
                                } else {
                                    echo "<span class='text-gray-400 text-xs italic'>-</span>";
                                }
                                ?>
                            </td>
                        </tr>
                <?php
                    }
                } else {
                    echo "<tr><td colspan='6' class='text-center py-16 text-gray-500'>Anda belum memiliki riwayat pesanan.</td></tr>";
                }
                $get_orders_stmt->close();
                ?>
            </tbody>
        </table>
    </div>
</div>