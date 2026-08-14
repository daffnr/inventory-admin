<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function sanitize($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

function format_rupiah($number) {
    return 'Rp ' . number_format($number, 0, ',', '.');
}

function format_tanggal($dateStr) {
    if (!$dateStr) return '-';
    return date('d-m-Y', strtotime($dateStr));
}

function set_flash_message($type, $message) {
    $_SESSION['flash_message'] = [
        'type' => $type,
        'message' => $message
    ];
}

function generate_no_transaksi($type = 'BM') {
    $pdo = getDBConnection();
    $prefix = ($type === 'BM') ? 'BM-' : 'BK-';
    $table = ($type === 'BM') ? 'transaksi_masuk' : 'transaksi_keluar';
    
    $stmt = $pdo->prepare("SELECT no_transaksi FROM $table WHERE no_transaksi LIKE ? ORDER BY no_transaksi DESC LIMIT 1");
    $stmt->execute([$prefix . '%']);
    $lastRecord = $stmt->fetch();
    
    if ($lastRecord) {
        $numberPart = (int) substr($lastRecord['no_transaksi'], strlen($prefix));
        $newNumber = $numberPart + 1;
    } else {
        $newNumber = 1;
    }
    
    return $prefix . str_pad($newNumber, 3, '0', STR_PAD_LEFT);
}

function generate_kode_barang() {
    $pdo = getDBConnection();
    $stmt = $pdo->query("SELECT kode_barang FROM barang WHERE kode_barang LIKE 'BRG-%' ORDER BY kode_barang DESC LIMIT 1");
    $lastRecord = $stmt->fetch();
    
    if ($lastRecord) {
        $numberPart = (int) substr($lastRecord['kode_barang'], 4);
        $newNumber = $numberPart + 1;
    } else {
        $newNumber = 1;
    }
    
    return 'BRG-' . str_pad($newNumber, 3, '0', STR_PAD_LEFT);
}
