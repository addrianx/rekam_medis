<?php
session_start();
include 'koneksi.php';

// Cek apakah pengguna sudah login dan memiliki role petugas (role = 2)
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] != 2) {
    $_SESSION['flash_message'] = [
        'type' => 'error',
        'message' => 'Anda harus login sebagai petugas untuk mengakses halaman ini!'
    ];
    header("Location: index.php");
    exit();
}

// Proses logout
if (isset($_POST['logout'])) {
    session_unset();
    session_destroy();
    header("Location: index.php");
    exit();
}

//untuk mengambil jumlah total pasien dari tabel pasien di database, lalu menyimpannya dalam variabel $total_pasien
$query_total_pasien = "SELECT COUNT(*) as total FROM pasien";
$stmt_total_pasien = mysqli_prepare($conn, $query_total_pasien);
mysqli_stmt_execute($stmt_total_pasien);
$result_total_pasien = mysqli_stmt_get_result($stmt_total_pasien);
$total_pasien = mysqli_fetch_assoc($result_total_pasien)['total'];
mysqli_stmt_close($stmt_total_pasien);

//untuk menghitung jumlah total baris (data dokter) yang ada di tabel dokter, lalu menyimpannya ke dalam variabel $total_dokter.
$query_total_dokter = "SELECT COUNT(*) as total FROM dokter";
$stmt_total_dokter = mysqli_prepare($conn, $query_total_dokter);
mysqli_stmt_execute($stmt_total_dokter);
$result_total_dokter = mysqli_stmt_get_result($stmt_total_dokter);
$total_dokter = mysqli_fetch_assoc($result_total_dokter)['total'];
mysqli_stmt_close($stmt_total_dokter);

//Untuk menghitung jumlah seluruh data obat yang ada di tabel obat, dan menyimpannya ke dalam variabel $total_obat.
$query_total_obat = "SELECT COUNT(*) as total FROM obat";
$stmt_total_obat = mysqli_prepare($conn, $query_total_obat);
mysqli_stmt_execute($stmt_total_obat);
$result_total_obat = mysqli_stmt_get_result($stmt_total_obat);
$total_obat = mysqli_fetch_assoc($result_total_obat)['total'];
mysqli_stmt_close($stmt_total_obat);

// Ambil data pasien
$query_pasien = "SELECT id_pasien, nama, tanggal_lahir, jenis_kelamin, alamat, no_hp 
                 FROM pasien 
                 ORDER BY id_pasien";
$stmt_pasien = mysqli_prepare($conn, $query_pasien);
mysqli_stmt_execute($stmt_pasien);
$result_pasien = mysqli_stmt_get_result($stmt_pasien);

// Ambil data dokter
$query_dokter = "SELECT id_dokter, nama, spesialisasi, nomor_telepon 
                 FROM dokter 
                 ORDER BY id_dokter";
$stmt_dokter = mysqli_prepare($conn, $query_dokter);
mysqli_stmt_execute($stmt_dokter);
$result_dokter = mysqli_stmt_get_result($stmt_dokter);

// Ambil data obat
$query_obat = "SELECT id_obat, nama_obat, dosis, harga 
               FROM obat 
               ORDER BY id_obat";
$stmt_obat = mysqli_prepare($conn, $query_obat);
mysqli_stmt_execute($stmt_obat);
$result_obat = mysqli_stmt_get_result($stmt_obat);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Petugas - Rekam Medis Klinik</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">
    <nav class="bg-white shadow-lg rounded-b-lg">
        <div class="container mx-auto px-4 sm:px-6 py-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-xl sm:text-2xl font-bold text-blue-600">🩺 Dashboard Petugas</h1>
                    <p class="text-sm sm:text-base text-gray-600">Selamat datang, <b class="text-blue-600"><?php echo htmlspecialchars(ucwords(strtolower($_SESSION['nama']))); ?></b></p>
                </div>
                <div class="flex space-x-3">
                    <form method="POST">
                        <button type="submit" name="logout" class="bg-red-600 hover:bg-red-700 text-white font-semibold py-1.5 sm:py-2 px-4 sm:px-6 rounded-xl transition duration-300 text-sm sm:text-base shadow">
                            Logout
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </nav>

    <main class="container mx-auto p-4 sm:p-6">
        <?php if (isset($_SESSION['flash_message'])): ?>
            <div class="bg-<?php echo $_SESSION['flash_message']['type'] === 'success' ? 'green' : 'red'; ?>-100 border-l-4 border-<?php echo $_SESSION['flash_message']['type'] === 'success' ? 'green' : 'red'; ?>-500 text-gray-700 p-3 sm:p-4 rounded-lg mb-3 sm:mb-4 text-sm sm:text-base">
                <?php echo htmlspecialchars($_SESSION['flash_message']['message']); ?>
            </div>
            <?php unset($_SESSION['flash_message']); ?>
        <?php endif; ?>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 sm:gap-6 mb-8 sm:mb-10">
            <div class="bg-white p-4 sm:p-6 rounded-xl shadow-lg hover:shadow-xl transition-shadow">
                <h2 class="text-base sm:text-lg font-semibold text-gray-700">👤 Total Pasien</h2>
                <p class="text-2xl sm:text-3xl font-bold text-blue-600"><?php echo $total_pasien; ?></p>
            </div>
            <div class="bg-white p-4 sm:p-6 rounded-xl shadow-lg hover:shadow-xl transition-shadow">
                <h2 class="text-base sm:text-lg font-semibold text-gray-700">🩺 Total Dokter</h2>
                <p class="text-2xl sm:text-3xl font-bold text-green-600"><?php echo $total_dokter; ?></p>
            </div>
            <div class="bg-white p-4 sm:p-6 rounded-xl shadow-lg hover:shadow-xl transition-shadow">
                <h2 class="text-base sm:text-lg font-semibold text-gray-700">💉 Total Obat</h2>
                <p class="text-2xl sm:text-3xl font-bold text-teal-600"><?php echo $total_obat; ?></p>
            </div>
        </div>

        <div class="space-y-8">
            <!-- Data Pasien -->
            <div>
                <div class="flex justify-between items-center mb-4 sm:mb-6">
                    <h2 class="text-xl sm:text-2xl font-semibold text-blue-600">📋 Data Pasien</h2>
                    <a href="tambah_pasien.php" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-1.5 sm:py-2 px-3 sm:px-4 rounded-xl transition duration-300 text-sm sm:text-base shadow">
                        Tambah Pasien
                    </a>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full bg-white rounded-xl shadow-lg">
                        <thead>
                            <tr class="bg-blue-100 text-left">
                                <th class="py-2 sm:py-3 px-1 sm:px-4 font-medium text-xs sm:text-base text-gray-700">ID</th>
                                <th class="py-2 sm:py-3 px-1 sm:px-4 font-medium text-xs sm:text-base text-gray-700">Nama</th>
                                <th class="py-2 sm:py-3 px-1 sm:px-4 font-medium text-xs sm:text-base text-gray-700">Tanggal Lahir</th>
                                <th class="py-2 sm:py-3 px-1 sm:px-4 font-medium text-xs sm:text-base text-gray-700">Jenis Kelamin</th>
                                <th class="py-2 sm:py-3 px-1 sm:px-4 font-medium text-xs sm:text-base text-gray-700">Alamat</th>
                                <th class="py-2 sm:py-3 px-1 sm:px-4 font-medium text-xs sm:text-base text-gray-700">No. HP</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (mysqli_num_rows($result_pasien) > 0): ?>
                                <?php while ($row = mysqli_fetch_assoc($result_pasien)): ?>
                                    <tr class="border-t border-gray-200 hover:bg-gray-50">
                                        <td class="py-2 sm:py-3 px-1 sm:px-4 text-xs sm:text-base text-gray-700 whitespace-nowrap"><?php echo htmlspecialchars($row['id_pasien']); ?></td>
                                        <td class="py-2 sm:py-3 px-1 sm:px-4 text-xs sm:text-base text-gray-700 whitespace-nowrap"><?php echo htmlspecialchars(ucwords(strtolower($row['nama']))); ?></td>
                                        <td class="py-2 sm:py-3 px-1 sm:px-4 text-xs sm:text-base text-gray-700 whitespace-nowrap"><?php echo htmlspecialchars($row['tanggal_lahir']); ?></td>
                                        <td class="py-2 sm:py-3 px-1 sm:px-4 text-xs sm:text-base text-gray-700 whitespace-nowrap"><?php echo htmlspecialchars($row['jenis_kelamin']); ?></td>
                                        <td class="py-2 sm:py-3 px-1 sm:px-4 text-xs sm:text-base text-gray-700 whitespace-nowrap"><?php echo htmlspecialchars($row['alamat']); ?></td>
                                        <td class="py-2 sm:py-3 px-1 sm:px-4 text-xs sm:text-base text-gray-700 whitespace-nowrap"><?php echo htmlspecialchars($row['no_hp']); ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="py-2 sm:py-3 px-1 sm:px-4 text-center text-gray-500 text-xs sm:text-base">Belum ada data pasien.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Data Dokter -->
            <div>
                <div class="flex justify-between items-center mb-4 sm:mb-6">
                    <h2 class="text-xl sm:text-2xl font-semibold text-green-600">🩺 Data Dokter</h2>
                    <a href="tambah_dokter.php" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-1.5 sm:py-2 px-3 sm:px-4 rounded-xl transition duration-300 text-sm sm:text-base shadow">
                        Tambah Dokter
                    </a>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full bg-white rounded-xl shadow-lg">
                        <thead>
                            <tr class="bg-green-100 text-left">
                                <th class="py-2 sm:py-3 px-1 sm:px-4 font-medium text-xs sm:text-base text-gray-700">ID</th>
                                <th class="py-2 sm:py-3 px-1 sm:px-4 font-medium text-xs sm:text-base text-gray-700">Nama</th>
                                <th class="py-2 sm:py-3 px-1 sm:px-4 font-medium text-xs sm:text-base text-gray-700">Spesialisasi</th>
                                <th class="py-2 sm:py-3 px-1 sm:px-4 font-medium text-xs sm:text-base text-gray-700">No. Telepon</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (mysqli_num_rows($result_dokter) > 0): ?>
                                <?php while ($row = mysqli_fetch_assoc($result_dokter)): ?>
                                    <tr class="border-t border-gray-200 hover:bg-gray-50">
                                        <td class="py-2 sm:py-3 px-1 sm:px-4 text-xs sm:text-base text-gray-700 whitespace-nowrap"><?php echo htmlspecialchars($row['id_dokter']); ?></td>
                                        <td class="py-2 sm:py-3 px-1 sm:px-4 text-xs sm:text-base text-gray-700 whitespace-nowrap"><?php echo htmlspecialchars(ucwords(strtolower($row['nama']))); ?></td>
                                        <td class="py-2 sm:py-3 px-1 sm:px-4 text-xs sm:text-base text-gray-700 whitespace-nowrap"><?php echo htmlspecialchars($row['spesialisasi']); ?></td>
                                        <td class="py-2 sm:py-3 px-1 sm:px-4 text-xs sm:text-base text-gray-700 whitespace-nowrap"><?php echo htmlspecialchars($row['nomor_telepon']); ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="py-2 sm:py-3 px-1 sm:px-4 text-center text-gray-500 text-xs sm:text-base">Belum ada data dokter.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Data Obat -->
            <div>
                <div class="flex justify-between items-center mb-4 sm:mb-6">
                    <h2 class="text-xl sm:text-2xl font-semibold text-teal-600">💉 Data Obat</h2>
                    <a href="tambah_obat.php" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-1.5 sm:py-2 px-3 sm:px-4 rounded-xl transition duration-300 text-sm sm:text-base shadow">
                        Tambah Obat
                    </a>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full bg-white rounded-xl shadow-lg">
                        <thead>
                            <tr class="bg-teal-100 text-left">
                                <th class="py-2 sm:py-3 px-1 sm:px-4 font-medium text-xs sm:text-base text-gray-700">ID Obat</th>
                                <th class="py-2 sm:py-3 px-1 sm:px-4 font-medium text-xs sm:text-base text-gray-700">Nama Obat</th>
                                <th class="py-2 sm:py-3 px-1 sm:px-4 font-medium text-xs sm:text-base text-gray-700">Dosis</th>
                                <th class="py-2 sm:py-3 px-1 sm:px-4 font-medium text-xs sm:text-base text-gray-700">Harga</th>
                            </tr>
                        </thead>
                        <!-- untuk menampilkan semua data obat yang ada di data base -->
                        <tbody>
                            <?php if (mysqli_num_rows($result_obat) > 0): ?>
                                <?php while ($row = mysqli_fetch_assoc($result_obat)): ?>
                                    <tr class="border-t border-gray-200 hover:bg-gray-50">
                                        <td class="py-2 sm:py-3 px-1 sm:px-4 text-xs sm:text-base text-gray-700 whitespace-nowrap"><?php echo htmlspecialchars($row['id_obat']); ?></td>
                                        <td class="py-2 sm:py-3 px-1 sm:px-4 text-xs sm:text-base text-gray-700 whitespace-nowrap"><?php echo htmlspecialchars($row['nama_obat']); ?></td>
                                        <td class="py-2 sm:py-3 px-1 sm:px-4 text-xs sm:text-base text-gray-700 whitespace-nowrap"><?php echo htmlspecialchars($row['dosis']); ?></td>
                                        <td class="py-2 sm:py-3 px-1 sm:px-4 text-xs sm:text-base text-gray-700 whitespace-nowrap">
                                        Rp.<?php echo number_format($row['harga'], 2, ',', '.'); ?>
                                        </td><!--  menggunakan fungsi bawaan php number_format untuk memunculkan format 2 desimal dibelakang koma -->
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="3" class="py-2 sm:py-3 px-1 sm:px-4 text-center text-gray-500 text-xs sm:text-base">Belum ada data obat.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
</body>
</html>

<?php
// Tutup statement dan koneksi
mysqli_stmt_close($stmt_pasien);
mysqli_stmt_close($stmt_dokter);
mysqli_stmt_close($stmt_obat);
mysqli_close($conn);
?>