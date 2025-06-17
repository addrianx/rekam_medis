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

// Ambil ID dokter dari sesi
$id_dokter = $_SESSION['user_id'];

// Ambil data obat untuk dropdown
$query_obat = "SELECT id_obat, nama_obat FROM obat ORDER BY nama_obat";
$result_obat = mysqli_query($conn, $query_obat);

// Ambil data obat untuk dropdown
$query_pasien = "SELECT id_pasien, nama FROM pasien ORDER BY nama";
$result_pasien = mysqli_query($conn, $query_pasien);

// Proses form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    mysqli_begin_transaction($conn);

    try {
        $id_pasien = $_POST['id_pasien'];
        $tanggal = $_POST['tanggal'];
        $diagnosa = trim($_POST['diagnosa']);
        $id_obat = $_POST['id_obat'];
        $jumlah = $_POST['jumlah'];
        $nama_tindakan = trim($_POST['nama_tindakan']);

        // Simpan rekam medis
        $query_rekam = "INSERT INTO rekam_medis (id_pasien, id_dokter, tanggal, diagnosa) VALUES (?, ?, ?, ?)";
        $stmt_rekam = mysqli_prepare($conn, $query_rekam);
        mysqli_stmt_bind_param($stmt_rekam, "iiss", $id_pasien, $id_dokter, $tanggal, $diagnosa);
        mysqli_stmt_execute($stmt_rekam);
        $id_rekam = mysqli_insert_id($conn);
        mysqli_stmt_close($stmt_rekam);

        // Simpan resep (tanpa biaya)
        if (!empty($id_obat) && !empty($jumlah)) {
            $query_resep = "INSERT INTO resep (id_rekam, id_obat, jumlah) VALUES (?, ?, ?)";
            $stmt_resep = mysqli_prepare($conn, $query_resep);
            mysqli_stmt_bind_param($stmt_resep, "iii", $id_rekam, $id_obat, $jumlah);
            mysqli_stmt_execute($stmt_resep);
            mysqli_stmt_close($stmt_resep);
        }

        // Simpan tindakan medis jika ada
        if (!empty($nama_tindakan)) {
            $query_tindakan = "INSERT INTO tindakan_medis (id_rekam, nama_tindakan) VALUES (?, ?)";
            $stmt_tindakan = mysqli_prepare($conn, $query_tindakan);
            mysqli_stmt_bind_param($stmt_tindakan, "is", $id_rekam, $nama_tindakan);
            mysqli_stmt_execute($stmt_tindakan);
            mysqli_stmt_close($stmt_tindakan);
        }

        mysqli_commit($conn);
        $_SESSION['flash_message'] = [
            'type' => 'success',
            'message' => 'Rekam medis berhasil disimpan!'
        ];
        header("Location: dokter.php");
        exit();
    } catch (Exception $e) {
        mysqli_rollback($conn);
        $_SESSION['flash_message'] = [
            'type' => 'error',
            'message' => 'Gagal menyimpan data: ' . $e->getMessage()
        ];
    }
}

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Pasien - Rekam Medis Klinik</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">
    <nav class="bg-white shadow p-4">
        <div class="container mx-auto flex justify-between items-center">
            <div>
                <h1 class="text-xl sm:text-2xl font-bold text-blue-600">🩺 Tambah Pasien</h1>
                <p class="text-sm sm:text-base text-gray-500">Selamat datang, <strong><?php echo htmlspecialchars(ucwords(strtolower($_SESSION['nama']))); ?></strong></p>
            </div>
            <form method="POST" action="dokter.php">
                <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-semibold py-1 sm:py-2 px-3 sm:px-4 rounded-xl transition duration-200 text-sm sm:text-base">
                    Kembali
                </button>
            </form>
        </div>
    </nav>

    <main class="container mx-auto p-4 sm:p-6">
        <div class="bg-white rounded-2xl shadow p-4 sm:p-6">
            <h2 class="text-xl sm:text-2xl font-semibold text-blue-600 mb-6 sm:mb-8">Form Tambah Pasien</h2>
            <form method="POST" class="space-y-8">
                <!-- Informasi Pasien -->


                <!-- Rekam Medis -->
                <div>
                    <h3 class="text-base sm:text-lg font-semibold text-red-600 mb-3 sm:mb-4">Rekam Medis</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6">
                        <div>
                            <label class="block text-sm sm:text-base font-medium text-gray-700">Tanggal</label>
                            <input type="date" name="tanggal" class="mt-2 block w-full rounded-xl border-2 border-gray-500 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50 text-sm sm:text-base" required>
                        </div> 

                    
                        <div>
                            <label class="block text-sm sm:text-base font-medium text-gray-700">Nama pasien</label>
                            <select name="id_pasien" class="mt-2 block w-full rounded-xl border-2 border-gray-500 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50 text-sm sm:text-base" required>
                                <option value="">Pilih Pasien</option>
                                <?php mysqli_data_seek($result_pasien, 0); ?>
                                <?php while ($pasien = mysqli_fetch_assoc($result_pasien)): ?>
                                    <option value="<?php echo $pasien['id_pasien']; ?>"><?php echo htmlspecialchars($pasien['nama']); ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        

                        <div class="md:col-span-2">
                            <label class="block text-sm sm:text-base font-medium text-gray-700">Diagnosa</label>
                            <textarea name="diagnosa" class="mt-2 block w-full rounded-xl border-2 border-gray-500 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50 text-sm sm:text-base" rows="4" required></textarea>
                        </div>
                    </div>
                </div>

                <!-- Resep -->
                <div>
                    <h3 class="text-base sm:text-lg font-semibold text-yellow-600 mb-3 sm:mb-4">Resep</h3>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6 mb-4 sm:mb-6">
                            <div>
                                <label class="block text-sm sm:text-base font-medium text-gray-700">Obat</label>
                                <select name="id_obat" class="mt-2 block w-full rounded-xl border-2 border-gray-500 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50 text-sm sm:text-base">
                                    <option value="">Pilih Obat</option>
                                    <?php mysqli_data_seek($result_obat, 0); ?>
                                    <?php while ($obat = mysqli_fetch_assoc($result_obat)): ?>
                                        <option value="<?php echo $obat['id_obat']; ?>"><?php echo htmlspecialchars($obat['nama_obat']); ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm sm:text-base font-medium text-gray-700">Jumlah</label>
                                <input type="number" name="jumlah" class="mt-2 block w-full rounded-xl border-2 border-gray-500 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50 text-sm sm:text-base" min="1">
                            </div>
                        </div>
                </div>

                <!-- Tindakan Medis -->
                <div>
                    <h3 class="text-base sm:text-lg font-semibold text-purple-600 mb-3 sm:mb-4">Tindakan Medis</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6 mb-4 sm:mb-6">
                            <div>
                                <label class="block text-sm sm:text-base font-medium text-gray-700">Tindakan</label>
                                <input type="text" name="nama_tindakan" class="mt-2 block w-full rounded-xl border-2 border-gray-500 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50 text-sm sm:text-base">
                            </div>

                        </div>

                </div>

                <!-- Tombol Submit -->
                <div class="flex justify-end">
                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-semibold py-1 sm:py-2 px-3 sm:px-4 rounded-xl transition duration-200 text-sm sm:text-base">
                        Simpan Data
                    </button>
                </div>
            </form>
        </div>
    </main>
</body>
</html>

<?php
// Tutup koneksi
mysqli_free_result($result_obat);
mysqli_close($conn);
?>