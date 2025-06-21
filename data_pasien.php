<?php
session_start();
include 'koneksi.php';

// Pastikan data pasien tersedia di session
if (!isset($_SESSION['pasien'])) {
    $_SESSION['flash_message'] = [
        'type' => 'error',
        'message' => 'Silakan cari pasien terlebih dahulu.'
    ];
    header("Location: pasien.php");
    exit();
}

$pasien = $_SESSION['pasien'];
$id_pasien = $pasien['id_pasien'];

// untuk mengambil data rekam medis berdasarkan id_pasien
// dengan relalsi di ambil dari tabel rekam medis,tindakan medis,obat,resep
$stmt = mysqli_prepare($conn, "
    SELECT 
        rm.id_rekam,
        rm.tanggal,
        rm.diagnosa,
        tm.nama_tindakan,
        tm.biaya,
        o.nama_obat,
        o.dosis,
        o.harga,
        r.jumlah
    FROM rekam_medis rm
    LEFT JOIN tindakan_medis tm ON rm.id_rekam = tm.id_rekam
    LEFT JOIN resep r ON rm.id_rekam = r.id_rekam
    LEFT JOIN obat o ON r.id_obat = o.id_obat
    WHERE rm.id_pasien = ?
    ORDER BY rm.tanggal DESC
");
mysqli_stmt_bind_param($stmt, "i", $id_pasien);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Data Pasien - Rekam Medis Klinik</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    body {
      margin: 0;
      padding: 0;
      overflow: hidden;
    }
    .video-background {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100vh;
      overflow: hidden;
      z-index: -1;
    }
    .video-background video {
      width: 100%;
      height: 100%;
      object-fit: cover;
      filter: blur(5px);
    }
    .overlay {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100vh;
      background: rgba(0, 0, 0, 0.4);
      z-index: -1;
    }
    .login-container {
      z-index: 1;
    }
  </style>
</head>
<body class="min-h-screen flex items-center justify-center">

  <!-- Background Video -->
  <div class="video-background">
    <video autoplay muted loop playsinline>
      <source src="assets/video/login.mp4" type="video/mp4" />
      Your browser does not support the video tag.
    </video>
  </div>
  <div class="overlay"></div>

  <!-- Konten -->
  <div class="bg-white shadow-lg rounded-2xl p-8 w-full max-w-3xl login-container overflow-y-auto max-h-[90vh]">
    <div class="text-center mb-6">
      <h1 class="text-2xl font-bold text-blue-600">🩺 Data Pasien</h1>
      <p class="text-gray-500 text-sm">Berikut adalah hasil pemeriksaan Anda</p>
    </div>

    <div class="mb-4 text-gray-700 text-sm sm:text-base">
      <p><strong>Nama:</strong> <?php echo htmlspecialchars($pasien['nama']); ?></p>
      <p><strong>No HP:</strong> <?php echo htmlspecialchars($pasien['no_hp']); ?></p>
      <p><strong>Alamat:</strong> <?php echo htmlspecialchars($pasien['alamat']); ?></p>
    </div>

    <div class="text-right mb-4">
    <a href="cetak_pdf.php" target="_blank"
        class="bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded-xl transition duration-200 shadow">
        📄 Cetak PDF
    </a>
    </div>


    <h3 class="text-lg sm:text-xl font-semibold text-gray-800 mb-3">Riwayat Rekam Medis:</h3>
    <div class="overflow-x-auto">
      <table class="w-full table-auto border text-sm bg-white rounded-xl overflow-hidden">
        <thead class="bg-blue-100">
          <tr>
            <th class="p-2 border">Tanggal</th>
            <th class="p-2 border">Diagnosa</th>
            <th class="p-2 border">Tindakan</th>
            <th class="p-2 border">Resep</th>
            <th class="p-2 border">Harga</th>
          </tr>
        </thead>
        <tbody>
          <?php if (mysqli_num_rows($result) > 0): ?>
            <?php while ($row = mysqli_fetch_assoc($result)): ?>
              <tr class="hover:bg-gray-50">
                <td class="p-2 border"><?php echo htmlspecialchars($row['tanggal']); ?></td>
                <td class="p-2 border"><?php echo htmlspecialchars($row['diagnosa']); ?></td>
                <td class="p-2 border"><?php echo htmlspecialchars($row['nama_tindakan'] ?? '-'); ?></td>
                <td class="p-2 border">
                <?php
                    if ($row['nama_obat']) {
                    echo htmlspecialchars("{$row['nama_obat']} ({$row['dosis']}) x{$row['jumlah']}");
                    } else {
                    echo '-';
                    }
                ?>
                </td>
                <td class="p-2 border">
                <?php
                    if ($row['harga']) {
                        echo 'Rp.' . number_format($row['harga'], 2, ',', '.');
                    } else {
                    echo '-';
                    }
                ?>
                </td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr>
              <td colspan="4" class="text-center text-gray-500 py-4">Belum ada rekam medis untuk pasien ini.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</body>
</html>
