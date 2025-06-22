<?php
// Kode ini digunakan untuk menghubungkan aplikasi PHP ke database MySQL bernama rekam_medis
$host     = "localhost";
$username = "root";       
$password = "";           
$database = "rekam_medis";

// Membuat koneksi
$conn = mysqli_connect($host, $username, $password, $database);

// Cek koneksi
// Cek koneksi dan tampilkan hasil
if (!$conn) {
    die("Koneksi ke database gagal: " . mysqli_connect_error());
}
?>