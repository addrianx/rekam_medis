-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 21, 2025 at 05:06 PM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `rekam_medis`
--

-- --------------------------------------------------------

--
-- Table structure for table `dokter`
--

CREATE TABLE `dokter` (
  `id_dokter` int(11) NOT NULL,
  `nama` varchar(100) DEFAULT NULL,
  `spesialisasi` varchar(50) DEFAULT NULL,
  `nomor_telepon` varchar(15) DEFAULT NULL,
  `role` varchar(1) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `dokter`
--

INSERT INTO `dokter` (`id_dokter`, `nama`, `spesialisasi`, `nomor_telepon`, `role`, `password`) VALUES
(1, 'dr. susan melati', 'dokter umum', '081111222333', '1', 'dokmum123'),
(2, 'dr. ahmad fauzi', 'spesialis jantung', '082222333444', '1', 'spj123'),
(3, 'dr. lina rahayu', 'spesialis anak', '083333444555', '1', 'spa123'),
(6, 'Devina Karamoy', 'Gigi', '091222333444', '', '12345');

-- --------------------------------------------------------

--
-- Table structure for table `obat`
--

CREATE TABLE `obat` (
  `id_obat` int(11) NOT NULL,
  `nama_obat` varchar(100) DEFAULT NULL,
  `dosis` varchar(50) DEFAULT NULL,
  `harga` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `obat`
--

INSERT INTO `obat` (`id_obat`, `nama_obat`, `dosis`, `harga`) VALUES
(1, 'paracetamol', '500 mg, 3x sehari', 25000.00),
(2, 'amoxicillin', '250 mg, 2x sehari', 35000.00),
(3, 'captopril', '25 mg, 1x sehari', 40000.00),
(5, 'Antasida', '500 mg,3x sehari', 20000.00),
(6, 'Acetazolamide', '250mg, 2x sehari', 50000.00);

-- --------------------------------------------------------

--
-- Table structure for table `pasien`
--

CREATE TABLE `pasien` (
  `id_pasien` int(11) NOT NULL,
  `nama` varchar(100) DEFAULT NULL,
  `tanggal_lahir` date DEFAULT NULL,
  `jenis_kelamin` varchar(10) DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `no_hp` varchar(15) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(1) NOT NULL,
  `is_deleted` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pasien`
--

INSERT INTO `pasien` (`id_pasien`, `nama`, `tanggal_lahir`, `jenis_kelamin`, `alamat`, `no_hp`, `password`, `role`, `is_deleted`) VALUES
(2, 'ani wijaya', '1985-11-22', 'perempuan', 'jl. diponegoro no. 5, bandung', '085678901234', '', '', 0),
(3, 'candra putra', '2000-03-07', 'laki-laki', 'jl. gatot subroto no. 20, surabaya', '087890123456', '', '', 0),
(5, 'Arya Wiguna', '1993-08-12', 'Laki-laki', 'kp pasir jengjing garut', '08123412345', '', '', 0),
(6, 'Ula Syifa', '2000-06-18', 'Perempuan', 'bandung', '08321345234', '', '', 0),
(7, 'Sadasd', '2025-06-19', 'Laki-laki', 'sadadsda', '098765345323', '', '', 0),
(8, 'Ucup', '2025-06-25', 'Laki-laki', 'kadungora', '09876543210', '', '', 0);

-- --------------------------------------------------------

--
-- Table structure for table `petugas`
--

CREATE TABLE `petugas` (
  `id_petugas` int(11) NOT NULL,
  `nama` varchar(100) DEFAULT NULL,
  `no_hp` varchar(15) DEFAULT NULL,
  `role` int(11) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `petugas`
--

INSERT INTO `petugas` (`id_petugas`, `nama`, `no_hp`, `role`, `password`) VALUES
(1, 'siti aminah', '081999888777', 2, 'staff123'),
(2, 'rudi hartono', '082888777666', 2, 'staff456'),
(3, 'dewi sartika', '083777666555', 2, 'staff789');

-- --------------------------------------------------------

--
-- Table structure for table `rekam_medis`
--

CREATE TABLE `rekam_medis` (
  `id_rekam` int(11) NOT NULL,
  `tanggal` date DEFAULT NULL,
  `diagnosa` text DEFAULT NULL,
  `id_pasien` int(11) DEFAULT NULL,
  `id_dokter` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rekam_medis`
--

INSERT INTO `rekam_medis` (`id_rekam`, `tanggal`, `diagnosa`, `id_pasien`, `id_dokter`) VALUES
(2, '2025-06-02', 'hipertensi ringan', 2, 2),
(3, '2025-06-03', 'infeksi saluran pernapasan', 3, 3),
(5, '2025-12-10', 'Infeksi Pernafasan', 5, 2),
(6, '2025-06-07', 'Hipertensi', 6, 2),
(8, '2025-06-19', 'Batu Ginjal', 5, 1),
(9, '2025-06-21', 'kangker', 8, 1);

-- --------------------------------------------------------

--
-- Table structure for table `resep`
--

CREATE TABLE `resep` (
  `id_resep` int(11) NOT NULL,
  `id_rekam` int(11) DEFAULT NULL,
  `id_obat` int(11) DEFAULT NULL,
  `jumlah` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `resep`
--

INSERT INTO `resep` (`id_resep`, `id_rekam`, `id_obat`, `jumlah`) VALUES
(2, 2, 3, 30),
(3, 3, 2, 15),
(5, 5, 2, 2),
(6, 6, 2, 3),
(8, 8, 3, 3),
(9, 9, 5, 2);

-- --------------------------------------------------------

--
-- Table structure for table `tindakan_medis`
--

CREATE TABLE `tindakan_medis` (
  `id_tindakan` int(11) NOT NULL,
  `id_rekam` int(11) DEFAULT NULL,
  `nama_tindakan` varchar(100) DEFAULT NULL,
  `biaya` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tindakan_medis`
--

INSERT INTO `tindakan_medis` (`id_tindakan`, `id_rekam`, `nama_tindakan`, `biaya`) VALUES
(2, 2, 'tes tekanan darah', 75000.00),
(3, 3, 'nebulizer', 100000.00),
(5, 6, 'Diuretik', 1500000.00),
(7, 8, 'Operasi', 50000000.00),
(8, 9, 'rawat', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `dokter`
--
ALTER TABLE `dokter`
  ADD PRIMARY KEY (`id_dokter`);

--
-- Indexes for table `obat`
--
ALTER TABLE `obat`
  ADD PRIMARY KEY (`id_obat`);

--
-- Indexes for table `pasien`
--
ALTER TABLE `pasien`
  ADD PRIMARY KEY (`id_pasien`);

--
-- Indexes for table `petugas`
--
ALTER TABLE `petugas`
  ADD PRIMARY KEY (`id_petugas`);

--
-- Indexes for table `rekam_medis`
--
ALTER TABLE `rekam_medis`
  ADD PRIMARY KEY (`id_rekam`),
  ADD KEY `id_pasien` (`id_pasien`),
  ADD KEY `id_dokter` (`id_dokter`);

--
-- Indexes for table `resep`
--
ALTER TABLE `resep`
  ADD PRIMARY KEY (`id_resep`),
  ADD KEY `id_rekam` (`id_rekam`),
  ADD KEY `id_obat` (`id_obat`);

--
-- Indexes for table `tindakan_medis`
--
ALTER TABLE `tindakan_medis`
  ADD PRIMARY KEY (`id_tindakan`),
  ADD KEY `id_rekam` (`id_rekam`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `dokter`
--
ALTER TABLE `dokter`
  MODIFY `id_dokter` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `obat`
--
ALTER TABLE `obat`
  MODIFY `id_obat` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `pasien`
--
ALTER TABLE `pasien`
  MODIFY `id_pasien` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `petugas`
--
ALTER TABLE `petugas`
  MODIFY `id_petugas` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `rekam_medis`
--
ALTER TABLE `rekam_medis`
  MODIFY `id_rekam` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `resep`
--
ALTER TABLE `resep`
  MODIFY `id_resep` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `tindakan_medis`
--
ALTER TABLE `tindakan_medis`
  MODIFY `id_tindakan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `rekam_medis`
--
ALTER TABLE `rekam_medis`
  ADD CONSTRAINT `rekam_medis_ibfk_1` FOREIGN KEY (`id_pasien`) REFERENCES `pasien` (`id_pasien`),
  ADD CONSTRAINT `rekam_medis_ibfk_2` FOREIGN KEY (`id_dokter`) REFERENCES `dokter` (`id_dokter`);

--
-- Constraints for table `resep`
--
ALTER TABLE `resep`
  ADD CONSTRAINT `resep_ibfk_1` FOREIGN KEY (`id_rekam`) REFERENCES `rekam_medis` (`id_rekam`),
  ADD CONSTRAINT `resep_ibfk_2` FOREIGN KEY (`id_obat`) REFERENCES `obat` (`id_obat`);

--
-- Constraints for table `tindakan_medis`
--
ALTER TABLE `tindakan_medis`
  ADD CONSTRAINT `tindakan_medis_ibfk_1` FOREIGN KEY (`id_rekam`) REFERENCES `rekam_medis` (`id_rekam`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
