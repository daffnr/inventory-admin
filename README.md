# TES TEKNIS - APLIKASI INVENTORY SEDERHANA (PHP + MySQL)

Aplikasi web manajemen stok barang (inventory) sederhana yang dibangun menggunakan **PHP Native (PDO)** dan **MySQL / MariaDB**. Aplikasi ini dilengkapi dengan pengelolaan Master Data (Barang & Kategori), Transaksi Multi-Item (Barang Masuk & Barang Keluar) dengan pembaruan stok otomatis, validasi batas stok ketat, serta Laporan Stok dan Periode Transaksi.

---

## 🛠️ Teknologi & Fitur Utama

### 1. Master Data
- **Master Kategori**: CRUD (Tambah, Edit, Hapus, Tampil) dengan validasi keterikatan data barang.
- **Master Barang**: Kode Barang, Nama Barang, Kategori (Relasi FK), Satuan, Harga, dan Stok. Dilengkapi fitur pencarian (Search) berdasarkan Kode, Nama, maupun Kategori.

### 2. Transaksi
- **Transaksi Barang Masuk**: Input multi-item barang dalam satu nomor transaksi (`BM-001`). Saat disimpan, **stok barang otomatis bertambah**.
- **Transaksi Barang Keluar**: Input multi-item barang (`BK-001`). Saat disimpan, **stok barang otomatis berkurang**.
- **Validasi Stok Ketat (Stock Protection)**: Transaksi Barang Keluar akan **otomatis ditolak** jika jumlah barang yang diminta melebihi sisa stok yang tersedia di gudang.

### 3. Laporan (Reports)
- **Laporan Stok**: Menampilkan Kode, Nama Barang, Kategori, Satuan, dan Stok. Dilengkapi filter pencarian Nama Barang & Kategori.
- **Laporan Barang Masuk**: Filter rentang tanggal (`Tanggal Dari` - `Tanggal Sampai`) menampilkan No Transaksi, Tanggal, Supplier, Barang, Jumlah, dan **Total Barang** di footer.
- **Laporan Barang Keluar**: Filter rentang tanggal (`Tanggal Dari` - `Tanggal Sampai`) menampilkan No Transaksi, Tanggal, Tujuan, Barang, Jumlah, dan **Total Barang** di footer.

---

## 🚀 Cara Menjalankan Aplikasi

### 1. Persyaratan Sistem
- PHP 8.0 atau yang lebih baru
- MySQL / MariaDB (misalnya XAMPP / Laragon)

### 2. Inisialisasi Database
Jalankan script `init_db.php` melalui terminal/Command Prompt untuk membuat database `db_inventory`, tabel ber-relasi, dan data awal:

```bash
php init_db.php
```

### 3. Jalankan Development Server PHP
Jalankan web server bawaan PHP pada port 8000:

```bash
php -S 127.0.0.1:8000
```

Buka browser dan akses alamat:
👉 **`http://127.0.0.1:8000`**

---

## 🗄️ Struktur Database (`db_inventory`)

- `kategori` (`id`, `nama_kategori`)
- `barang` (`kode_barang`, `nama_barang`, `kategori_id`, `satuan`, `harga`, `stok`)
- `transaksi_masuk` (`no_transaksi`, `tanggal`, `supplier`)
- `transaksi_masuk_detail` (`id`, `no_transaksi`, `kode_barang`, `jumlah`)
- `transaksi_keluar` (`no_transaksi`, `tanggal`, `tujuan`)
- `transaksi_keluar_detail` (`id`, `no_transaksi`, `kode_barang`, `jumlah`)
