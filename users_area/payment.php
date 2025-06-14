<?php

// Ambil data user_id dari sesi
$user_id = $_SESSION['user_id'] ?? 0;
?>
<div class="container my-5">
    <h1 class="text-center text-3xl font-bold mb-8">Pilih Metode Pembayaran</h1>
    <div class="grid md:grid-cols-2 gap-8 max-w-2xl mx-auto">
        <!-- Opsi 1: Transfer Bank -->
        <div class="bg-white rounded-lg shadow-md p-8 text-center flex flex-col items-center justify-between hover:shadow-xl transition-shadow">
            <div class="flex-grow">
                <i class="fas fa-university fa-3x text-blue-500 mb-4"></i>
                <h3 class="text-xl font-bold mb-3">Transfer Bank (Cek Manual)</h3>
                <p class="text-gray-600 text-sm">Pesanan akan diproses setelah pembayaran Anda kami verifikasi secara manual.</p>
            </div>
            <a href="order.php?user_id=<?php echo $user_id; ?>&payment_method=transfer" class="mt-6 w-full bg-blue-600 text-white font-semibold py-2 px-4 rounded-lg hover:bg-blue-700 transition-colors">
                Bayar via Transfer
            </a>
        </div>

        <!-- Opsi 2: Cash on Delivery (COD) -->
        <div class="bg-white rounded-lg shadow-md p-8 text-center flex flex-col items-center justify-between hover:shadow-xl transition-shadow">
            <div class="flex-grow">
                <i class="fas fa-hand-holding-usd fa-3x text-green-500 mb-4"></i>
                <h3 class="text-xl font-bold mb-3">Cash on Delivery (COD)</h3>
                <p class="text-gray-600 text-sm">Bayar tunai kepada kurir saat pesanan Anda tiba di tujuan.</p>
            </div>
            <a href="order.php?user_id=<?php echo $user_id; ?>&payment_method=cod" class="mt-6 w-full bg-green-500 text-white font-semibold py-2 px-4 rounded-lg hover:bg-green-600 transition-colors">
                Pesan dengan COD
            </a>
        </div>
    </div>
</div>