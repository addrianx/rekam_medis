<?php
// data_dokter_grid.php
session_start();
include '../koneksi.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 2) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized access']);
    exit();
}

$offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
$limit  = isset($_GET['limit'])  ? (int)$_GET['limit']  : 10;

$query = "SELECT id_dokter, nama, spesialisasi, nomor_telepon FROM dokter ORDER BY id_dokter LIMIT ? OFFSET ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "ii", $limit, $offset);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$data = [];
while ($row = mysqli_fetch_assoc($result)) {
    $data[] = $row;
}

$total = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM dokter"))['total'];

header('Content-Type: application/json');
echo json_encode($data);