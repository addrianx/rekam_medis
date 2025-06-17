<?php
session_start();
include 'koneksi.php';

// Cek apakah pengguna sudah login dan memiliki role dokter (role = 1)
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] != 2) {
    $_SESSION['flash_message'] = [
        'type' => 'error',
        'message' => 'Anda harus login sebagai petugas untuk mengakses halaman ini!'
    ];
    header("Location: login.php");
    exit();
}

// Ambil ID dokter dari sesi
$petugas = $_SESSION['user_id'];

// Proses form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // Mulai transaksi
    mysqli_begin_transaction($conn);

    try {
        // Simpan data dokter
        $nama_obat = ucwords(strtolower(trim($_POST['nama_obat'])));
        $dosis = trim($_POST['dosis']);
        

        $query_obat = "INSERT INTO obat (nama_obat, dosis) VALUES (?, ?)";
        $stmt_obat = mysqli_prepare($conn, $query_obat);
        mysqli_stmt_bind_param($stmt_obat, "ss", $nama_obat, $dosis);



        mysqli_stmt_execute($stmt_obat);
        $id_obat = mysqli_insert_id($conn);
        mysqli_stmt_close($stmt_obat);


        // Commit transaksi
        mysqli_commit($conn);
        $_SESSION['flash_message'] = [
            'type' => 'success',
            'message' => 'Data obat dan terkait berhasil ditambahkan!'
        ];
        header("Location: petugas.php");
        exit();
    } catch (Exception $e) {
        // Rollback jika terjadi error
        mysqli_rollback($conn);
        $_SESSION['flash_message'] = [
            'type' => 'error',
            'message' => 'Gagal menambahkan data: ' . $e->getMessage()
        ];
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah obat - Rekam Medis Klinik</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">
    <nav class="bg-white shadow p-4">
        <div class="container mx-auto flex justify-between items-center">
            <div>
                <h1 class="text-xl sm:text-2xl font-bold text-blue-600">🩺 Tambah obat</h1>
                <p class="text-sm sm:text-base text-gray-500">Selamat datang, <strong><?php echo htmlspecialchars(ucwords(strtolower($_SESSION['nama']))); ?></strong></p>
            </div>
            <form method="POST" action="petugas.php">
                <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-semibold py-1 sm:py-2 px-3 sm:px-4 rounded-xl transition duration-200 text-sm sm:text-base">
                    Kembali
                </button>
            </form>
        </div>
    </nav>

    <main class="container mx-auto p-4 sm:p-6">
        <div class="bg-white rounded-2xl shadow p-4 sm:p-6">
            <h2 class="text-xl sm:text-2xl font-semibold text-blue-600 mb-6 sm:mb-8">Form Tambah obat</h2>
            <form method="POST" class="space-y-8">
                <!-- Informasi obat -->
                <div>
                    <h3 class="text-base sm:text-lg font-semibold text-gray-700 mb-3 sm:mb-4">Informasi obat</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6">
                        <div>
                            <label class="block text-sm sm:text-base font-medium text-gray-700">Nama obat</label>
                            <input type="text" name="nama_obat" class="mt-2 block w-full rounded-xl border-2 border-gray-500 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50 text-sm sm:text-base" required>
                        </div>
                        <div>
                            <label class="block text-sm sm:text-base font-medium text-gray-700">dosis</label>
                            <input type="text" name="dosis" class="mt-2 block w-full rounded-xl border-2 border-gray-500 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50 text-sm sm:text-base" required>
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
mysqli_close($conn);
?>
