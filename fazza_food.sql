-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 20, 2025 at 10:02 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `fazza_food`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `id_admin` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`id_admin`, `username`, `password`) VALUES
(1, 'admin', 'password');

-- --------------------------------------------------------

--
-- Table structure for table `distribusi`
--

CREATE TABLE `distribusi` (
  `id_distribusi` int(11) NOT NULL,
  `nama_toko` varchar(100) DEFAULT NULL,
  `tanggal_distribusi` date DEFAULT NULL,
  `id_produk` int(11) NOT NULL,
  `jumlah_pesanan` int(11) DEFAULT NULL,
  `tanggal_pesanan` date DEFAULT NULL,
  `status_pengiriman` varchar(50) DEFAULT NULL,
  `nama_distributor` varchar(100) DEFAULT NULL,
  `alamat_distributor` varchar(255) DEFAULT NULL,
  `id_distributor` int(11) DEFAULT NULL,
  `id_admin` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `distribusi`
--

INSERT INTO `distribusi` (`id_distribusi`, `nama_toko`, `tanggal_distribusi`, `id_produk`, `jumlah_pesanan`, `tanggal_pesanan`, `status_pengiriman`, `nama_distributor`, `alamat_distributor`, `id_distributor`, `id_admin`) VALUES
(23, NULL, NULL, 4901, 150, '2025-10-22', 'Dikirim', 'Toko Amin', 'Klaten', NULL, NULL),
(24, NULL, NULL, 4602, 150, '2025-10-22', 'Dikirim', 'Toko Amin', 'Klaten', NULL, NULL),
(25, NULL, NULL, 4901, 200, '2025-10-20', 'Diproses', 'Toko Sumber Rejeki', 'Pasar Pagi Wonosobo', NULL, NULL),
(26, NULL, NULL, 4602, 200, '2025-10-20', 'Diproses', 'Toko Sumber Rejeki', 'Pasar Pagi Wonosobo', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `distribusi_detail`
--

CREATE TABLE `distribusi_detail` (
  `id_detail` int(11) NOT NULL,
  `id_distribusi` int(11) NOT NULL,
  `id_produk` int(11) NOT NULL,
  `id_stok` int(11) NOT NULL,
  `jumlah_kg` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jadwal`
--

CREATE TABLE `jadwal` (
  `id_jadwal` int(11) NOT NULL,
  `tanggal` date DEFAULT NULL,
  `waktu` time DEFAULT NULL,
  `waktu_mulai` time DEFAULT NULL,
  `waktu_selesai` time DEFAULT NULL,
  `jenis_kegiatan` varchar(100) DEFAULT NULL,
  `id_admin` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `jadwal`
--

INSERT INTO `jadwal` (`id_jadwal`, `tanggal`, `waktu`, `waktu_mulai`, `waktu_selesai`, `jenis_kegiatan`, `id_admin`) VALUES
(14, '2025-10-18', NULL, '07:00:00', '16:00:00', 'Produksi', 1),
(15, '2025-10-18', NULL, '08:00:00', '16:00:00', 'Pengemasan', 1),
(16, '2025-10-20', NULL, '13:00:00', '16:00:00', 'Distribusi', 1),
(17, '2025-11-12', NULL, '07:00:00', '16:00:00', 'Distribusi', 1),
(18, '2025-10-21', NULL, '08:00:00', '16:00:00', 'Pengemasan', 1),
(19, '2025-10-19', NULL, '05:00:00', '12:00:00', 'Produksi', 1),
(20, '2025-09-21', NULL, '03:00:00', '12:00:00', 'Produksi', 1);

-- --------------------------------------------------------

--
-- Table structure for table `laporan`
--

CREATE TABLE `laporan` (
  `id_laporan` int(11) NOT NULL,
  `kategori_laporan` varchar(50) DEFAULT NULL,
  `periode_laporan` varchar(50) DEFAULT NULL,
  `total_pesanan` int(11) DEFAULT NULL,
  `total_dikemas` int(11) DEFAULT NULL,
  `total_stok` int(11) DEFAULT NULL,
  `total_distribusi` int(11) DEFAULT NULL,
  `total_reject` int(11) DEFAULT NULL,
  `total_gaji` int(11) DEFAULT NULL,
  `total_produksi` int(11) DEFAULT NULL,
  `rekap_jadwal` text DEFAULT NULL,
  `id_admin` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pekerja_lepas`
--

CREATE TABLE `pekerja_lepas` (
  `id_pekerja` int(11) NOT NULL,
  `nama_pekerja` varchar(100) NOT NULL,
  `kontak` varchar(20) DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `status_pembayaran` varchar(50) DEFAULT NULL,
  `id_admin` int(11) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pekerja_lepas`
--

INSERT INTO `pekerja_lepas` (`id_pekerja`, `nama_pekerja`, `kontak`, `alamat`, `status_pembayaran`, `id_admin`) VALUES
(10, 'Enjel', '0987654321', 'Yogyakarta', 'Belum Dibayar', 1),
(11, 'Rizka', '1234567890', 'Ngasinan', 'Belum Dibayar', 1),
(12, 'Nanang', '09865432', 'Leksono', 'Dibayar', 1),
(13, 'Salbiah', '08123456789', 'Kauman, RT 01/RW 04, Wonosobo', 'Belum Dibayar', 1);

-- --------------------------------------------------------

--
-- Table structure for table `pembayaran_gaji`
--

CREATE TABLE `pembayaran_gaji` (
  `id_pembayaran` int(11) NOT NULL,
  `id_pekerja` int(11) NOT NULL,
  `tanggal_pembayaran` date DEFAULT NULL,
  `upah_per_kg` int(11) DEFAULT NULL,
  `berat_dikemas_kg` decimal(10,2) DEFAULT NULL,
  `total_gaji` int(11) DEFAULT NULL,
  `status_pembayaran` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pengambilan_stok`
--

CREATE TABLE `pengambilan_stok` (
  `id_pengambilan` int(11) NOT NULL,
  `id_stok` int(11) NOT NULL,
  `id_pekerja` int(11) NOT NULL,
  `jumlah_diambil` int(11) DEFAULT NULL,
  `tanggal_pengambilan` date DEFAULT NULL,
  `jumlah_ambil` int(11) DEFAULT NULL,
  `tanggal_ambil` datetime DEFAULT NULL,
  `keperluan` varchar(100) DEFAULT NULL,
  `id_admin` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pengambilan_stok_pekerja`
--

CREATE TABLE `pengambilan_stok_pekerja` (
  `id_pengambilan` int(11) NOT NULL,
  `id_pekerja` int(11) DEFAULT NULL,
  `id_stok` int(11) DEFAULT NULL,
  `tanggal_ambil` date DEFAULT NULL,
  `jumlah_kg` int(11) DEFAULT NULL,
  `total_gaji` bigint(20) DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pengambilan_stok_pekerja`
--

INSERT INTO `pengambilan_stok_pekerja` (`id_pengambilan`, `id_pekerja`, `id_stok`, `tanggal_ambil`, `jumlah_kg`, `total_gaji`, `status`) VALUES
(7, 10, 8, '2025-10-20', 50, 125000, 'Sedang dikerjakan'),
(8, 12, 13, '2025-10-20', 50, 125000, 'Sedang dikerjakan'),
(9, 11, 14, '2025-10-20', 50, 125000, 'Sedang dikerjakan'),
(10, 13, 14, '2025-10-20', 100, 250000, 'Sedang dikerjakan');

-- --------------------------------------------------------

--
-- Table structure for table `produk`
--

CREATE TABLE `produk` (
  `id_produk` int(11) NOT NULL,
  `nama_produk` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `produk`
--

INSERT INTO `produk` (`id_produk`, `nama_produk`) VALUES
(4602, 'Agar-Agar Pita'),
(4901, 'Agar-Agar Pelangi');

-- --------------------------------------------------------

--
-- Table structure for table `produksi`
--

CREATE TABLE `produksi` (
  `id_produksi` int(11) NOT NULL,
  `id_produk` int(11) NOT NULL,
  `id_jadwal` int(11) DEFAULT NULL,
  `id_admin` int(11) DEFAULT NULL,
  `jumlah_produksi` int(11) DEFAULT NULL,
  `tgl_produksi` date DEFAULT NULL,
  `jumlah_dikemas` int(11) DEFAULT NULL,
  `jumlah_reject` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `produksi`
--

INSERT INTO `produksi` (`id_produksi`, `id_produk`, `id_jadwal`, `id_admin`, `jumlah_produksi`, `tgl_produksi`, `jumlah_dikemas`, `jumlah_reject`) VALUES
(11, 4602, 19, 1, 500, '2025-10-19', 449, 51),
(12, 4602, 14, 1, 200, '2025-10-18', 152, 48),
(13, 4901, 20, 1, 350, '2025-09-21', 350, 0);

--
-- Triggers `produksi`
--
DELIMITER $$
CREATE TRIGGER `trg_produksi_fill_date_ins` BEFORE INSERT ON `produksi` FOR EACH ROW BEGIN
  IF NEW.tgl_produksi IS NULL AND NEW.id_jadwal IS NOT NULL THEN
    SET NEW.tgl_produksi = (
      SELECT tanggal FROM jadwal WHERE id_jadwal = NEW.id_jadwal LIMIT 1
    );
  END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_produksi_fill_date_upd` BEFORE UPDATE ON `produksi` FOR EACH ROW BEGIN
  IF (NEW.tgl_produksi IS NULL OR NEW.tgl_produksi = '') AND NEW.id_jadwal IS NOT NULL THEN
    SET NEW.tgl_produksi = (
      SELECT tanggal FROM jadwal WHERE id_jadwal = NEW.id_jadwal LIMIT 1
    );
  END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `riwayat_gaji`
--

CREATE TABLE `riwayat_gaji` (
  `id_gaji` int(11) NOT NULL,
  `id_pekerja` int(11) DEFAULT NULL,
  `tanggal` date DEFAULT NULL,
  `berat_barang_kg` decimal(10,2) DEFAULT NULL,
  `tarif_per_kg` int(11) DEFAULT NULL,
  `total_gaji` bigint(20) DEFAULT NULL,
  `keterangan` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `riwayat_gaji`
--

INSERT INTO `riwayat_gaji` (`id_gaji`, `id_pekerja`, `tanggal`, `berat_barang_kg`, `tarif_per_kg`, `total_gaji`, `keterangan`) VALUES
(7, 10, '2025-10-20', 50.00, 2500, 125000, 'Belum Dibayar'),
(8, 12, '2025-10-20', 50.00, 2500, 125000, 'Dibayar'),
(9, 11, '2025-10-20', 50.00, 2500, 125000, 'Belum Dibayar'),
(10, 13, '2025-10-20', 100.00, 2500, 250000, 'Belum Dibayar');

-- --------------------------------------------------------

--
-- Table structure for table `stok`
--

CREATE TABLE `stok` (
  `id_stok` int(11) NOT NULL,
  `id_produk` int(11) NOT NULL,
  `id_produksi` int(11) DEFAULT NULL,
  `id_admin` int(11) DEFAULT NULL,
  `jumlah_stok` int(11) DEFAULT NULL,
  `sisa_stok` int(11) DEFAULT NULL,
  `status_stok` varchar(50) DEFAULT NULL,
  `jumlah_reject` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `stok`
--

INSERT INTO `stok` (`id_stok`, `id_produk`, `id_produksi`, `id_admin`, `jumlah_stok`, `sisa_stok`, `status_stok`, `jumlah_reject`) VALUES
(6, 4602, NULL, NULL, 35, NULL, 'Siap dikemas', NULL),
(7, 4602, NULL, NULL, 195, NULL, 'Siap dikemas', NULL),
(8, 4602, NULL, NULL, 50, NULL, 'Siap dikemas', NULL),
(11, 4901, NULL, NULL, 150, NULL, 'Siap dikemas', NULL),
(12, 4602, 12, NULL, 100, NULL, 'Siap dikemas', NULL),
(13, 4602, 12, NULL, 2, NULL, 'Siap dipacking', NULL),
(14, 4901, 13, NULL, 200, NULL, 'Siap dikemas', NULL);

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_produksi_fix`
-- (See below for the actual view)
--
CREATE TABLE `v_produksi_fix` (
`id_produksi` int(11)
,`id_produk` int(11)
,`id_jadwal` int(11)
,`jumlah_produksi` int(11)
,`jumlah_dikemas` int(11)
,`jumlah_reject` int(11)
,`tanggal_produksi` date
);

-- --------------------------------------------------------

--
-- Structure for view `v_produksi_fix`
--
DROP TABLE IF EXISTS `v_produksi_fix`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_produksi_fix`  AS SELECT `pr`.`id_produksi` AS `id_produksi`, `pr`.`id_produk` AS `id_produk`, `pr`.`id_jadwal` AS `id_jadwal`, `pr`.`jumlah_produksi` AS `jumlah_produksi`, `pr`.`jumlah_dikemas` AS `jumlah_dikemas`, `pr`.`jumlah_reject` AS `jumlah_reject`, coalesce(`pr`.`tgl_produksi`,`j`.`tanggal`) AS `tanggal_produksi` FROM (`produksi` `pr` left join `jadwal` `j` on(`j`.`id_jadwal` = `pr`.`id_jadwal`)) ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id_admin`);

--
-- Indexes for table `distribusi`
--
ALTER TABLE `distribusi`
  ADD PRIMARY KEY (`id_distribusi`),
  ADD KEY `id_produk` (`id_produk`),
  ADD KEY `id_admin` (`id_admin`);

--
-- Indexes for table `distribusi_detail`
--
ALTER TABLE `distribusi_detail`
  ADD PRIMARY KEY (`id_detail`),
  ADD KEY `id_distribusi` (`id_distribusi`),
  ADD KEY `id_produk` (`id_produk`),
  ADD KEY `id_stok` (`id_stok`);

--
-- Indexes for table `jadwal`
--
ALTER TABLE `jadwal`
  ADD PRIMARY KEY (`id_jadwal`),
  ADD KEY `fk_jadwal_admin` (`id_admin`);

--
-- Indexes for table `laporan`
--
ALTER TABLE `laporan`
  ADD PRIMARY KEY (`id_laporan`),
  ADD KEY `id_admin` (`id_admin`);

--
-- Indexes for table `pekerja_lepas`
--
ALTER TABLE `pekerja_lepas`
  ADD PRIMARY KEY (`id_pekerja`),
  ADD KEY `id_admin` (`id_admin`);

--
-- Indexes for table `pembayaran_gaji`
--
ALTER TABLE `pembayaran_gaji`
  ADD PRIMARY KEY (`id_pembayaran`),
  ADD KEY `id_pekerja` (`id_pekerja`);

--
-- Indexes for table `pengambilan_stok`
--
ALTER TABLE `pengambilan_stok`
  ADD PRIMARY KEY (`id_pengambilan`),
  ADD KEY `id_stok` (`id_stok`),
  ADD KEY `id_pekerja` (`id_pekerja`);

--
-- Indexes for table `pengambilan_stok_pekerja`
--
ALTER TABLE `pengambilan_stok_pekerja`
  ADD PRIMARY KEY (`id_pengambilan`),
  ADD KEY `id_pekerja` (`id_pekerja`),
  ADD KEY `id_stok` (`id_stok`);

--
-- Indexes for table `produk`
--
ALTER TABLE `produk`
  ADD PRIMARY KEY (`id_produk`);

--
-- Indexes for table `produksi`
--
ALTER TABLE `produksi`
  ADD PRIMARY KEY (`id_produksi`),
  ADD KEY `id_produk` (`id_produk`),
  ADD KEY `fk_produksi_jadwal` (`id_jadwal`),
  ADD KEY `id_admin` (`id_admin`);

--
-- Indexes for table `riwayat_gaji`
--
ALTER TABLE `riwayat_gaji`
  ADD PRIMARY KEY (`id_gaji`),
  ADD KEY `id_pekerja` (`id_pekerja`);

--
-- Indexes for table `stok`
--
ALTER TABLE `stok`
  ADD PRIMARY KEY (`id_stok`),
  ADD KEY `id_produk` (`id_produk`),
  ADD KEY `fk_stok_produksi` (`id_produksi`),
  ADD KEY `id_admin` (`id_admin`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `id_admin` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `distribusi`
--
ALTER TABLE `distribusi`
  MODIFY `id_distribusi` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `distribusi_detail`
--
ALTER TABLE `distribusi_detail`
  MODIFY `id_detail` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jadwal`
--
ALTER TABLE `jadwal`
  MODIFY `id_jadwal` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `laporan`
--
ALTER TABLE `laporan`
  MODIFY `id_laporan` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pekerja_lepas`
--
ALTER TABLE `pekerja_lepas`
  MODIFY `id_pekerja` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `pembayaran_gaji`
--
ALTER TABLE `pembayaran_gaji`
  MODIFY `id_pembayaran` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pengambilan_stok`
--
ALTER TABLE `pengambilan_stok`
  MODIFY `id_pengambilan` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pengambilan_stok_pekerja`
--
ALTER TABLE `pengambilan_stok_pekerja`
  MODIFY `id_pengambilan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `produk`
--
ALTER TABLE `produk`
  MODIFY `id_produk` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4902;

--
-- AUTO_INCREMENT for table `produksi`
--
ALTER TABLE `produksi`
  MODIFY `id_produksi` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `riwayat_gaji`
--
ALTER TABLE `riwayat_gaji`
  MODIFY `id_gaji` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `stok`
--
ALTER TABLE `stok`
  MODIFY `id_stok` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `distribusi`
--
ALTER TABLE `distribusi`
  ADD CONSTRAINT `distribusi_ibfk_1` FOREIGN KEY (`id_produk`) REFERENCES `produk` (`id_produk`);

--
-- Constraints for table `distribusi_detail`
--
ALTER TABLE `distribusi_detail`
  ADD CONSTRAINT `distribusi_detail_ibfk_1` FOREIGN KEY (`id_distribusi`) REFERENCES `distribusi` (`id_distribusi`) ON DELETE CASCADE,
  ADD CONSTRAINT `distribusi_detail_ibfk_2` FOREIGN KEY (`id_produk`) REFERENCES `produk` (`id_produk`),
  ADD CONSTRAINT `distribusi_detail_ibfk_3` FOREIGN KEY (`id_stok`) REFERENCES `stok` (`id_stok`);

--
-- Constraints for table `jadwal`
--
ALTER TABLE `jadwal`
  ADD CONSTRAINT `fk_jadwal_admin` FOREIGN KEY (`id_admin`) REFERENCES `admin` (`id_admin`);

--
-- Constraints for table `pembayaran_gaji`
--
ALTER TABLE `pembayaran_gaji`
  ADD CONSTRAINT `pembayaran_gaji_ibfk_1` FOREIGN KEY (`id_pekerja`) REFERENCES `pekerja_lepas` (`id_pekerja`);

--
-- Constraints for table `pengambilan_stok`
--
ALTER TABLE `pengambilan_stok`
  ADD CONSTRAINT `pengambilan_stok_ibfk_1` FOREIGN KEY (`id_stok`) REFERENCES `stok` (`id_stok`),
  ADD CONSTRAINT `pengambilan_stok_ibfk_2` FOREIGN KEY (`id_pekerja`) REFERENCES `pekerja_lepas` (`id_pekerja`);

--
-- Constraints for table `pengambilan_stok_pekerja`
--
ALTER TABLE `pengambilan_stok_pekerja`
  ADD CONSTRAINT `pengambilan_stok_pekerja_ibfk_1` FOREIGN KEY (`id_pekerja`) REFERENCES `pekerja_lepas` (`id_pekerja`) ON DELETE CASCADE,
  ADD CONSTRAINT `pengambilan_stok_pekerja_ibfk_2` FOREIGN KEY (`id_stok`) REFERENCES `stok` (`id_stok`) ON DELETE CASCADE;

--
-- Constraints for table `produksi`
--
ALTER TABLE `produksi`
  ADD CONSTRAINT `fk_produksi_jadwal` FOREIGN KEY (`id_jadwal`) REFERENCES `jadwal` (`id_jadwal`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `produksi_ibfk_1` FOREIGN KEY (`id_produk`) REFERENCES `produk` (`id_produk`);

--
-- Constraints for table `riwayat_gaji`
--
ALTER TABLE `riwayat_gaji`
  ADD CONSTRAINT `riwayat_gaji_ibfk_1` FOREIGN KEY (`id_pekerja`) REFERENCES `pekerja_lepas` (`id_pekerja`) ON DELETE CASCADE;

--
-- Constraints for table `stok`
--
ALTER TABLE `stok`
  ADD CONSTRAINT `fk_stok_produksi` FOREIGN KEY (`id_produksi`) REFERENCES `produksi` (`id_produksi`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `stok_ibfk_1` FOREIGN KEY (`id_produk`) REFERENCES `produk` (`id_produk`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
