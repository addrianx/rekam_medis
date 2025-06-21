<?php
require 'vendor/autoload.php';
use Dompdf\Dompdf;
use Dompdf\Options;

session_start();
include 'koneksi.php';

if (!isset($_SESSION['pasien'])) {
    die("Pasien tidak ditemukan.");
}

$pasien = $_SESSION['pasien'];
$id_pasien = $pasien['id_pasien'];

// Ambil data rekam medis
$stmt = mysqli_prepare($conn, "
    SELECT 
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

// Ambil data gambar dan encode base64
$logo_path = 'assets/logo.png';
$logo_base64 = base64_encode(file_get_contents($logo_path));
$logo_src = 'data:image/png;base64,' . $logo_base64;

// HTML
$html = '
<div style="text-align: center;">
  <table style="margin: 0 auto; margin-bottom: 10px;">
    <tr>
      <td style="width: 100px; vertical-align: middle;">
        <img src="' . $logo_src . '" alt="Logo" width="80">
      </td>
      <td style="padding-left: 15px; text-align: left;">
        <h2 style="margin: 0; color: #0A2C5A;">KLINIK MEDIKA NUSANTARA</h2>
        <p style="margin: 4px 0 0; font-size: 12px;">Laporan Pemeriksaan Pasien</p>
      </td>
    </tr>
  </table>
</div>
<hr>
<p><strong>Nama:</strong> ' . htmlspecialchars($pasien['nama']) . '</p>
<p><strong>No HP:</strong> ' . htmlspecialchars($pasien['no_hp']) . '</p>
<p><strong>Alamat:</strong> ' . htmlspecialchars($pasien['alamat']) . '</p>
<br>
<table border="1" cellspacing="0" cellpadding="5" width="100%">
    <thead>
    <tr style="background-color: #e1ecf4;">
        <th>Tanggal</th>
        <th>Diagnosa</th>
        <th>Tindakan & Biaya</th>
        <th>Resep & Harga</th>
    </tr>
    </thead>

    <tbody>';

    $total_harga = 0;

    if (mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            // Tindakan
            $tindakan = '-';
            $harga_tindakan = 0;
            if (!empty($row['nama_tindakan'])) {
                $harga_tindakan = $row['biaya'] ?? 0;
                $tindakan = htmlspecialchars($row['nama_tindakan']) . '<br><small>Rp. ' . number_format($harga_tindakan, 2, ',', '.') . '</small>';
            }
    
            // Resep
            $resep = '-';
            $harga_obat_total = 0;
            if (!empty($row['nama_obat'])) {
                $harga_obat = $row['harga'] ?? 0;
                $jumlah = $row['jumlah'] ?? 0;
                $harga_obat_total = $harga_obat * $jumlah;
                $resep = htmlspecialchars("{$row['nama_obat']} ({$row['dosis']}) x{$jumlah}") . 
                         '<br><small>Rp. ' . number_format($harga_obat_total, 2, ',', '.') . '</small>';
            }
    
            $subtotal = $harga_tindakan + $harga_obat_total;
            $total_harga += $subtotal;
    
            $html .= '<tr>
                <td>' . htmlspecialchars($row['tanggal']) . '</td>
                <td>' . htmlspecialchars($row['diagnosa']) . '</td>
                <td>' . $tindakan . '</td>
                <td>' . $resep . '</td>
            </tr>';
        }
        $html .= '<tr>
        <td colspan="3" align="right"><strong>Total Harga Keseluruhan:</strong></td>
        <td><strong>Rp. ' . number_format($total_harga, 2, ',', '.') . '</strong></td>
    </tr>';
    } else {
        $html .= '<tr><td colspan="4" align="center">Belum ada data rekam medis.</td></tr>';
    }
    
    
    

$html .= '</tbody></table>';

// Generate PDF
$options = new Options();
$options->set('isRemoteEnabled', true);
$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream('struk_diagnosa_' . $pasien['nama'] . '.pdf', ["Attachment" => false]); // false = buka di browser
exit();
