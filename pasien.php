<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Data pasien - Sistem Rekam Medis Klinik</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    body {
      margin: 0;
      padding: 0;
      overflow: hidden; /* Mencegah scroll */
    }
    .video-background {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100vh;
      overflow: hidden;
      z-index: -1; /* Di belakang konten */
    }
    .video-background video {
      width: 100%;
      height: 100%;
      object-fit: cover; /* Memenuhi layar tanpa distorsi */
      filter: blur(5px); /* Efek blur untuk menyamarkan kualitas rendah */
    }
    .overlay {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100vh;
      background: rgba(0, 0, 0, 0.4); /* Overlay semi-transparan */
      z-index: -1; /* Di belakang form, di atas video */
    }
    .login-container {
      z-index: 1; /* Di depan video dan overlay */
    }
  </style>
</head>
<body class="min-h-screen flex items-center justify-center">
  <!-- Video Background -->
  <div class="video-background">
    <video autoplay muted loop playsinline>
      <source src="assets/video/login.mp4" type="video/mp4" />
      Your browser does not support the video tag.
    </video>
  </div>
  <!-- Overlay -->
  <div class="overlay"></div>

  <!-- Form Login -->
  <div class="bg-white shadow-lg rounded-2xl p-8 w-full max-w-md login-container">
    <div class="text-center mb-6">
      <h1 class="text-2xl font-bold text-blue-600">🩺 Cari Data Pasien</h1>
      <p class="text-gray-500 text-sm">Silahkan masukan nama dan no hp yang terdaftar</p>
    </div>
    <!-- Menampilkan Flash Message -->
    <?php
    session_start();
    if (isset($_SESSION['flash_message'])) {
      $flash = $_SESSION['flash_message'];
      echo '<div class="mb-4 p-4 rounded-xl bg-red-100 text-red-700 border border-red-300">';
      echo htmlspecialchars($flash['message']);
      echo '</div>';
      // Hapus flash message setelah ditampilkan
      unset($_SESSION['flash_message']);
    }
    ?>
<form action="data_check.php" method="POST">
  <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
    <!-- Nama Pasien -->
    <div>
      <label for="nama_pasien" class="block text-gray-700 font-medium mb-2">Nama Pasien</label>
      <input type="text" id="nama_pasien" name="nama_pasien" required
        class="w-full px-4 py-2 border rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-400"
        placeholder="Nama Lengkap" />
    </div>

    <!-- No HP -->
    <div>
      <label for="no_hp" class="block text-gray-700 font-medium mb-2">No HP</label>
      <input type="text" id="no_hp" name="no_hp" required
        class="w-full px-4 py-2 border rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-400"
        placeholder="08xxxxxxxxxx" />
    </div>
  </div>

  <!-- Tombol -->
  <button type="submit"
    class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-xl transition duration-200">
    Cari Data Pasien
  </button>
</form>

  </div>
</body>
</html>