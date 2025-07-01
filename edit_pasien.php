<?php
session_start();
include 'koneksi.php';

// Cek login dan role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 2) {
    $_SESSION['flash_message'] = ['type' => 'error', 'message' => 'Akses ditolak. Login sebagai petugas.'];
    header("Location: index.php");
    exit();
}

// Ambil ID pasien
$id_pasien = $_GET['id'] ?? null;
if (!$id_pasien) {
    $_SESSION['flash_message'] = ['type' => 'error', 'message' => 'ID pasien tidak ditemukan.'];
    header("Location: petugas.php");
    exit();
}

// Ambil data pasien
$stmt = mysqli_prepare($conn, "SELECT * FROM pasien WHERE id_pasien = ?");
mysqli_stmt_bind_param($stmt, 'i', $id_pasien);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$pasien = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$pasien) {
    $_SESSION['flash_message'] = ['type' => 'error', 'message' => 'Data pasien tidak ditemukan.'];
    header("Location: petugas.php");
    exit();
}

// Proses update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama']);
    $tanggal_lahir = $_POST['tanggal_lahir'];
    $jenis_kelamin = $_POST['jenis_kelamin'];
    $alamat = trim($_POST['alamat']);
    $no_hp = trim($_POST['no_hp']);

    $stmt = mysqli_prepare($conn, "UPDATE pasien SET nama=?, tanggal_lahir=?, jenis_kelamin=?, alamat=?, no_hp=? WHERE id_pasien=?");
    mysqli_stmt_bind_param($stmt, 'sssssi', $nama, $tanggal_lahir, $jenis_kelamin, $alamat, $no_hp, $id_pasien);

    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['flash_message'] = ['type' => 'success', 'message' => 'Data pasien berhasil diperbarui.'];
        header("Location: petugas.php");
        exit();
    } else {
        $error_message = 'Gagal memperbarui data pasien.';
    }
    mysqli_stmt_close($stmt);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Pasien</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">
        <!-- Navbar -->
        <nav class="bg-white shadow p-4">
            <div class="container mx-auto flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold text-blue-600">🩺 Tambah Pasien</h1>
                    <p class="text-gray-500 text-sm">
                        Selamat datang, <strong><?= htmlspecialchars($_SESSION['nama'] ?? '') ?></strong>
                    </p>
                </div>
                <form method="POST" action="petugas.php">
                    <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-semibold py-2 px-4 rounded-xl transition">
                        Kembali
                    </button>
                </form>
            </div>
        </nav>
        
    <div class="container mx-auto px-4 py-6">
        <h1 class="text-2xl font-bold text-blue-600 mb-4">✏️ Edit Data Pasien</h1>

        <?php if (!empty($error_message)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <?= htmlspecialchars($error_message) ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="space-y-4 bg-white p-6 rounded-xl shadow">
            <div>
                <label class="block font-medium text-gray-700">Nama</label>
                <input type="text" name="nama" value="<?= htmlspecialchars($pasien['nama']) ?>" class="w-full border rounded-xl px-4 py-2">
            </div>
            <div>
                <label class="block font-medium text-gray-700">Tanggal Lahir</label>
                <input type="date" name="tanggal_lahir" value="<?= htmlspecialchars($pasien['tanggal_lahir']) ?>" class="w-full border rounded-xl px-4 py-2">
            </div>
            <div>
                <label class="block font-medium text-gray-700">Jenis Kelamin</label>
                <select name="jenis_kelamin" class="w-full border rounded-xl px-4 py-2">
                    <option value="Laki-laki" <?= $pasien['jenis_kelamin'] === 'Laki-laki' ? 'selected' : '' ?>>Laki-laki</option>
                    <option value="Perempuan" <?= $pasien['jenis_kelamin'] === 'Perempuan' ? 'selected' : '' ?>>Perempuan</option>
                </select>
            </div>
            <div>
                <label class="block font-medium text-gray-700">Alamat</label>
                <textarea name="alamat" rows="3" class="w-full border rounded-xl px-4 py-2"><?= htmlspecialchars($pasien['alamat']) ?></textarea>
            </div>
            <div>
                <label class="block font-medium text-gray-700">No HP</label>
                <input type="text" name="no_hp" value="<?= htmlspecialchars($pasien['no_hp']) ?>" class="w-full border rounded-xl px-4 py-2">
            </div>
            <div class="text-right">
                <button type="submit" class="bg-yellow-500 hover:bg-yellow-600 text-white font-semibold px-6 py-2 rounded-xl">Simpan Perubahan</button>
                <a href="petugas.php" class="ml-2 text-gray-600 hover:underline">Batal</a>
            </div>
        </form>
    </div>
</body>
</html>

<?php mysqli_close($conn); ?>
