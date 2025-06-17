<?php
session_start();
include 'koneksi.php';

// Cek apakah pengguna sudah login dan memiliki role dokter (role = 1)
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] != 1) {
    $_SESSION['flash_message'] = [
        'type' => 'error',
        'message' => 'Anda harus login sebagai dokter untuk mengakses halaman ini!'
    ];
    header("Location: login.php");
    exit();
}

// Proses logout
if (isset($_POST['logout'])) {
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit();
}

// Ambil ID dokter dari sesi
$id_dokter = $_SESSION['user_id'];

// Ambil total data untuk kartu dashboard menggunakan prepared statement
$query_total_pasien = "SELECT COUNT(DISTINCT p.id_pasien) as total FROM pasien p JOIN rekam_medis rm ON p.id_pasien = rm.id_pasien WHERE rm.id_dokter = ?";
$stmt_total_pasien = mysqli_prepare($conn, $query_total_pasien);
mysqli_stmt_bind_param($stmt_total_pasien, "i", $id_dokter);
mysqli_stmt_execute($stmt_total_pasien);
$result_total_pasien = mysqli_stmt_get_result($stmt_total_pasien);
$total_pasien = mysqli_fetch_assoc($result_total_pasien)['total'];
mysqli_stmt_close($stmt_total_pasien);

$query_total_rekam = "SELECT COUNT(*) as total FROM rekam_medis WHERE id_dokter = ?";
$stmt_total_rekam = mysqli_prepare($conn, $query_total_rekam);
mysqli_stmt_bind_param($stmt_total_rekam, "i", $id_dokter);
mysqli_stmt_execute($stmt_total_rekam);
$result_total_rekam = mysqli_stmt_get_result($stmt_total_rekam);
$total_rekam_medis = mysqli_fetch_assoc($result_total_rekam)['total'];
mysqli_stmt_close($stmt_total_rekam);

$query_total_resep = "SELECT COUNT(*) as total FROM resep r JOIN rekam_medis rm ON r.id_rekam = rm.id_rekam WHERE rm.id_dokter = ?";
$stmt_total_resep = mysqli_prepare($conn, $query_total_resep);
mysqli_stmt_bind_param($stmt_total_resep, "i", $id_dokter);
mysqli_stmt_execute($stmt_total_resep);
$result_total_resep = mysqli_stmt_get_result($stmt_total_resep);
$total_resep = mysqli_fetch_assoc($result_total_resep)['total'];
mysqli_stmt_close($stmt_total_resep);

// Ambil data pasien beserta detail rekam medis, resep, dan tindakan
$query_pasien = "
    SELECT DISTINCT p.id_pasien, p.nama, p.tanggal_lahir, p.jenis_kelamin, p.alamat, p.no_hp 
    FROM pasien p 
    JOIN rekam_medis rm ON p.id_pasien = rm.id_pasien 
    WHERE rm.id_dokter = ? 
    ORDER BY p.nama";
$stmt_pasien = mysqli_prepare($conn, $query_pasien);
mysqli_stmt_bind_param($stmt_pasien, "i", $id_dokter);
mysqli_stmt_execute($stmt_pasien);
$result_pasien = mysqli_stmt_get_result($stmt_pasien);

// Fungsi untuk mengambil detail rekam medis, resep, dan tindakan per pasien
function getPatientDetails($conn, $id_pasien, $id_dokter) {
    $details = ['rekam_medis' => [], 'resep' => [], 'tindakan' => []];

    // Rekam Medis
    $query_rekam = "SELECT rm.id_rekam, rm.tanggal, rm.diagnosa 
                    FROM rekam_medis rm 
                    WHERE rm.id_pasien = ? AND rm.id_dokter = ? 
                    ORDER BY rm.tanggal DESC";
    $stmt_rekam = mysqli_prepare($conn, $query_rekam);
    mysqli_stmt_bind_param($stmt_rekam, "ii", $id_pasien, $id_dokter);
    mysqli_stmt_execute($stmt_rekam);
    $result_rekam = mysqli_stmt_get_result($stmt_rekam);
    while ($row = mysqli_fetch_assoc($result_rekam)) {
        $details['rekam_medis'][] = $row;
    }
    mysqli_stmt_close($stmt_rekam);

    // Resep
    $query_resep = "SELECT r.id_resep, o.nama_obat, o.dosis, r.jumlah 
                    FROM resep r 
                    JOIN rekam_medis rm ON r.id_rekam = rm.id_rekam 
                    JOIN obat o ON r.id_obat = o.id_obat 
                    WHERE rm.id_pasien = ? AND rm.id_dokter = ? 
                    ORDER BY r.id_resep";
    $stmt_resep = mysqli_prepare($conn, $query_resep);
    mysqli_stmt_bind_param($stmt_resep, "ii", $id_pasien, $id_dokter);
    mysqli_stmt_execute($stmt_resep);
    $result_resep = mysqli_stmt_get_result($stmt_resep);
    while ($row = mysqli_fetch_assoc($result_resep)) {
        $details['resep'][] = $row;
    }
    mysqli_stmt_close($stmt_resep);

    // Tindakan Medis
    $query_tindakan = "SELECT tm.id_tindakan, tm.nama_tindakan, tm.biaya 
                       FROM tindakan_medis tm 
                       JOIN rekam_medis rm ON tm.id_rekam = rm.id_rekam 
                       WHERE rm.id_pasien = ? AND rm.id_dokter = ? 
                       ORDER BY tm.id_tindakan";
    $stmt_tindakan = mysqli_prepare($conn, $query_tindakan);
    mysqli_stmt_bind_param($stmt_tindakan, "ii", $id_pasien, $id_dokter);
    mysqli_stmt_execute($stmt_tindakan);
    $result_tindakan = mysqli_stmt_get_result($stmt_tindakan);
    while ($row = mysqli_fetch_assoc($result_tindakan)) {
        $details['tindakan'][] = $row;
    }
    mysqli_stmt_close($stmt_tindakan);

    return $details;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Dokter - Rekam Medis Klinik</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        function toggleAccordion(id) {
            const element = document.getElementById(id);
            const isExpanded = element.classList.contains('hidden');
            document.querySelectorAll('.accordion-content').forEach(content => {
                content.classList.add('hidden');
            });
            if (isExpanded) {
                element.classList.remove('hidden');
            }
        }

        function confirmDelete(id) {
            if (confirm('Apakah Anda yakin ingin menghapus data pasien ini?')) {
                window.location.href = 'hapus_pasien.php?id=' + id;
            }
        }
    </script>
</head>
<body class="bg-gray-100 min-h-screen">
    <nav class="bg-white shadow p-4">
        <div class="container mx-auto flex justify-between items-center">
            <div>
                <h1 class="text-xl sm:text-2xl font-bold text-blue-600">🩺 Dashboard Dokter</h1>
                <p class="text-sm sm:text-base text-gray-500">Selamat datang, <strong><?php echo htmlspecialchars(ucwords(strtolower($_SESSION['nama']))); ?></strong></p>
            </div>
            <form method="POST">
                <button type="submit" name="logout" class="bg-red-600 hover:bg-red-700 text-white font-semibold py-1 sm:py-2 px-3 sm:px-4 rounded-xl transition duration-200 text-sm sm:text-base">
                    Logout
                </button>
            </form>
        </div>
    </nav>

    <main class="container mx-auto p-4 sm:p-6">
        <?php if (isset($_SESSION['flash_message'])): ?>
            <div class="bg-<?php echo $_SESSION['flash_message']['type'] === 'success' ? 'green' : 'red'; ?>-100 border-l-4 border-<?php echo $_SESSION['flash_message']['type'] === 'success' ? 'green' : 'red'; ?>-500 text-<?php echo $_SESSION['flash_message']['type'] === 'success' ? 'green' : 'red'; ?>-700 p-4 sm:p-6 mb-4 sm:mb-6 text-sm sm:text-base">
                <?php echo htmlspecialchars($_SESSION['flash_message']['message']); ?>
            </div>
            <?php unset($_SESSION['flash_message']); ?>
        <?php endif; ?>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 sm:gap-6 mb-8 sm:mb-10">
            <div class="bg-white p-4 sm:p-6 rounded-2xl shadow hover:shadow-lg">
                <h2 class="text-base sm:text-lg font-semibold text-gray-700">👤 Total Pasien</h2>
                <p class="text-2xl sm:text-3xl font-bold text-blue-500"><?php echo $total_pasien; ?></p>
            </div>
            <div class="bg-white p-4 sm:p-6 rounded-2xl shadow hover:shadow-lg">
                <h2 class="text-base sm:text-lg font-semibold text-gray-700">📄 Total Rekam Medis</h2>
                <p class="text-2xl sm:text-3xl font-bold text-red-500"><?php echo $total_rekam_medis; ?></p>
            </div>
            <div class="bg-white p-4 sm:p-6 rounded-2xl shadow hover:shadow-lg">
                <h2 class="text-base sm:text-lg font-semibold text-gray-700">💊 Total Resep</h2>
                <p class="text-2xl sm:text-3xl font-bold text-yellow-500"><?php echo $total_resep; ?></p>
            </div>
        </div>

        <div class="flex justify-between items-center mb-4 sm:mb-6">
            <h2 class="text-xl sm:text-2xl font-semibold text-blue-600">📋 Data Pasien dan Detail</h2>
            <a href="tambah_diagnosa.php" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-1 sm:py-2 px-3 sm:px-4 rounded-xl transition duration-200 text-sm sm:text-base">
                + Tambah Pasien
            </a>
        </div>

        <div class="space-y-4">
            <?php if (mysqli_num_rows($result_pasien) > 0): ?>
                <?php while ($row = mysqli_fetch_assoc($result_pasien)): ?>
                    <?php $details = getPatientDetails($conn, $row['id_pasien'], $id_dokter); ?>
                    <div class="bg-white rounded-xl shadow">
                        <div class="flex justify-between items-center w-full text-left p-3 sm:p-4 bg-blue-50 hover:bg-blue-100 rounded-t-xl">
                            <button onclick="toggleAccordion('patient-<?php echo $row['id_pasien']; ?>')" class="flex-1 flex justify-between items-center">
                                <span class="font-semibold text-sm sm:text-lg truncate">
                                    <?php echo htmlspecialchars(ucwords(strtolower($row['nama']))); ?> 
                                    (ID: <?php echo $row['id_pasien']; ?>) 
                                    - <?php echo htmlspecialchars($row['alamat']); ?>
                                </span>
                                <svg class="w-4 sm:w-5 h-4 sm:h-5 transform transition-transform" id="arrow-<?php echo $row['id_pasien']; ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>
                            <button onclick="confirmDelete(<?php echo $row['id_pasien']; ?>)" class="bg-red-600 hover:bg-red-700 text-white font-semibold py-1 px-3 rounded-xl text-sm sm:text-base ml-2">
                                Hapus
                            </button>
                        </div>
                        <div id="patient-<?php echo $row['id_pasien']; ?>" class="accordion-content hidden p-3 sm:p-4">
                            <!-- Informasi Pasien -->
                            <div class="mb-4 sm:mb-6">
                                <h3 class="text-base sm:text-lg font-semibold text-gray-700 mb-2">Informasi Pasien</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 sm:gap-4 text-sm sm:text-base">
                                    <p><strong>Tanggal Lahir:</strong> <?php echo htmlspecialchars($row['tanggal_lahir']); ?></p>
                                    <p><strong>Jenis Kelamin:</strong> <?php echo htmlspecialchars($row['jenis_kelamin']); ?></p>
                                    <p><strong>Alamat:</strong> <?php echo htmlspecialchars($row['alamat']); ?></p>
                                    <p><strong>No. HP:</strong> <?php echo htmlspecialchars($row['no_hp']); ?></p>
                                </div>
                            </div>

                            <!-- Rekam Medis -->
                            <div class="mb-4 sm:mb-6">
                                <h3 class="text-base sm:text-lg font-semibold text-red-600 mb-2">Rekam Medis</h3>
                                <?php if (!empty($details['rekam_medis'])): ?>
                                    <div class="overflow-x-auto">
                                        <table class="min-w-full bg-white rounded-xl">
                                            <thead>
                                                <tr class="bg-red-100 text-left">
                                                    <th class="py-1 sm:py-2 px-2 sm:px-4 font-medium text-sm sm:text-base">ID</th>
                                                    <th class="py-1 sm:py-2 px-2 sm:px-4 font-medium text-sm sm:text-base">Tanggal</th>
                                                    <th class="py-1 sm:py-2 px-2 sm:px-4 font-medium text-sm sm:text-base">Diagnosa</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($details['rekam_medis'] as $rekam): ?>
                                                    <tr class="border-t hover:bg-gray-50">
                                                        <td class="py-1 sm:py-2 px-2 sm:px-4 text-sm sm:text-base"><?php echo htmlspecialchars($rekam['id_rekam']); ?></td>
                                                        <td class="py-1 sm:py-2 px-2 sm:px-4 text-sm sm:text-base"><?php echo htmlspecialchars($rekam['tanggal']); ?></td>
                                                        <td class="py-1 sm:py-2 px-2 sm:px-4 text-sm sm:text-base"><?php echo htmlspecialchars($rekam['diagnosa']); ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php else: ?>
                                    <p class="text-gray-500 text-sm sm:text-base">Belum ada rekam medis.</p>
                                <?php endif; ?>
                            </div>

                            <!-- Resep -->
                            <div class="mb-4 sm:mb-6">
                                <h3 class="text-base sm:text-lg font-semibold text-yellow-600 mb-2">Resep</h3>
                                <?php if (!empty($details['resep'])): ?>
                                    <div class="overflow-x-auto">
                                        <table class="min-w-full bg-white rounded-xl">
                                            <thead>
                                                <tr class="bg-yellow-100 text-left">
                                                    <th class="py-1 sm:py-2 px-2 sm:px-4 font-medium text-sm sm:text-base">ID Resep</th>
                                                    <th class="py-1 sm:py-2 px-2 sm:px-4 font-medium text-sm sm:text-base">Nama Obat</th>
                                                    <th class="py-1 sm:py-2 px-2 sm:px-4 font-medium text-sm sm:text-base">Dosis</th>
                                                    <th class="py-1 sm:py-2 px-2 sm:px-4 font-medium text-sm sm:text-base">Jumlah</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($details['resep'] as $resep): ?>
                                                    <tr class="border-t hover:bg-gray-50">
                                                        <td class="py-1 sm:py-2 px-2 sm:px-4 text-sm sm:text-base"><?php echo htmlspecialchars($resep['id_resep']); ?></td>
                                                        <td class="py-1 sm:py-2 px-2 sm:px-4 text-sm sm:text-base"><?php echo htmlspecialchars($resep['nama_obat']); ?></td>
                                                        <td class="py-1 sm:py-2 px-2 sm:px-4 text-sm sm:text-base"><?php echo htmlspecialchars($resep['dosis']); ?></td>
                                                        <td class="py-1 sm:py-2 px-2 sm:px-4 text-sm sm:text-base"><?php echo htmlspecialchars($resep['jumlah']); ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php else: ?>
                                    <p class="text-gray-500 text-sm sm:text-base">Belum ada resep.</p>
                                <?php endif; ?>
                            </div>

                            <!-- Tindakan Medis -->
                            <div>
                                <h3 class="text-base sm:text-lg font-semibold text-purple-600 mb-2">Tindakan Medis</h3>
                                <?php if (!empty($details['tindakan'])): ?>
                                    <div class="overflow-x-auto">
                                        <table class="min-w-full bg-white rounded-xl">
                                            <thead>
                                                <tr class="bg-purple-100 text-left">
                                                    <th class="py-1 sm:py-2 px-2 sm:px-4 font-medium text-sm sm:text-base">ID Tindakan</th>
                                                    <th class="py-1 sm:py-2 px-2 sm:px-4 font-medium text-sm sm:text-base">Nama Tindakan</th>
                                                    <th class="py-1 sm:py-2 px-2 sm:px-4 font-medium text-sm sm:text-base">Biaya</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($details['tindakan'] as $tindakan): ?>
                                                    <tr class="border-t hover:bg-gray-50">
                                                        <td class="py-1 sm:py-2 px-2 sm:px-4 text-sm sm:text-base"><?php echo htmlspecialchars($tindakan['id_tindakan']); ?></td>
                                                        <td class="py-1 sm:py-2 px-2 sm:px-4 text-sm sm:text-base"><?php echo htmlspecialchars($tindakan['nama_tindakan']); ?></td>
                                                        <td class="py-1 sm:py-2 px-2 sm:px-4 text-sm sm:text-base"><?php echo number_format($tindakan['biaya'], 2, ',', '.'); ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php else: ?>
                                    <p class="text-gray-500 text-sm sm:text-base">Belum ada tindakan medis.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p class="text-gray-500 text-center text-sm sm:text-base">Belum ada data pasien.</p>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>

<?php
// Tutup statement dan koneksi
mysqli_stmt_close($stmt_pasien);
mysqli_close($conn);
?>