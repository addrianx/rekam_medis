<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 2) {
    $_SESSION['flash_message'] = [
        'type' => 'error',
        'message' => 'Anda harus login sebagai petugas untuk mengakses halaman ini!'
    ];
    header("Location: index.php");
    exit();
}

if (isset($_POST['logout'])) {
    session_unset();
    session_destroy();
    header("Location: index.php");
    exit();
}

$total_pasien = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM pasien"))['total'];
$total_dokter = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM dokter"))['total'];
$total_obat   = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM obat"))['total'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Petugas - Rekam Medis Klinik</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://unpkg.com/gridjs/dist/theme/mermaid.min.css" rel="stylesheet" />
    <script src="https://unpkg.com/gridjs/dist/gridjs.umd.js"></script>
</head>
<body class="bg-gray-100 min-h-screen">
    <nav class="bg-white shadow-lg rounded-b-lg">
        <div class="container mx-auto px-4 py-6 flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-blue-600">🩺 Dashboard Petugas</h1>
                <p class="text-base text-gray-600">Selamat datang, <strong class="text-blue-600"><?= htmlspecialchars(ucwords(strtolower($_SESSION['nama']))) ?></strong></p>
            </div>
            <form method="POST">
                <button type="submit" name="logout" class="bg-red-600 hover:bg-red-700 text-white font-semibold py-2 px-6 rounded-xl shadow transition">
                    Logout
                </button>
            </form>
        </div>
    </nav>

    <main class="container mx-auto p-4 sm:p-6">
        <?php if (isset($_SESSION['flash_message'])): ?>
            <?php $flash = $_SESSION['flash_message']; unset($_SESSION['flash_message']); ?>
            <div class="bg-<?= $flash['type'] === 'success' ? 'green' : 'red' ?>-100 border-l-4 border-<?= $flash['type'] === 'success' ? 'green' : 'red' ?>-500 text-gray-700 p-4 rounded-lg mb-4">
                <?= htmlspecialchars($flash['message']) ?>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
            <?php
            $cards = [
                ['label' => '👤 Total Pasien', 'total' => $total_pasien, 'color' => 'text-blue-600'],
                ['label' => '🩺 Total Dokter', 'total' => $total_dokter, 'color' => 'text-green-600'],
                ['label' => '💉 Total Obat',   'total' => $total_obat,   'color' => 'text-teal-600'],
            ];
            foreach ($cards as $card): ?>
                <div class="bg-white p-6 rounded-xl shadow-lg hover:shadow-xl transition">
                    <h2 class="text-lg font-semibold text-gray-700"><?= $card['label'] ?></h2>
                    <p class="text-3xl font-bold <?= $card['color'] ?>"><?= $card['total'] ?></p>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="mb-10">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-2xl font-semibold text-blue-600">📋 Data Pasien</h2>
                <a href="tambah_pasien.php" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-xl shadow transition">Tambah</a>
            </div>
            <div class="overflow-x-auto">
                <div id="table-pasien"></div>
            </div>
        </div>


        <div class="mb-10">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-2xl font-semibold text-green-600">🩺 Data Dokter</h2>
                <a href="tambah_dokter.php" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-xl shadow transition">Tambah</a>
            </div>
            <div class="overflow-x-auto">
                <div id="table-dokter"></div>
            </div>
        </div>

        <div class="mb-10">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-2xl font-semibold text-teal-600">💉 Data Obat</h2>
                <a href="tambah_obat.php" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-xl shadow transition">Tambah</a>
            </div>
            <div class="overflow-x-auto">
                <div id="table-obat"></div>
            </div>
        </div>
    </main>

    <script>

    new gridjs.Grid({
    columns: ["No", "Nama", "Tanggal Lahir", "Jenis Kelamin", "Alamat", "No. HP", "Aksi"],
    search: true,
    pagination: {
        limit: 10,
        server: {
        url: (prev, page, limit) => `${prev}?limit=${limit}&offset=${page * limit}`
        }
    },
    server: {
        url: 'json/data_pasien_grid.php',
        then: res => res.data.map((row, i) => [
        i + 1 + res.pagination.offset,
        row.nama,
        row.tanggal_lahir,
        row.jenis_kelamin,
        row.alamat,
        row.no_hp,
        gridjs.html(`<a href='edit_pasien.php?id=${row.id_pasien}' class='text-blue-600 hover:underline'>Edit</a>`)
        ]),
        total: res => res.pagination.total
    }
    }).render(document.getElementById("table-pasien"));


      new gridjs.Grid({
        columns: ["No", "Nama", "Tanggal Lahir", "Jenis Kelamin", "Alamat", "No. HP", "Aksi"],
        search: true,
        pagination: {
            limit: 10,
            server: true
        },
        server: {
            url: (prev, query) => {
            let url = new URL("json/data_pasien_grid.php", window.location.origin);

            // ambil offset dan limit dari query (otomatis dikirim Grid.js)
            for (const param of query) {
                url.searchParams.append(param.name, param.value);
            }

            return url.href;
            },
            then: response => response.data.map((row, i) => [
            i + 1,
            row.nama,
            row.tanggal_lahir,
            row.jenis_kelamin,
            row.alamat,
            row.no_hp,
            `<a href='edit_pasien.php?id=${row.id_pasien}' class='text-blue-600'>Edit</a>`
            ])
        }
        }).render(document.getElementById("table-pasien"));

      new gridjs.Grid({
        columns: ["No", "Nama", "Spesialisasi", "No. Telepon"],
        search: true,
        pagination: { limit: 10 },
        server: {
          url: 'json/data_dokter_grid.php',
          then: data => data.map((row, i) => [
            i + 1,
            row.nama,
            row.spesialisasi,
            row.nomor_telepon
          ])
        }
      }).render(document.getElementById("table-dokter"));

      new gridjs.Grid({
        columns: ["No", "Nama Obat", "Dosis", "Harga"],
        search: true,
        pagination: { limit: 10 },
        server: {
          url: 'json/data_obat_grid.php',
          then: data => data.map((row, i) => [
            i + 1,
            row.nama_obat,
            row.dosis,
            row.harga
          ])
        }
      }).render(document.getElementById("table-obat"));
    </script>
</body>
</html>

<?php mysqli_close($conn); ?>
