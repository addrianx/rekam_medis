<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard Pasien - Rekam Medis Klinik</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">
  <nav class="bg-white shadow p-4">
    <div class="container mx-auto">
      <h1 class="text-2xl font-bold text-blue-600">🩺 Dashboard Pasien</h1>
      <p class="text-gray-500">Selamat datang, <strong>Dina Lestari</strong></p>
    </div>
  </nav>

  <main class="container mx-auto p-6">
    <div class="bg-white p-6 rounded-xl shadow">
      <h2 class="text-xl font-semibold mb-4 text-red-500">📄 Rekam Medis Anda</h2>
      <div class="overflow-x-auto">
        <table class="min-w-full bg-white rounded-xl shadow">
          <thead>
            <tr class="bg-red-100 text-left">
              <th class="py-3 px-4 font-medium">ID</th>
              <th class="py-3 px-4 font-medium">Tanggal</th>
              <th class="py-3 px-4 font-medium">Dokter</th>
              <th class="py-3 px-4 font-medium">Diagnosa</th>
            </tr>
          </thead>
          <tbody>
            <!-- Hanya data milik pasien ID=2 -->
            <tr class="border-t hover:bg-gray-50">
              <td class="py-3 px-4">2</td>
              <td class="py-3 px-4">2025-06-01</td>
              <td class="py-3 px-4">Dr. Lisa Hartati</td>
              <td class="py-3 px-4">Batuk Pilek</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </main>
</body>
</html>
