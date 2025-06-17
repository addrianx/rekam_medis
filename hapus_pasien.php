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

// Proses penghapusan jika ID pasien diberikan
if (isset($_GET['id'])) {
    $id_pasien = $_GET['id'];

    // Mulai transaksi
    mysqli_begin_transaction($conn);

    try {
        // Hapus data terkait di tabel resep
        $query_resep = "DELETE r FROM resep r 
                        JOIN rekam_medis rm ON r.id_rekam = rm.id_rekam 
                        WHERE rm.id_pasien = ? AND rm.id_dokter = ?";
        $stmt_resep = mysqli_prepare($conn, $query_resep);
        mysqli_stmt_bind_param($stmt_resep, "ii", $id_pasien, $id_dokter);
        mysqli_stmt_execute($stmt_resep);
        mysqli_stmt_close($stmt_resep);

        // Hapus data terkait di tabel tindakan_medis
        $query_tindakan = "DELETE tm FROM tindakan_medis tm 
                           JOIN rekam_medis rm ON tm.id_rekam = rm.id_rekam 
                           WHERE rm.id_pasien = ? AND rm.id_dokter = ?";
        $stmt_tindakan = mysqli_prepare($conn, $query_tindakan);
        mysqli_stmt_bind_param($stmt_tindakan, "ii", $id_pasien, $id_dokter);
        mysqli_stmt_execute($stmt_tindakan);
        mysqli_stmt_close($stmt_tindakan);

        // Hapus data di tabel rekam_medis
        $query_rekam = "DELETE FROM rekam_medis WHERE id_pasien = ? AND id_dokter = ?";
        $stmt_rekam = mysqli_prepare($conn, $query_rekam);
        mysqli_stmt_bind_param($stmt_rekam, "ii", $id_pasien, $id_dokter);
        mysqli_stmt_execute($stmt_rekam);
        mysqli_stmt_close($stmt_rekam);

        // Hapus data pasien
        $query_pasien = "DELETE FROM pasien WHERE id_pasien = ?";
        $stmt_pasien = mysqli_prepare($conn, $query_pasien);
        mysqli_stmt_bind_param($stmt_pasien, "i", $id_pasien);
        mysqli_stmt_execute($stmt_pasien);
        mysqli_stmt_close($stmt_pasien);

        // Commit transaksi
        mysqli_commit($conn);

        $_SESSION['flash_message'] = [
            'type' => 'success',
            'message' => 'Data pasien berhasil dihapus!'
        ];
        header("Location: dokter.php");
        exit();
    } catch (Exception $e) {
        // Rollback jika terjadi error
        mysqli_rollback($conn);
        $_SESSION['flash_message'] = [
            'type' => 'error',
            'message' => 'Gagal menghapus data: ' . $e->getMessage()
        ];
        header("Location: dokter.php");
        exit();
    }
} else {
    $_SESSION['flash_message'] = [
        'type' => 'error',
        'message' => 'ID pasien tidak valid!'
    ];
    header("Location: dokter.php");
    exit();
}

// Tutup koneksi
mysqli_close($conn);
?>