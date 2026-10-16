-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 09 Feb 2026 pada 16.13
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `peminjaman_inventaris`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `inventaris`
--

CREATE TABLE `inventaris` (
  `id_inventaris` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `foto` varchar(255) DEFAULT 'default.jpg',
  `jenis` enum('meja','kursi','kipas_angin','ruang_rapat','kendaraan') NOT NULL,
  `jumlah_total` int(11) NOT NULL,
  `jumlah_tersedia` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `inventaris`
--

INSERT INTO `inventaris` (`id_inventaris`, `nama`, `foto`, `jenis`, `jumlah_total`, `jumlah_tersedia`) VALUES
(1, 'Meja Rapat Oval', 'meja rapat oval.jpg', 'meja', 5, 5),
(2, 'Meja Kerja Kayu', 'meja kerja kayu.jpg', 'meja', 10, 10),
(3, 'Kursi Lipat', 'kursi lipat.jpg', 'kursi', 20, 18),
(4, 'Kursi Kantor Ergonomis', 'kursi kantor ergonomis.jpg', 'kursi', 8, 8),
(6, 'Kipas Gantung Ruang Rapat', 'kipas gantung ruang rapat.jpg', 'kipas_angin', 3, 3),
(7, 'Ruang Rapat A', 'ruang rapat a.jpg', 'ruang_rapat', 1, 1),
(8, 'Ruang Rapat B', 'ruang rapat b.jpg', 'ruang_rapat', 1, 1),
(9, 'Mobil Avanza Silver', 'mobil avanza silver.jpg', 'kendaraan', 1, 1),
(10, 'Motor Beat Hitam', 'motor beat hitam.jpg', 'kendaraan', 2, 2),
(11, 'Kipas Angin Standing 16\"', 'kipas_angin_standing_16_kipas_angin_2.jpg', 'kipas_angin', 10, 10);

-- --------------------------------------------------------

--
-- Struktur dari tabel `peminjaman`
--

CREATE TABLE `peminjaman` (
  `id_peminjaman` int(11) NOT NULL,
  `id` int(11) NOT NULL,
  `id_inventaris` int(11) NOT NULL,
  `jumlah_pinjam` int(11) NOT NULL,
  `tgl_pinjam` date NOT NULL,
  `tgl_kembali` date DEFAULT NULL,
  `status` enum('diajukan','disetujui','dikembalikan','ditolak') DEFAULT 'diajukan',
  `catatan` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `peminjaman`
--

INSERT INTO `peminjaman` (`id_peminjaman`, `id`, `id_inventaris`, `jumlah_pinjam`, `tgl_pinjam`, `tgl_kembali`, `status`, `catatan`) VALUES
(1, 2, 3, 2, '2026-01-15', NULL, 'disetujui', 'Untuk meeting tim'),
(2, 3, 7, 1, '2026-01-10', NULL, 'disetujui', 'Meeting dengan client'),
(4, 2, 2, 3, '2026-01-27', NULL, 'diajukan', NULL),
(5, 2, 11, 3, '2026-02-09', NULL, 'diajukan', NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nama_lengkap` varchar(100) NOT NULL,
  `role` enum('admin','pegawai') NOT NULL DEFAULT 'pegawai'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `nama_lengkap`, `role`) VALUES
(1, 'admin', '$2a$12$NCLJ7rF8SY0DKad6UMt6BemyYdmo3s8PM2/ypc/CsE9z2MTT0Wf1e', 'Admin Utama', 'admin'),
(2, 'dela', '$2a$12$KWG45S9kXZ6oHfdaqwAR5.LRiQ5NACb/QHzd5KHE8ls3AEkOcxj26', 'Dela Arnis Amalia', 'pegawai'),
(3, 'alda', '$2a$12$CBJEYXQQpo9PLzolGf.gXuEiXSVIy6MNWYlqqvTWTvSaLMmUndvoK', 'Robiathul Ada Wiah', 'pegawai');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `inventaris`
--
ALTER TABLE `inventaris`
  ADD PRIMARY KEY (`id_inventaris`);

--
-- Indeks untuk tabel `peminjaman`
--
ALTER TABLE `peminjaman`
  ADD PRIMARY KEY (`id_peminjaman`),
  ADD KEY `id` (`id`),
  ADD KEY `id_inventaris` (`id_inventaris`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `inventaris`
--
ALTER TABLE `inventaris`
  MODIFY `id_inventaris` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT untuk tabel `peminjaman`
--
ALTER TABLE `peminjaman`
  MODIFY `id_peminjaman` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `peminjaman`
--
ALTER TABLE `peminjaman`
  ADD CONSTRAINT `peminjaman_ibfk_1` FOREIGN KEY (`id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `peminjaman_ibfk_2` FOREIGN KEY (`id_inventaris`) REFERENCES `inventaris` (`id_inventaris`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
