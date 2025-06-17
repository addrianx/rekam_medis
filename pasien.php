<?php
session_start();
include 'koneksi.php';

// Cek apakah pengguna sudah login dan memiliki role pasien (role = 3)
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] != 3) {
    $_SESSION['flash_message'] = [
        'type' => 'error',
        'message' => 'Anda harus login sebagai pasien untuk mengakses halaman ini!'
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

// Ambil ID pasien dari sesi
$id_pasien = $_SESSION['user_id'];

// Ambil informasi pribadi pasien
$query_pasien = "SELECT nama, tanggal_lahir, jenis_kelamin, alamat, no_hp FROM pasien WHERE id_pasien = ?";
$stmt_pasien = mysqli_prepare($conn, $query_pasien);
mysqli_stmt_bind_param($stmt_pasien, "i", $id_pasien);
mysqli_stmt_execute($stmt_pasien);
$result_pasien = mysqli_stmt_get_result($stmt_pasien);
$pasien = mysqli_fetch_assoc($result_pasien);

// Ambil total data untuk kartu dashboard
$query_total_rekam = "SELECT COUNT(*) as total FROM rekam_medis WHERE id_pasien = ?";
$stmt_total_rekam = mysqli_prepare($conn, $query_total_rekam);
mysqli_stmt_bind_param($stmt_total_rekam, "i", $id_pasien);
mysqli_stmt_execute($stmt_total_rekam);
$total_rekam_medis = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_total_rekam))['total'];
mysqli_stmt_close($stmt_total_rekam);

$query_total_resep = "SELECT COUNT(*) as total FROM resep r JOIN rekam_medis rm ON r.id_rekam = rm.id_rekam WHERE rm.id_pasien = ?";
$stmt_total_resep = mysqli_prepare($conn, $query_total_resep);
mysqli_stmt_bind_param($stmt_total_resep, "i", $id_pasien);
mysqli_stmt_execute($stmt_total_resep);
$total_resep = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_total_resep))['total'];
mysqli_stmt_close($stmt_total_resep);

$query_total_tindakan = "SELECT COUNT(*) as total FROM tindakan_medis tm JOIN rekam_medis rm ON tm.id_rekam = rm.id_rekam WHERE rm.id_pasien = ?";
$stmt_total_tindakan = mysqli_prepare($conn, $query_total_tindakan);
mysqli_stmt_bind_param($stmt_total_tindakan, "i", $id_pasien);
mysqli_stmt_execute($stmt_total_tindakan);
$total_tindakan = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_total_tindakan))['total'];
mysqli_stmt_close($stmt_total_tindakan);

// Ambil data rekam medis
$query_rekam = "SELECT rm.id_rekam, rm.tanggal, rm.diagnosa, d.nama AS nama_dokter 
                FROM rekam_medis rm JOIN dokter d ON rm.id_dokter = d.id_dokter 
                WHERE rm.id_pasien = ? ORDER BY rm.tanggal DESC";
$stmt_rekam = mysqli_prepare($conn, $query_rekam);
mysqli_stmt_bind_param($stmt_rekam, "i", $id_pasien);
mysqli_stmt_execute($stmt_rekam);
$result_rekam = mysqli_stmt_get_result($stmt_rekam);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Agus</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">
    <nav class="bg-white shadow-lg rounded-b-lg">
        <div class="container mx-auto px-4 sm:px-6 py-3 sm:py-4">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-lg sm:text-xl md:text-2xl font-bold text-blue-600">🩺 Dashboard Pasien</h1>
                    <p class="text-xs sm:text-sm md:text-base text-gray-600">Selamat datang, <span class="text-blue-500"><?php echo htmlspecialchars(ucwords(strtolower($_SESSION['nama']))); ?></span></p>
                </div>
                <form method="POST">
                    <button type="submit" name="logout" class="bg-red-600 hover:bg-red-700 text-white font-semibold py-1.5 sm:py-2 px-3 sm:px-4 rounded-xl transition duration-200 text-xs sm:text-sm md:text-base">
                        Logout
                    </button>
                </form>
            </div>
        </div>
    </nav>

    <main class="container mx-auto p-4 sm:p-6">
        <!-- Kartu Dashboard -->
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 sm:gap-6 mb-8 sm:mb-10">
            <div class="bg-white p-4 sm:p-6 rounded-xl shadow hover:shadow-lg transition">
                <h2 class="text-sm sm:text-base font-semibold text-gray-700">📄 Total Rekam Medis</h2>
                <p class="text-2xl sm:text-3xl font-bold text-red-500"><?php echo $total_rekam_medis; ?></p>
            </div>
            <div class="bg-white p-4 sm:p-6 rounded-xl shadow hover:shadow-lg transition">
                <h2 class="text-sm sm:text-base font-semibold text-gray-700">💊 Total Resep</h2>
                <p class="text-2xl sm:text-3xl font-bold text-yellow-500"><?php echo $total_resep; ?></p>
            </div>
            <div class="bg-white p-4 sm:p-6 rounded-xl shadow hover:shadow-lg transition">
                <h2 class="text-sm sm:text-base font-semibold text-gray-700">🩺 Total Tindakan</h2>
                <p class="text-2xl sm:text-3xl font-bold text-purple-500"><?php echo $total_tindakan; ?></p>
            </div>
        </div>

        <!-- Informasi Pribadi -->
        <div class="bg-white p-4 sm:p-6 rounded-xl shadow mb-8 sm:mb-10">
            <h2 class="text-lg sm:text-xl md:text-2xl font-semibold mb-4 text-blue-600">👤 Informasi Pribadi</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs sm:text-sm md:text-base">
                <div>
                    <p class="text-gray-600"><strong>Nama:</strong> <?php echo htmlspecialchars($pasien['nama']); ?></p>
                    <p class="text-gray-600"><strong>Tanggal Lahir:</strong> <?php echo htmlspecialchars($pasien['tanggal_lahir']); ?></p>
                </div>
                <div>
                    <p class="text-gray-600"><strong>Jenis Kelamin:</strong> <?php echo htmlspecialchars($pasien['jenis_kelamin']); ?></p>
                    <p class="text-gray-600"><strong>No. HP:</strong> <?php echo htmlspecialchars($pasien['no_hp']); ?></p>
                </div>
                <div class="sm:col-span-2">
                    <p class="text-gray-600"><strong>Alamat:</strong> <?php echo htmlspecialchars($pasien['alamat']); ?></p>
                </div>
            </div>
        </div>

        <!-- Riwayat Medis -->
        <div class="bg-white p-4 sm:p-6 rounded-xl shadow">
            <h2 class="text-lg sm:text-xl md:text-2xl font-semibold mb-4 text-blue-600">📋 Riwayat Medis Anda</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white rounded-xl shadow">
                    <thead>
                        <tr class="bg-blue-100 text-left">
                            <th class="py-2 sm:py-3 px-1 sm:px-3 font-semibold text-xs sm:text-sm md:text-base text-gray-700">📅 No. Rekam</th>
                            <th class="py-2 sm:py-3 px-1 sm:px-3 font-semibold text-xs sm:text-sm md:text-base text-gray-700">📅 Tanggal</th>
                            <th class="py-2 sm:py-3 px-1 sm:px-3 font-semibold text-xs sm:text-sm md:text-base text-gray-700">👨‍⚕️ Dokter</th>
                            <th class="py-2 sm:py-3 px-1 sm:px-3 font-semibold text-xs sm:text-sm md:text-base text-gray-700">Diagnosa</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($result_rekam) > 0): ?>
                            <?php while ($row_rekam = mysqli_fetch_assoc($result_rekam)): ?>
                                <!-- Baris Rekam Medis -->
                                <tr class="border-t border-gray-200 bg-blue-50">
                                    <td class="py-2 sm:py-3 px-1 sm:px-3 text-xs sm:text-sm md:text-base font-semibold text-gray-700 whitespace-nowrap"><?php echo htmlspecialchars($row_rekam['id_rekam']); ?></td>
                                    <td class="py-2 sm:py-3 px-1 sm:px-3 text-xs sm:text-sm md:text-base font-semibold text-gray-700 whitespace-nowrap"><?php echo htmlspecialchars($row_rekam['tanggal']); ?></td>
                                    <td class="py-2 sm:py-3 px-1 sm:px-3 text-xs sm:text-sm md:text-base font-semibold text-gray-700 whitespace-nowrap"><?php echo htmlspecialchars($row_rekam['nama_dokter']); ?></td>
                                    <td class="py-2 sm:py-3 px-1 sm:px-3 text-xs sm:text-sm md:text-base font-semibold text-gray-700 whitespace-nowrap"><?php echo htmlspecialchars($row_rekam['diagnosa']); ?></td>
                                </tr>
                                <!-- Resep terkait -->
                                <?php
                                $id_rekam = $row_rekam['id_rekam'];
                                $query_resep = "SELECT o.nama_obat, o.dosis, r.jumlah 
                                               FROM resep r JOIN obat o ON r.id_obat = o.id_obat 
                                               WHERE r.id_rekam = ?";
                                $stmt_resep = mysqli_prepare($conn, $query_resep);
                                mysqli_stmt_bind_param($stmt_resep, "i", $id_rekam);
                                mysqli_stmt_execute($stmt_resep);
                                $result_resep = mysqli_stmt_get_result($stmt_resep);
                                if (mysqli_num_rows($result_resep) > 0):
                                ?>
                                    <tr class="bg-gray-50">
                                        <td colspan="4" class="py-2 sm:py-3 px-1 sm:px-3">
                                            <div class="pl-4 sm:pl-6">
                                                <p class="text-xs sm:text-sm md:text-base font-semibold text-gray-600 mb-2">💊 Resep:</p>
                                                <table class="w-full">
                                                    <thead>
                                                        <tr>
                                                            <th class="py-1 px-1 text-xs sm:text-sm md:text-base text-gray-600 text-left">Obat</th>
                                                            <th class="py-1 px-1 text-xs sm:text-sm md:text-base text-gray-600 text-left">Dosis</th>
                                                            <th class="py-1 px-1 text-xs sm:text-sm md:text-base text-gray-600 text-left">Jumlah</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php while ($row_resep = mysqli_fetch_assoc($result_resep)): ?>
                                                            <tr>
                                                                <td class="py-1 px-1 text-xs sm:text-sm md:text-base text-gray-600 whitespace-nowrap"><?php echo htmlspecialchars($row_resep['nama_obat']); ?></td>
                                                                <td class="py-1 px-1 text-xs sm:text-sm md:text-base text-gray-600 whitespace-nowrap"><?php echo htmlspecialchars($row_resep['dosis']); ?></td>
                                                                <td class="py-1 px-1 text-xs sm:text-sm md:text-base text-gray-600 whitespace-nowrap"><?php echo htmlspecialchars($row_resep['jumlah']); ?></td>
                                                            </tr>
                                                        <?php endwhile; ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endif; mysqli_stmt_close($stmt_resep); ?>
                                <!-- Tindakan Medis terkait -->
                                <?php
                                $query_tindakan = "SELECT tm.nama_tindakan, tm.biaya 
                                                  FROM tindakan_medis tm 
                                                  WHERE tm.id_rekam = ?";
                                $stmt_tindakan = mysqli_prepare($conn, $query_tindakan);
                                mysqli_stmt_bind_param($stmt_tindakan, "i", $id_rekam);
                                mysqli_stmt_execute($stmt_tindakan);
                                $result_tindakan = mysqli_stmt_get_result($stmt_tindakan);
                                if (mysqli_num_rows($result_tindakan) > 0):
                                ?>
                                    <tr class="bg-gray-50">
                                        <td colspan="4" class="py-2 sm:py-3 px-1 sm:px-3">
                                            <div class="pl-4 sm:pl-6">
                                                <p class="text-xs sm:text-sm md:text-base font-semibold text-gray-600 mb-2">🩺 Tindakan Medis:</p>
                                                <table class="w-full">
                                                    <thead>
                                                        <tr>
                                                            <th class="py-1 px-1 text-xs sm:text-sm md:text-base text-gray-600 text-left">Tindakan</th>
                                                            <th class="py-1 px-1 text-xs sm:text-sm md:text-base text-gray-600 text-left">Biaya</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php while ($row_tindakan = mysqli_fetch_assoc($result_tindakan)): ?>
                                                            <tr>
                                                                <td class="py-1 px-1 text-xs sm:text-sm md:text-base text-gray-600 whitespace-nowrap"><?php echo htmlspecialchars($row_tindakan['nama_tindakan']); ?></td>
                                                                <td class="py-1 px-1 text-xs sm:text-sm md:text-base text-gray-600 whitespace-nowrap"><?php echo 'Rp ' . number_format($row_tindakan['biaya'], 2, ',', '.'); ?></td>
                                                            </tr>
                                                        <?php endwhile; ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endif; mysqli_stmt_close($stmt_tindakan); ?>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="py-2 sm:py-3 px-1 sm:px-3 text-center text-gray-500 text-xs sm:text-sm md:text-base">
                                    Belum ada riwayat medis. Silakan konsultasi dengan dokter Anda.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</body>
</html>

<?php
// Tutup statement dan koneksi
mysqli_stmt_close($stmt_pasien);
mysqli_stmt_close($stmt_rekam);
mysqli_close($conn);
?>