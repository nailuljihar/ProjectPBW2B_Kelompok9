<?php
// Pastikan variabel koneksi dan sesi sudah tersedia dari profile.php
if (!isset($con) || !isset($_SESSION['user_id'])) {
    exit('Akses langsung tidak diizinkan.');
}

// Logika untuk menghapus akun jika form disubmit
if (isset($_POST['submit_delete'])) {
    $user_id = $_SESSION['user_id'];

    // Gunakan prepared statement untuk keamanan
    $delete_stmt = $con->prepare("DELETE FROM `user_table` WHERE user_id = ?");
    $delete_stmt->bind_param("i", $user_id);
    
    if ($delete_stmt->execute()) {
        // Hapus session dan alihkan ke halaman utama
        session_unset();
        session_destroy();
        echo "<script>alert('Akun Anda telah berhasil dihapus.');</script>";
        echo "<script>window.open('../index.php','_self');</script>";
        exit();
    } else {
        echo "<script>alert('Gagal menghapus akun. Silakan coba lagi.');</script>";
    }
    $delete_stmt->close();
}

// Jika tombol "Batal" diklik, kembali ke dashboard profil
if (isset($_POST['submit_dont_delete'])) {
    echo "<script>window.open('profile.php','_self');</script>";
    exit();
}
?>

<div class="w-full">
    <h3 class="text-2xl font-bold text-red-600 mb-4">Hapus Akun</h3>
    <div class="bg-red-50 border-l-4 border-red-500 text-red-800 p-6 rounded-lg shadow-md">
        <div class="flex">
            <div class="py-1"><i class="fas fa-exclamation-triangle fa-2x text-red-500 mr-4"></i></div>
            <div>
                <p class="font-bold text-lg">Peringatan Penting!</p>
                <p class="text-sm mt-1">
                    Tindakan ini bersifat **permanen** dan tidak dapat diurungkan. Menghapus akun Anda akan menghilangkan semua riwayat pesanan, data pribadi, dan akses Anda ke situs ini.
                </p>
                <p class="mt-4 font-semibold">Apakah Anda benar-benar yakin ingin melanjutkan?</p>
            </div>
        </div>

        <form method="post" action="profile.php?delete_account" class="mt-6 flex flex-col sm:flex-row gap-4">
            <button type="submit" name="submit_delete" class="w-full sm:w-auto bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-6 rounded-lg focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors">
                Ya, Hapus Akun Saya
            </button>
            <button type="submit" name="submit_dont_delete" class="w-full sm:w-auto bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-2 px-6 rounded-lg focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-400 transition-colors">
                Batal
            </button>
        </form>
    </div>
</div>