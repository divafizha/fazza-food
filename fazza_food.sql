-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 12 Agu 2025 pada 10.56
-- Versi server: 10.4.28-MariaDB
-- Versi PHP: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `salsa_ff`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `admin`
--

CREATE TABLE `admin` (
  `id_admin` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `admin`
--

INSERT INTO `admin` (`id_admin`, `username`, `password`) VALUES
(1, 'admin', 'password');

-- --------------------------------------------------------

--
-- Struktur dari tabel `distribusi`
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
  `id_distributor` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `distribusi`
--

INSERT INTO `distribusi` (`id_distribusi`, `nama_toko`, `tanggal_distribusi`, `id_produk`, `jumlah_pesanan`, `tanggal_pesanan`, `status_pengiriman`, `nama_distributor`, `alamat_distributor`, `id_distributor`) VALUES
(3, NULL, NULL, 4901, 600, '2025-09-06', 'Diproses', 'Toko Merpati', 'Solo', NULL),
(7, NULL, NULL, 4602, 300, '2025-08-15', 'Diproses', 'Toko Jaya', 'Semarang', NULL),
(11, NULL, NULL, 4901, 500, '2025-08-10', 'Diproses', 'Toko Pink', 'Banjarnegara', NULL),
(12, NULL, NULL, 4602, 200, '2025-08-10', 'Diproses', 'Toko Pink', 'Banjarnegara', NULL),
(15, NULL, NULL, 4901, 500, '2025-08-21', 'Diproses', 'Toko Biru', 'Kaliwiro', NULL),
(16, NULL, NULL, 4602, 400, '2025-08-21', 'Diproses', 'Toko Biru', 'Kaliwiro', NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `distribusi_detail`
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
-- Struktur dari tabel `jadwal`
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
-- Dumping data untuk tabel `jadwal`
--

INSERT INTO `jadwal` (`id_jadwal`, `tanggal`, `waktu`, `waktu_mulai`, `waktu_selesai`, `jenis_kegiatan`, `id_admin`) VALUES
(3, '2025-08-07', NULL, '13:22:00', '16:22:00', 'Produksi', 1),
(4, '2025-08-01', NULL, '13:30:00', '15:30:00', 'Produksi', 1),
(5, '2025-08-02', NULL, '13:30:00', '15:30:00', 'Produksi', 1),
(6, '2025-08-01', NULL, '16:30:00', '19:30:00', 'Pengemasan', 1),
(7, '2025-08-02', NULL, '17:30:00', '19:30:00', 'Pengemasan', 1),
(8, '2025-08-05', NULL, '08:00:00', '12:00:00', 'Produksi', 1),
(9, '2025-08-08', NULL, '12:00:00', '19:00:00', 'Produksi', 1),
(10, '2025-08-09', NULL, '10:00:00', '12:30:00', 'Produksi', 1),
(12, '2025-08-11', NULL, '07:00:00', '12:00:00', 'Produksi', 1),
(13, '2025-08-10', NULL, '10:07:00', '13:00:00', 'Produksi', 1);

-- --------------------------------------------------------

--
-- Struktur dari tabel `laporan`
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
  `rekap_jadwal` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `pekerja_lepas`
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
-- Dumping data untuk tabel `pekerja_lepas`
--

INSERT INTO `pekerja_lepas` (`id_pekerja`, `nama_pekerja`, `kontak`, `alamat`, `status_pembayaran`, `id_admin`) VALUES
(1, 'Zahwa', '085729269751', 'Kalibeber', 'Belum Dibayar', 1),
(2, 'Fatimah', '088216476342', 'Mojotengah', 'Belum Dibayar', 1),
(3, 'Wanti', '082164903970', 'Krasak', 'Belum Dibayar', 1),
(4, 'Ardi', '082232192297', 'Garung', 'Belum Dibayar', 1),
(5, 'Edwin', '089922347764', 'Krasak', 'Dibayar', 1),
(6, 'Yudhis', '087766547789', 'Kalibeber', 'Belum Dibayar', 1),
(7, 'Salma', '085729269751', 'kalibeber', 'Belum Dibayar', 1),
(8, 'slsa', '0999', 'wonosobo', 'Belum Dibayar', 1);

-- --------------------------------------------------------

--
-- Struktur dari tabel `pembayaran_gaji`
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
-- Struktur dari tabel `pengambilan_stok`
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
-- Struktur dari tabel `pengambilan_stok_pekerja`
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
-- Dumping data untuk tabel `pengambilan_stok_pekerja`
--

INSERT INTO `pengambilan_stok_pekerja` (`id_pengambilan`, `id_pekerja`, `id_stok`, `tanggal_ambil`, `jumlah_kg`, `total_gaji`, `status`) VALUES
(1, 4, 5, '2025-08-08', 20, 50000, 'Sedang dikerjakan'),
(2, 7, 6, '2025-08-08', 30, 75000, 'Sedang dikerjakan'),
(3, 2, 6, '2025-08-08', 20, 50000, 'Sedang dikerjakan'),
(4, 5, 7, '2025-08-09', 40, 100000, 'Sedang dikerjakan'),
(5, 3, 7, '2025-08-09', 50, 125000, 'Sedang dikerjakan');

-- --------------------------------------------------------

--
-- Struktur dari tabel `produk`
--

CREATE TABLE `produk` (
  `id_produk` int(11) NOT NULL,
  `nama_produk` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `produk`
--

INSERT INTO `produk` (`id_produk`, `nama_produk`) VALUES
(4602, 'Agar-Agar Pita'),
(4901, 'Agar-Agar Pelangi');

-- --------------------------------------------------------

--
-- Struktur dari tabel `produksi`
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
-- Dumping data untuk tabel `produksi`
--

INSERT INTO `produksi` (`id_produksi`, `id_produk`, `id_jadwal`, `id_admin`, `jumlah_produksi`, `tgl_produksi`, `jumlah_dikemas`, `jumlah_reject`) VALUES
(3, 4901, 4, 1, 500, '2025-08-01', 450, 50),
(4, 4602, 5, 1, 200, '2025-08-02', 198, 2),
(5, 4602, 8, 1, 300, '2025-08-05', 297, 3),
(6, 4602, 9, 1, 100, '2025-08-08', 90, 10),
(7, 4602, 10, 1, 300, '2025-08-09', 285, 15),
(8, 4602, 12, 1, 300, '2025-08-11', 285, 15),
(9, 4602, 13, 1, 900, '2025-08-10', 890, 10);

--
-- Trigger `produksi`
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
-- Struktur dari tabel `riwayat_gaji`
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
-- Dumping data untuk tabel `riwayat_gaji`
--

INSERT INTO `riwayat_gaji` (`id_gaji`, `id_pekerja`, `tanggal`, `berat_barang_kg`, `tarif_per_kg`, `total_gaji`, `keterangan`) VALUES
(1, 4, '2025-08-08', 20.00, 2500, 50000, 'Belum Dibayar'),
(2, 7, '2025-08-08', 30.00, 2500, 75000, 'Belum Dibayar'),
(3, 2, '2025-08-08', 20.00, 2500, 50000, 'Belum Dibayar'),
(4, 5, '2025-08-09', 40.00, 2500, 100000, 'Dibayar'),
(5, 3, '2025-08-09', 50.00, 2500, 125000, 'Belum Dibayar');

-- --------------------------------------------------------

--
-- Struktur dari tabel `stok`
--

CREATE TABLE `stok` (
  `id_stok` int(11) NOT NULL,
  `id_produk` int(11) NOT NULL,
  `id_produksi` int(11) DEFAULT NULL,
  `jumlah_stok` int(11) DEFAULT NULL,
  `sisa_stok` int(11) DEFAULT NULL,
  `status_stok` varchar(50) DEFAULT NULL,
  `jumlah_reject` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `stok`
--

INSERT INTO `stok` (`id_stok`, `id_produk`, `id_produksi`, `jumlah_stok`, `sisa_stok`, `status_stok`, `jumlah_reject`) VALUES
(1, 4901, 3, 50, NULL, 'Sudah dipacking', NULL),
(2, 4901, 3, 100, NULL, 'Sudah dipacking', NULL),
(3, 4901, 3, 300, NULL, 'Siap dipacking', NULL),
(4, 4602, 5, 297, NULL, 'Siap dipacking', NULL),
(5, 4602, 4, 80, NULL, 'Siap dikemas', NULL),
(6, 4602, 6, 35, NULL, 'Siap dikemas', NULL),
(7, 4602, 7, 195, NULL, 'Siap dikemas', NULL),
(8, 4602, 8, 100, NULL, 'Siap dikemas', NULL),
(9, 4602, 8, 150, NULL, 'Siap dikemas', NULL),
(10, 4602, 9, 870, NULL, 'Siap dikemas', NULL);

-- --------------------------------------------------------

--
-- Stand-in struktur untuk tampilan `v_produksi_fix`
-- (Lihat di bawah untuk tampilan aktual)
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
-- Struktur untuk view `v_produksi_fix`
--
DROP TABLE IF EXISTS `v_produksi_fix`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_produksi_fix`  AS SELECT `pr`.`id_produksi` AS `id_produksi`, `pr`.`id_produk` AS `id_produk`, `pr`.`id_jadwal` AS `id_jadwal`, `pr`.`jumlah_produksi` AS `jumlah_produksi`, `pr`.`jumlah_dikemas` AS `jumlah_dikemas`, `pr`.`jumlah_reject` AS `jumlah_reject`, coalesce(`pr`.`tgl_produksi`,`j`.`tanggal`) AS `tanggal_produksi` FROM (`produksi` `pr` left join `jadwal` `j` on(`j`.`id_jadwal` = `pr`.`id_jadwal`)) ;

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id_admin`);

--
-- Indeks untuk tabel `distribusi`
--
ALTER TABLE `distribusi`
  ADD PRIMARY KEY (`id_distribusi`),
  ADD KEY `id_produk` (`id_produk`);

--
-- Indeks untuk tabel `distribusi_detail`
--
ALTER TABLE `distribusi_detail`
  ADD PRIMARY KEY (`id_detail`),
  ADD KEY `id_distribusi` (`id_distribusi`),
  ADD KEY `id_produk` (`id_produk`),
  ADD KEY `id_stok` (`id_stok`);

--
-- Indeks untuk tabel `jadwal`
--
ALTER TABLE `jadwal`
  ADD PRIMARY KEY (`id_jadwal`),
  ADD KEY `fk_jadwal_admin` (`id_admin`);

--
-- Indeks untuk tabel `laporan`
--
ALTER TABLE `laporan`
  ADD PRIMARY KEY (`id_laporan`);

--
-- Indeks untuk tabel `pekerja_lepas`
--
ALTER TABLE `pekerja_lepas`
  ADD PRIMARY KEY (`id_pekerja`);

--
-- Indeks untuk tabel `pembayaran_gaji`
--
ALTER TABLE `pembayaran_gaji`
  ADD PRIMARY KEY (`id_pembayaran`),
  ADD KEY `id_pekerja` (`id_pekerja`);

--
-- Indeks untuk tabel `pengambilan_stok`
--
ALTER TABLE `pengambilan_stok`
  ADD PRIMARY KEY (`id_pengambilan`),
  ADD KEY `id_stok` (`id_stok`),
  ADD KEY `id_pekerja` (`id_pekerja`);

--
-- Indeks untuk tabel `pengambilan_stok_pekerja`
--
ALTER TABLE `pengambilan_stok_pekerja`
  ADD PRIMARY KEY (`id_pengambilan`),
  ADD KEY `id_pekerja` (`id_pekerja`),
  ADD KEY `id_stok` (`id_stok`);

--
-- Indeks untuk tabel `produk`
--
ALTER TABLE `produk`
  ADD PRIMARY KEY (`id_produk`);

--
-- Indeks untuk tabel `produksi`
--
ALTER TABLE `produksi`
  ADD PRIMARY KEY (`id_produksi`),
  ADD KEY `id_produk` (`id_produk`),
  ADD KEY `fk_produksi_jadwal` (`id_jadwal`);

--
-- Indeks untuk tabel `riwayat_gaji`
--
ALTER TABLE `riwayat_gaji`
  ADD PRIMARY KEY (`id_gaji`),
  ADD KEY `id_pekerja` (`id_pekerja`);

--
-- Indeks untuk tabel `stok`
--
ALTER TABLE `stok`
  ADD PRIMARY KEY (`id_stok`),
  ADD KEY `id_produk` (`id_produk`),
  ADD KEY `fk_stok_produksi` (`id_produksi`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `admin`
--
ALTER TABLE `admin`
  MODIFY `id_admin` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `distribusi`
--
ALTER TABLE `distribusi`
  MODIFY `id_distribusi` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT untuk tabel `distribusi_detail`
--
ALTER TABLE `distribusi_detail`
  MODIFY `id_detail` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `jadwal`
--
ALTER TABLE `jadwal`
  MODIFY `id_jadwal` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT untuk tabel `laporan`
--
ALTER TABLE `laporan`
  MODIFY `id_laporan` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `pekerja_lepas`
--
ALTER TABLE `pekerja_lepas`
  MODIFY `id_pekerja` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT untuk tabel `pembayaran_gaji`
--
ALTER TABLE `pembayaran_gaji`
  MODIFY `id_pembayaran` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `pengambilan_stok`
--
ALTER TABLE `pengambilan_stok`
  MODIFY `id_pengambilan` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `pengambilan_stok_pekerja`
--
ALTER TABLE `pengambilan_stok_pekerja`
  MODIFY `id_pengambilan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT untuk tabel `produk`
--
ALTER TABLE `produk`
  MODIFY `id_produk` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4902;

--
-- AUTO_INCREMENT untuk tabel `produksi`
--
ALTER TABLE `produksi`
  MODIFY `id_produksi` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT untuk tabel `riwayat_gaji`
--
ALTER TABLE `riwayat_gaji`
  MODIFY `id_gaji` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT untuk tabel `stok`
--
ALTER TABLE `stok`
  MODIFY `id_stok` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `distribusi`
--
ALTER TABLE `distribusi`
  ADD CONSTRAINT `distribusi_ibfk_1` FOREIGN KEY (`id_produk`) REFERENCES `produk` (`id_produk`);

--
-- Ketidakleluasaan untuk tabel `distribusi_detail`
--
ALTER TABLE `distribusi_detail`
  ADD CONSTRAINT `distribusi_detail_ibfk_1` FOREIGN KEY (`id_distribusi`) REFERENCES `distribusi` (`id_distribusi`) ON DELETE CASCADE,
  ADD CONSTRAINT `distribusi_detail_ibfk_2` FOREIGN KEY (`id_produk`) REFERENCES `produk` (`id_produk`),
  ADD CONSTRAINT `distribusi_detail_ibfk_3` FOREIGN KEY (`id_stok`) REFERENCES `stok` (`id_stok`);

--
-- Ketidakleluasaan untuk tabel `jadwal`
--
ALTER TABLE `jadwal`
  ADD CONSTRAINT `fk_jadwal_admin` FOREIGN KEY (`id_admin`) REFERENCES `admin` (`id_admin`);

--
-- Ketidakleluasaan untuk tabel `pembayaran_gaji`
--
ALTER TABLE `pembayaran_gaji`
  ADD CONSTRAINT `pembayaran_gaji_ibfk_1` FOREIGN KEY (`id_pekerja`) REFERENCES `pekerja_lepas` (`id_pekerja`);

--
-- Ketidakleluasaan untuk tabel `pengambilan_stok`
--
ALTER TABLE `pengambilan_stok`
  ADD CONSTRAINT `pengambilan_stok_ibfk_1` FOREIGN KEY (`id_stok`) REFERENCES `stok` (`id_stok`),
  ADD CONSTRAINT `pengambilan_stok_ibfk_2` FOREIGN KEY (`id_pekerja`) REFERENCES `pekerja_lepas` (`id_pekerja`);

--
-- Ketidakleluasaan untuk tabel `pengambilan_stok_pekerja`
--
ALTER TABLE `pengambilan_stok_pekerja`
  ADD CONSTRAINT `pengambilan_stok_pekerja_ibfk_1` FOREIGN KEY (`id_pekerja`) REFERENCES `pekerja_lepas` (`id_pekerja`) ON DELETE CASCADE,
  ADD CONSTRAINT `pengambilan_stok_pekerja_ibfk_2` FOREIGN KEY (`id_stok`) REFERENCES `stok` (`id_stok`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `produksi`
--
ALTER TABLE `produksi`
  ADD CONSTRAINT `fk_produksi_jadwal` FOREIGN KEY (`id_jadwal`) REFERENCES `jadwal` (`id_jadwal`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `produksi_ibfk_1` FOREIGN KEY (`id_produk`) REFERENCES `produk` (`id_produk`);

--
-- Ketidakleluasaan untuk tabel `riwayat_gaji`
--
ALTER TABLE `riwayat_gaji`
  ADD CONSTRAINT `riwayat_gaji_ibfk_1` FOREIGN KEY (`id_pekerja`) REFERENCES `pekerja_lepas` (`id_pekerja`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `stok`
--
ALTER TABLE `stok`
  ADD CONSTRAINT `fk_stok_produksi` FOREIGN KEY (`id_produksi`) REFERENCES `produksi` (`id_produksi`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `stok_ibfk_1` FOREIGN KEY (`id_produk`) REFERENCES `produk` (`id_produk`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
