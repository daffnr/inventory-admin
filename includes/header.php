<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/functions.php';

$pageTitle = isset($pageTitle) ? $pageTitle : 'Inventory App';
$activeMenu = isset($activeMenu) ? $activeMenu : '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle); ?> - Inventory App</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="/assets/css/style.css?v=<?= time(); ?>">
</head>
<body>

<div class="d-flex" id="wrapper">
    <div id="sidebar-backdrop" class="sidebar-backdrop"></div>

    <div id="sidebar-wrapper">
        <div class="sidebar-heading">
            <i class="bi bi-box-seam-fill"></i>
            <span>INVENTORY APP</span>
        </div>
        
        <div class="list-group list-group-flush py-3">
            <a href="/index.php" class="sidebar-link <?= ($activeMenu === 'dashboard') ? 'active' : ''; ?>">
                <i class="bi bi-speedometer2"></i>
                <span>Dashboard</span>
            </a>

            <div class="list-group-heading">Master Data</div>
            <a href="/modules/barang/index.php" class="sidebar-link <?= ($activeMenu === 'barang') ? 'active' : ''; ?>">
                <i class="bi bi-archive-fill"></i>
                <span>Master Barang</span>
            </a>
            <a href="/modules/kategori/index.php" class="sidebar-link <?= ($activeMenu === 'kategori') ? 'active' : ''; ?>">
                <i class="bi bi-tags-fill"></i>
                <span>Master Kategori</span>
            </a>

            <div class="list-group-heading">Transaksi</div>
            <a href="/modules/transaksi_masuk/index.php" class="sidebar-link <?= ($activeMenu === 'transaksi_masuk') ? 'active' : ''; ?>">
                <i class="bi bi-box-arrow-in-down-right text-success"></i>
                <span>Barang Masuk</span>
            </a>
            <a href="/modules/transaksi_keluar/index.php" class="sidebar-link <?= ($activeMenu === 'transaksi_keluar') ? 'active' : ''; ?>">
                <i class="bi bi-box-arrow-up-right text-danger"></i>
                <span>Barang Keluar</span>
            </a>

            <div class="list-group-heading">Laporan</div>
            <a href="/modules/laporan/stok.php" class="sidebar-link <?= ($activeMenu === 'laporan_stok') ? 'active' : ''; ?>">
                <i class="bi bi-file-earmark-bar-graph"></i>
                <span>Laporan Stok</span>
            </a>
            <a href="/modules/laporan/barang_masuk.php" class="sidebar-link <?= ($activeMenu === 'laporan_masuk') ? 'active' : ''; ?>">
                <i class="bi bi-file-earmark-arrow-down"></i>
                <span>Laporan Barang Masuk</span>
            </a>
            <a href="/modules/laporan/barang_keluar.php" class="sidebar-link <?= ($activeMenu === 'laporan_keluar') ? 'active' : ''; ?>">
                <i class="bi bi-file-earmark-arrow-up"></i>
                <span>Laporan Barang Keluar</span>
            </a>
        </div>
    </div>

    <div id="page-content-wrapper">
        <nav class="top-navbar d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
                <button class="btn btn-outline-primary me-2 d-md-none border-0 p-1 px-2" id="sidebarToggle" type="button" aria-label="Toggle Navigation">
                    <i class="bi bi-list fs-3"></i>
                </button>
                <h5 class="m-0 fw-bold text-slate-800 fs-6 fs-md-5"><?= htmlspecialchars($pageTitle); ?></h5>
            </div>
            <div class="d-flex align-items-center gap-2 gap-md-3">
                <div class="d-none d-sm-flex align-items-center text-muted fs-7">
                    <i class="bi bi-calendar-event me-2"></i>
                    <span><?= date('d F Y'); ?></span>
                </div>
                <div class="border-start ps-2 ps-md-3">
                    <span class="badge bg-primary px-2 px-md-3 py-2">
                        <i class="bi bi-person-fill me-1"></i> Admin
                    </span>
                </div>
            </div>
        </nav>

        <main class="main-content">
            <?php include_once __DIR__ . '/alerts.php'; ?>
