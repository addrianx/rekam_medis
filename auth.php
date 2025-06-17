<?php
session_start();
include 'koneksi.php';

$no_hp = $_POST['no_hp'];
$password = $_POST['password'];

// Fungsi untuk menyimpan flash message
function setFlashMessage($type, $message) {
    $_SESSION['flash_message'] = [
        'type' => $type,
        'message' => $message
    ];
}

// Cek di tabel pasien
$query = "SELECT id_pasien AS user_id, nama, role, password FROM pasien WHERE no_hp = ? LIMIT 1";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "s", $no_hp);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($result && mysqli_num_rows($result) === 1) {
    $data = mysqli_fetch_assoc($result);
    if ($password == $data['password']) {
        $_SESSION['user_id'] = $data['user_id'];
        $_SESSION['nama'] = $data['nama'];
        $_SESSION['role'] = $data['role'];
        
        if ($data['role'] == 3) {
            header("Location: pasien.php");
            exit();
        } else {
            setFlashMessage('error', 'Role tidak valid untuk pasien!');
            header("Location: index.php");
            exit();
        }
    } else {
        setFlashMessage('error', 'Password salah!');
        header("Location: index.php");
        exit();
    }
}

// Cek di tabel dokter
$query = "SELECT id_dokter AS user_id, nama, role, password FROM dokter WHERE nomor_telepon = ? LIMIT 1";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "s", $no_hp);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($result && mysqli_num_rows($result) === 1) {
    $data = mysqli_fetch_assoc($result);
    if ($password == $data['password']) {
        $_SESSION['user_id'] = $data['user_id'];
        $_SESSION['nama'] = $data['nama'];
        $_SESSION['role'] = $data['role'];
        
        if ($data['role'] == 1) {
            header("Location: dokter.php");
            exit();
        } else {
            setFlashMessage('error', 'Role tidak valid untuk dokter!');
            header("Location: index.php");
            exit();
        }
    } else {
        setFlashMessage('error', 'Password salah!');
        header("Location: index.php");
        exit();
    }
}

// Cek di tabel petugas (asumsikan tabel petugas ada)
$query = "SELECT id_petugas AS user_id, nama, role, password FROM petugas WHERE no_hp = ? LIMIT 1";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "s", $no_hp);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($result && mysqli_num_rows($result) === 1) {
    $data = mysqli_fetch_assoc($result);
    if ($password == $data['password']) {
        $_SESSION['user_id'] = $data['user_id'];
        $_SESSION['nama'] = $data['nama'];
        $_SESSION['role'] = $data['role'];
        
        if ($data['role'] == 2) {
            header("Location: petugas.php");
            exit();
        } else {
            setFlashMessage('error', 'Role tidak valid untuk petugas!');
            header("Location: index.php");
            exit();
        }
    } else {
        setFlashMessage('error', 'Password salah!');
        header("Location: index.php");
        exit();
    }
}

// Jika tidak ditemukan di tabel manapun
setFlashMessage('error', 'Nomor HP tidak ditemukan!');
header("Location: index.php");
exit();

// Tutup statement
mysqli_stmt_close($stmt);
?>