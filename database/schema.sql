CREATE DATABASE IF NOT EXISTS `db_inventory` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `db_inventory`;

CREATE TABLE IF NOT EXISTS `kategori` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `nama_kategori` VARCHAR(100) NOT NULL UNIQUE,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `barang` (
  `kode_barang` VARCHAR(50) PRIMARY KEY,
  `nama_barang` VARCHAR(150) NOT NULL,
  `kategori_id` INT NOT NULL,
  `satuan` VARCHAR(50) NOT NULL,
  `harga` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
  `stok` INT NOT NULL DEFAULT 0,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_barang_kategori` FOREIGN KEY (`kategori_id`) REFERENCES `kategori` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `transaksi_masuk` (
  `no_transaksi` VARCHAR(50) PRIMARY KEY,
  `tanggal` DATE NOT NULL,
  `supplier` VARCHAR(150) NOT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `transaksi_masuk_detail` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `no_transaksi` VARCHAR(50) NOT NULL,
  `kode_barang` VARCHAR(50) NOT NULL,
  `jumlah` INT NOT NULL,
  CONSTRAINT `fk_detail_masuk_header` FOREIGN KEY (`no_transaksi`) REFERENCES `transaksi_masuk` (`no_transaksi`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_detail_masuk_barang` FOREIGN KEY (`kode_barang`) REFERENCES `barang` (`kode_barang`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `transaksi_keluar` (
  `no_transaksi` VARCHAR(50) PRIMARY KEY,
  `tanggal` DATE NOT NULL,
  `tujuan` VARCHAR(150) NOT NULL DEFAULT 'Umum',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `transaksi_keluar_detail` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `no_transaksi` VARCHAR(50) NOT NULL,
  `kode_barang` VARCHAR(50) NOT NULL,
  `jumlah` INT NOT NULL,
  CONSTRAINT `fk_detail_keluar_header` FOREIGN KEY (`no_transaksi`) REFERENCES `transaksi_keluar` (`no_transaksi`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_detail_keluar_barang` FOREIGN KEY (`kode_barang`) REFERENCES `barang` (`kode_barang`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
