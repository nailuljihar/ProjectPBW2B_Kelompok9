<div class="container mx-auto px-4 py-8">
    <h2 class="text-2xl font-bold mb-6 text-center">All Brands</h2>
    <div class="bg-white shadow-md rounded-lg overflow-x-auto">
        <table class="min-w-full leading-normal">
            <thead class="bg-gray-800 text-white">
                <tr>
                    <th class="px-5 py-3 border-b-2 border-gray-200 text-left text-xs font-semibold uppercase tracking-wider">No.</th>
                    <th class="px-5 py-3 border-b-2 border-gray-200 text-left text-xs font-semibold uppercase tracking-wider">Brand Title</th>
                    <th class="px-5 py-3 border-b-2 border-gray-200 text-left text-xs font-semibold uppercase tracking-wider">Logo</th>
                    <th class="px-5 py-3 border-b-2 border-gray-200 text-center text-xs font-semibold uppercase tracking-wider">Edit</th>
                    <th class="px-5 py-3 border-b-2 border-gray-200 text-center text-xs font-semibold uppercase tracking-wider">Delete</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if (isset($con)) {
                    $get_brand_query = "SELECT * FROM `brands` ORDER BY brand_title ASC";
                    $get_brand_result = mysqli_query($con, $get_brand_query);
                    $id_number = 1;
                    while ($row_fetch_brands = mysqli_fetch_array($get_brand_result)) {
                        $brand_id = $row_fetch_brands['brand_id'];
                        // **PERBAIKAN:** Menggunakan null coalescing operator untuk mencegah error
                        $brand_title = htmlspecialchars($row_fetch_brands['brand_title'] ?? '');
                        $brand_logo = htmlspecialchars($row_fetch_brands['brand_logo'] ?? '');
                        $logo_path = "./brand_images/$brand_logo";
                    ?>
                        <tr class="hover:bg-gray-100">
                            <td class="px-5 py-4 border-b border-gray-200 text-sm"><?php echo $id_number; ?></td>
                            <td class="px-5 py-4 border-b border-gray-200 text-sm"><?php echo $brand_title; ?></td>
                            <td class="px-5 py-4 border-b border-gray-200 text-sm">
                                <?php if (!empty($brand_logo) && file_exists($logo_path)) { ?>
                                    <img src="<?php echo $logo_path; ?>" alt="<?php echo $brand_title; ?> Logo" class="h-10 object-contain">
                                <?php } else { ?>
                                    <span class="text-gray-400 italic">No Logo</span>
                                <?php } ?>
                            </td>
                            <td class="px-5 py-4 border-b border-gray-200 text-sm text-center">
                                <a href='index.php?edit_brand=<?php echo $brand_id; ?>' class='text-blue-600 hover:text-blue-900'>
                                    <i class='fas fa-edit'></i>
                                </a>
                            </td>
                            <td class="px-5 py-4 border-b border-gray-200 text-sm text-center">
                                <a href='index.php?delete_brand=<?php echo $brand_id; ?>' class='text-red-600 hover:text-red-900' onclick="return confirm('Apakah Anda yakin ingin menghapus brand ini?');">
                                    <i class='fas fa-trash'></i>
                                </a>
                            </td>
                        </tr>
                    <?php
                        $id_number++;
                    }
                }
                ?>
            </tbody>
        </table>
    </div>
</div>