<?php
session_start();
include 'koneksi.php';

// untuk mengecek apakah data berasal dari form input yang di sediakan atau bukan
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_pasien = trim($_POST['nama_pasien']);
    $no_hp = trim($_POST['no_hp']);

    // Cek apakah pasien dengan nama dan no_hp ada di database
    $stmt = mysqli_prepare($conn, "SELECT * FROM pasien WHERE nama = ? AND no_hp = ?");
    mysqli_stmt_bind_param($stmt, "ss", $nama_pasien, $no_hp);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($pasien = mysqli_fetch_assoc($result)) {
        // Simpan data pasien ke session untuk ditampilkan di halaman selanjutnya
        $_SESSION['pasien'] = $pasien;
        header("Location: data_pasien.php");
        exit();
    } else {
        $_SESSION['flash_message'] = [
            'type' => 'error',
            'message' => 'Data pasien tidak ditemukan. Pastikan nama dan nomor HP sesuai.'
        ];
        header("Location: pasien.php"); // ganti dengan nama file form pencarian
        exit();
    }
}
?>
