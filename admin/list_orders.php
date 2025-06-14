<?php
// Logika untuk konfirmasi pembayaran oleh admin
if (isset($_GET['confirm_order_payment']) && isset($con)) {
    $order_id_to_confirm = (int)$_GET['confirm_order_payment'];
    $update_stmt = $con->prepare("UPDATE `user_orders` SET order_status = 'Selesai' WHERE order_id = ?");
    $update_stmt->bind_param("i", $order_id_to_confirm);
    if ($update_stmt->execute()) {
        echo "<script>alert('Pembayaran telah dikonfirmasi dan pesanan selesai.'); window.location.href='index.php?list_orders';</script>";
    } else {
        echo "<script>alert('Gagal mengonfirmasi pembayaran.');</script>";
    }
    $update_stmt->close();
}
?>
<div class="container mx-auto px-4 py-8">
    <h2 class="text-2xl font-bold mb-6 text-center">All Orders</h2>
    <div class="bg-white shadow-md rounded-lg overflow-x-auto">
        <table class="min-w-full leading-normal">
            <thead class="bg-gray-800 text-white">
                <tr>
                    <th class="px-5 py-3 border-b-2 border-gray-200 text-left text-xs font-semibold uppercase tracking-wider">Order ID</th>
                    <th class="px-5 py-3 border-b-2 border-gray-200 text-left text-xs font-semibold uppercase tracking-wider">Amount Due</th>
                    <th class="px-5 py-3 border-b-2 border-gray-200 text-left text-xs font-semibold uppercase tracking-wider">Invoice</th>
                    <th class="px-5 py-3 border-b-2 border-gray-200 text-center text-xs font-semibold uppercase tracking-wider">Total Products</th>
                    <th class="px-5 py-3 border-b-2 border-gray-200 text-left text-xs font-semibold uppercase tracking-wider">Order Date</th>
                    <th class="px-5 py-3 border-b-2 border-gray-200 text-center text-xs font-semibold uppercase tracking-wider">Payment Proof</th>
                    <th class="px-5 py-3 border-b-2 border-gray-200 text-center text-xs font-semibold uppercase tracking-wider">Status</th>
                    <th class="px-5 py-3 border-b-2 border-gray-200 text-center text-xs font-semibold uppercase tracking-wider">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
            <?php
            if (isset($con)) {
                $get_orders_query = "SELECT * FROM `user_orders` ORDER BY order_date DESC";
                $result = mysqli_query($con, $get_orders_query);
                if(mysqli_num_rows($result) > 0) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        $order_id = $row['order_id'];
                        $amount_due = $row['amount_due'];
                        $invoice_number = htmlspecialchars($row['invoice_number']);
                        $total_products = $row['total_products'];
                        $order_date = date("d M Y, H:i", strtotime($row['order_date']));
                        $order_status = htmlspecialchars($row['order_status']);
                ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-5 py-4 text-sm"><?php echo $order_id; ?></td>
                        <td class="px-5 py-4 text-sm">Rp <?php echo number_format($amount_due); ?></td>
                        <td class="px-5 py-4 text-sm font-mono"><?php echo $invoice_number; ?></td>
                        <td class="px-5 py-4 text-sm text-center"><?php echo $total_products; ?></td>
                        <td class="px-5 py-4 text-sm text-gray-600"><?php echo $order_date; ?></td>
                        <td class="px-5 py-4 text-sm text-center">
                            <?php 
                            $get_payment = $con->prepare("SELECT bukti FROM `user_payments` WHERE invoice_number = ?");
                            $get_payment->bind_param("s", $invoice_number);
                            $get_payment->execute();
                            $result_payment = $get_payment->get_result();
                            if($result_payment->num_rows > 0){
                                $row_payment = $result_payment->fetch_assoc();
                                $bukti_file = htmlspecialchars($row_payment['bukti']);
                                echo "<a href='../users_area/bukti_pembayaran/$bukti_file' target='_blank' class='text-blue-500 hover:underline'>Lihat Bukti</a>";
                            } else {
                                echo "<span class='text-gray-400'>-</span>";
                            }
                            $get_payment->close();
                            ?>
                        </td>
                        <td class="px-5 py-4 text-sm text-center">
                             <?php
                                $status_bg = 'bg-gray-200 text-gray-800'; // Default
                                if (strpos(strtolower($order_status), 'menunggu') !== false) {
                                    $status_bg = 'bg-yellow-100 text-yellow-800';
                                } elseif (strpos(strtolower($order_status), 'selesai') !== false || strpos(strtolower($order_status), 'paid') !== false) {
                                    $status_bg = 'bg-green-100 text-green-800';
                                } elseif (strpos(strtolower($order_status), 'cod') !== false) {
                                    $status_bg = 'bg-blue-100 text-blue-800';
                                }
                                echo "<span class='py-1 px-3 rounded-full text-xs font-bold $status_bg'>$order_status</span>";
                            ?>
                        </td>
                        <td class="px-5 py-4 text-sm text-center">
                            <?php
                            if($order_status == 'Menunggu Konfirmasi'){
                                echo "<a href='index.php?list_orders&confirm_order_payment=$order_id' class='bg-green-500 hover:bg-green-600 text-white text-xs font-bold py-1 px-2 rounded'>Setujui</a>";
                            } else {
                                 echo "<a href='index.php?delete_order=$order_id' class='text-red-500 hover:text-red-700' onclick=\"return confirm('Anda yakin ingin menghapus pesanan ini?');\"><i class='fas fa-trash'></i></a>";
                            }
                            ?>
                        </td>
                    </tr>
                <?php } } else { ?>
                    <tr><td colspan="8" class="text-center py-10 text-gray-500">Tidak ada pesanan yang ditemukan.</td></tr>
                <?php }
            } ?>
            </tbody>
        </table>
    </div>
</div>