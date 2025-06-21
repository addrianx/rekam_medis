<?php
// untuk memulai sesi (session) agar kita bisa menyimpan dan mengambil data pengguna selama mereka mengakses website.
// include koneksi php berfungsi untuk memanggil file koneksi php agar halaman bisa tersambung ke data base
session_start(); 
include 'koneksi.php';

// mengambil nilai dari form login dengan data no_hp dan password yang telah di inpt pengguna di halaman login
$no_hp = $_POST['no_hp'];
$password = $_POST['password'];

// Fungsi untuk menyimpan flash message
// digunakan untuk menampilkan error di halamn login
function setFlashMessage($type, $message) {
    $_SESSION['flash_message'] = [
        'type' => $type,
        'message' => $message
    ];
}

// untuk mengecek apabila yang login adlah dokter 
$query = "SELECT id_dokter AS user_id, nama, role, password FROM dokter WHERE nomor_telepon = ? LIMIT 1";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "s", $no_hp);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($result && mysqli_num_rows($result) === 1) {
    $data = mysqli_fetch_assoc($result);
    if ($password == $data['password']) {
        // jika no hp dan password di temukan kita simpan nama dan id dokter di sesion agar data dokter bisa di simpan di browser
        $_SESSION['user_id'] = $data['user_id'];
        $_SESSION['nama'] = $data['nama'];
        $_SESSION['role'] = $data['role'];
        
        // apabila selesai di simpan di sesion halamn di arahkan ke halaman dokter
        if ($data['role'] == 1) {
            header("Location: dokter.php");
            exit();
        
        // jika data tidak sesuai akan di lemparkan kembali ke halaman login
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

// untuk menutup sambungan ke data base
mysqli_stmt_close($stmt);
?>