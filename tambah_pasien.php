<?php
session_start();

// Tampilkan flash message jika ada
if (isset($_SESSION['flash_message'])):
    $flash = $_SESSION['flash_message'];
?>
    <div class="mb-4 p-4 rounded-xl <?= $flash['type'] === 'error' ? 'bg-red-100 text-red-700 border border-red-300' : 'bg-green-100 text-green-700 border border-green-300' ?>">
        <?= htmlspecialchars($flash['message']) ?>
    </div>
<?php
    unset($_SESSION['flash_message']);
endif;

// Ambil error dan input lama (jika validasi gagal sebelumnya)
$errors = $_SESSION['form_errors'] ?? [];
$old = $_SESSION['old'] ?? [];
unset($_SESSION['form_errors'], $_SESSION['old']);

include 'koneksi.php';

// Cek login dan role user (wajib petugas = role 2)
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] != 2) {
    $_SESSION['flash_message'] = [
        'type' => 'error',
        'message' => 'Anda harus login sebagai petugas untuk mengakses halaman ini!'
    ];
    header("Location: login.php");
    exit();
}

// Ambil ID petugas (dokter)
$petugas = $_SESSION['user_id'];

// Fungsi untuk membuat kode akses pasien berdasarkan nama
function generateKodeAkses($nama_pasien) {
    $nama_bersih = strtoupper(preg_replace('/[^A-Za-z]/', '', $nama_pasien)); // Hanya huruf, uppercase
    $prefix = substr($nama_bersih, 0, 3);
    if (strlen($prefix) < 3) {
        $prefix = str_pad($prefix, 3, 'X'); // Isi dengan X jika kurang dari 3 huruf
    }

    $angka = rand(100, 999);
    $karakter = ['-', '_', '@'];
    $simbol = $karakter[array_rand($karakter)];

    return $prefix . $simbol . $angka;
}

// Proses form saat POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    mysqli_begin_transaction($conn); // Mulai transaksi

    try {
        $errors = [];
        $old = [];

        // Ambil dan bersihkan input
        $nama = ucwords(strtolower(trim($_POST['nama'])));
        $tanggal_lahir = $_POST['tanggal_lahir'];
        $jenis_kelamin = $_POST['jenis_kelamin'];
        $alamat = trim($_POST['alamat']);
        $no_hp = trim($_POST['no_hp']);

        // Simpan input lama untuk keperluan repopulasi
        $old = compact('nama', 'tanggal_lahir', 'jenis_kelamin', 'alamat', 'no_hp');

        // Validasi manual seperti Laravel
        if (empty($nama)) $errors['nama'] = "Nama wajib diisi.";
        if (empty($tanggal_lahir)) $errors['tanggal_lahir'] = "Tanggal lahir wajib diisi.";
        if (!in_array($jenis_kelamin, ['Laki-laki', 'Perempuan'])) $errors['jenis_kelamin'] = "Pilih jenis kelamin yang valid.";
        if (empty($alamat)) $errors['alamat'] = "Alamat wajib diisi.";
        if (!preg_match('/^08[0-9]{8,12}$/', $no_hp)) $errors['no_hp'] = "Nomor HP tidak valid (contoh: 08xxxxxxxxxx).";

        // Jika ada error validasi, redirect kembali dengan pesan error dan input lama
        if (!empty($errors)) {
            $_SESSION['form_errors'] = $errors;
            $_SESSION['old'] = $old;
            header("Location: tambah_pasien.php");
            exit();
        }

        // Buat kode akses dan tanggal kadaluarsa
        $kode_akses = generateKodeAkses($nama);
        $kadaluarsa = date('Y-m-d', strtotime('+7 days'));

        // Simpan data ke database
        $query = "INSERT INTO pasien (nama, tanggal_lahir, jenis_kelamin, alamat, no_hp, kode_akses, akses_kadaluarsa)
                  VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "sssssss", $nama, $tanggal_lahir, $jenis_kelamin, $alamat, $no_hp, $kode_akses, $kadaluarsa);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        mysqli_commit($conn); // Commit transaksi jika sukses

        $_SESSION['flash_message'] = [
            'type' => 'success',
            'message' => 'Pasien berhasil ditambahkan. Kode akses: ' . $kode_akses
        ];
        header("Location: petugas.php");
        exit();
    } catch (Exception $e) {
        // Rollback jika terjadi error saat insert
        mysqli_rollback($conn);
        $_SESSION['flash_message'] = [
            'type' => 'error',
            'message' => 'Gagal menambahkan pasien: ' . $e->getMessage()
        ];
        header("Location: tambah_pasien.php");
        exit();
    }
}
?>

<!-- =============================== HTML =============================== -->

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Pasien - Rekam Medis Klinik</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">

    <!-- Navbar -->
    <nav class="bg-white shadow p-4">
        <div class="container mx-auto flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-blue-600">🩺 Tambah Pasien</h1>
                <p class="text-gray-500 text-sm">
                    Selamat datang, <strong><?= htmlspecialchars($_SESSION['nama'] ?? '') ?></strong>
                </p>
            </div>
            <form method="POST" action="petugas.php">
                <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-semibold py-2 px-4 rounded-xl transition">
                    Kembali
                </button>
            </form>
        </div>
    </nav>

    <!-- Form Tambah Pasien -->
    <main class="container mx-auto p-4 sm:p-6">
        <div class="bg-white rounded-2xl shadow p-6">
            <h2 class="text-2xl font-semibold text-blue-600 mb-6">Form Tambah Pasien</h2>

            <form method="POST" class="space-y-6">
                <?php
                // Fungsi untuk menampilkan input field (mengurangi pengulangan)
                function field($label, $name, $errors, $old, $type = 'text', $options = []) {
                    $value = htmlspecialchars($old[$name] ?? '');
                    $error = $errors[$name] ?? '';
                    $borderClass = $error ? 'border-red-500' : 'border-gray-500';

                    echo "<div>";
                    echo "<label class='block font-medium text-gray-700'>" . $label . "</label>";

                    if ($type === 'textarea') {
                        echo "<textarea name='$name' rows='4' class='mt-1 w-full rounded-xl border-2 $borderClass focus:outline-none focus:ring-2 focus:ring-blue-500 px-4 py-2'>$value</textarea>";
                    } elseif ($type === 'select') {
                        echo "<select name='$name' class='mt-1 w-full rounded-xl border-2 $borderClass focus:outline-none focus:ring-2 focus:ring-blue-500 px-4 py-2'>";
                        echo "<option value=''>Pilih $label</option>";
                        foreach ($options as $opt) {
                            $selected = $value === $opt ? 'selected' : '';
                            echo "<option value='$opt' $selected>$opt</option>";
                        }
                        echo "</select>";
                    } else {
                        echo "<input type='$type' name='$name' value='$value' class='mt-1 w-full rounded-xl border-2 $borderClass focus:outline-none focus:ring-2 focus:ring-blue-500 px-4 py-2'>";
                    }

                    if ($error) {
                        echo "<p class='text-red-600 text-sm mt-1'>$error</p>";
                    }
                    echo "</div>";
                }

                // Render field satu per satu
                field('Nama', 'nama', $errors, $old);
                field('Tanggal Lahir', 'tanggal_lahir', $errors, $old, 'date');
                field('Jenis Kelamin', 'jenis_kelamin', $errors, $old, 'select', ['Laki-laki', 'Perempuan']);
                field('No HP', 'no_hp', $errors, $old);
                field('Alamat', 'alamat', $errors, $old, 'textarea');
                ?>

                <!-- Tombol Simpan -->
                <div class="text-right">
                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white py-2 px-6 rounded-xl">
                        Simpan Data
                    </button>
                </div>
            </form>
        </div>
    </main>

</body>
</html>

<?php
// Tutup koneksi database
mysqli_close($conn);
?>
