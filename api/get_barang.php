<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';

try {
    $pdo = getDBConnection();
    $stmt = $pdo->query("SELECT b.kode_barang, b.nama_barang, b.satuan, b.stok, b.harga, k.nama_kategori 
                         FROM barang b 
                         JOIN kategori k ON b.kategori_id = k.id 
                         ORDER BY b.nama_barang ASC");
    $items = $stmt->fetchAll();
    echo json_encode(['status' => 'success', 'data' => $items]);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
