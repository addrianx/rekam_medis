<?php
$host     = "localhost";
$username = "root";       // ganti jika tidak pakai user root
$password = "";           // sesuaikan dengan password MySQL kamu
$database = "rekam_medis";

// Membuat koneksi
$conn = mysqli_connect($host, $username, $password, $database);

// Cek koneksi
// Cek koneksi dan tampilkan hasil
if (!$conn) {
    die("Koneksi ke database gagal: " . mysqli_connect_error());
}
?>