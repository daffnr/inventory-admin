<?php
// init_db.php - Script inisialisasi database db_inventory

$host = '127.0.0.1';
$port = '3306';
$username = 'root';
$password = 'password';

try {
    // 1. Koneksi ke MySQL server tanpa memilih database
    $pdo = new PDO("mysql:host=$host;port=$port;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    
    echo "✔ Terhubung ke server MySQL.\n";

    // 2. Baca dan jalankan schema SQL
    $schemaSql = file_get_contents(__DIR__ . '/database/schema.sql');
    $pdo->exec($schemaSql);
    echo "✔ Schema database 'db_inventory' berhasil dibuat/diinisialisasi.\n";

    // 3. Gunakan db_inventory
    $pdo->exec("USE `db_inventory`");

    // 4. Inisialisasi data awal (Kategori & Barang) jika belum ada
    $checkKategori = $pdo->query("SELECT COUNT(*) as total FROM kategori")->fetch();
    if ($checkKategori['total'] == 0) {
        $stmtKat = $pdo->prepare("INSERT INTO kategori (id, nama_kategori) VALUES (?, ?)");
        $stmtKat->execute([1, 'Elektronik']);
        $stmtKat->execute([2, 'Aksesoris Komputer']);
        $stmtKat->execute([3, 'Peralatan Kantor']);
        echo "✔ Data awal Kategori berhasil ditambahkan.\n";
    }

    $checkBarang = $pdo->query("SELECT COUNT(*) as total FROM barang")->fetch();
    if ($checkBarang['total'] == 0) {
        $stmtBrg = $pdo->prepare("INSERT INTO barang (kode_barang, nama_barang, kategori_id, satuan, harga, stok) VALUES (?, ?, ?, ?, ?, ?)");
        $stmtBrg->execute(['BRG-001', 'Laptop Asus Vivobook', 1, 'Unit', 8500000.00, 10]);
        $stmtBrg->execute(['BRG-002', 'Mouse Wireless Logitech', 2, 'Pcs', 150000.00, 25]);
        $stmtBrg->execute(['BRG-003', 'Keyboard Mechanical RGB', 2, 'Pcs', 450000.00, 15]);
        $stmtBrg->execute(['BRG-004', 'Kertas A4 80gsm Sidu', 3, 'Rim', 48000.00, 50]);
        $stmtBrg->execute(['BRG-005', 'Printer Epson L3210', 1, 'Unit', 2300000.00, 5]);
        echo "✔ Data awal Barang berhasil ditambahkan.\n";
    }

    echo "🎉 Inisialisasi Database Selesai dengan Sukses!\n";
} catch (PDOException $e) {
    echo "❌ Gagal Inisialisasi Database: " . $e->getMessage() . "\n";
    exit(1);
}
