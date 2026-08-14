<?php
// index.php - Dashboard utama Aplikasi Inventory
$pageTitle = 'Dashboard Summary';
$activeMenu = 'dashboard';

require_once __DIR__ . '/includes/header.php';

// Statistics Calculation
$totalBarang = $pdo->query("SELECT COUNT(*) FROM barang")->fetchColumn();
$totalKategori = $pdo->query("SELECT COUNT(*) FROM kategori")->fetchColumn();

$blnIni = date('Y-m');
$stmtMasuk = $pdo->prepare("SELECT COALESCE(SUM(tmd.jumlah), 0) FROM transaksi_masuk_detail tmd 
                            JOIN transaksi_masuk tm ON tmd.no_transaksi = tm.no_transaksi 
                            WHERE DATE_FORMAT(tm.tanggal, '%Y-%m') = ?");
$stmtMasuk->execute([$blnIni]);
$totalMasukBulanIni = $stmtMasuk->fetchColumn();

$stmtKeluar = $pdo->prepare("SELECT COALESCE(SUM(tkd.jumlah), 0) FROM transaksi_keluar_detail tkd 
                             JOIN transaksi_keluar tk ON tkd.no_transaksi = tk.no_transaksi 
                             WHERE DATE_FORMAT(tk.tanggal, '%Y-%m') = ?");
$stmtKeluar->execute([$blnIni]);
$totalKeluarBulanIni = $stmtKeluar->fetchColumn();

// Low Stock Items (< 5)
$lowStockStmt = $pdo->query("SELECT b.*, k.nama_kategori FROM barang b 
                             JOIN kategori k ON b.kategori_id = k.id 
                             WHERE b.stok <= 5 ORDER BY b.stok ASC LIMIT 5");
$lowStockItems = $lowStockStmt->fetchAll();

// Recent Incoming Transactions
$recentMasuk = $pdo->query("SELECT tm.*, COUNT(tmd.id) as item_count, SUM(tmd.jumlah) as total_qty 
                            FROM transaksi_masuk tm 
                            JOIN transaksi_masuk_detail tmd ON tm.no_transaksi = tmd.no_transaksi 
                            GROUP BY tm.no_transaksi 
                            ORDER BY tm.created_at DESC LIMIT 5")->fetchAll();

// Recent Outgoing Transactions
$recentKeluar = $pdo->query("SELECT tk.*, COUNT(tkd.id) as item_count, SUM(tkd.jumlah) as total_qty 
                             FROM transaksi_keluar tk 
                             JOIN transaksi_keluar_detail tkd ON tk.no_transaksi = tkd.no_transaksi 
                             GROUP BY tk.no_transaksi 
                             ORDER BY tk.created_at DESC LIMIT 5")->fetchAll();
?>

<!-- Welcome Banner -->
<div class="card border-0 shadow-sm mb-4 bg-primary text-white overflow-hidden position-relative">
    <div class="card-body p-4 position-relative z-1">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h4 class="fw-bold mb-2">Selamat Datang di Sistem Inventory Admin 👋</h4>
                <p class="mb-0 text-white-50">Kelola master barang, transaksi penerimaan & pengeluaran barang, serta laporan stok secara real-time dan terintegrasi.</p>
            </div>
            <div class="col-md-4 text-md-end mt-3 mt-md-0">
                <a href="/modules/transaksi_masuk/create.php" class="btn btn-light me-2 text-primary fw-bold">
                    <i class="bi bi-plus-circle me-1"></i> Barang Masuk
                </a>
                <a href="/modules/transaksi_keluar/create.php" class="btn btn-outline-light fw-bold">
                    <i class="bi bi-dash-circle me-1"></i> Barang Keluar
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Stat Cards -->
<div class="row g-3 mb-4">
    <div class="col-md-3 col-sm-6">
        <div class="card stat-card p-3">
            <div class="d-flex align-items-center">
                <div class="stat-icon bg-light-primary me-3">
                    <i class="bi bi-archive-fill"></i>
                </div>
                <div>
                    <h3 class="fw-bold m-0 text-slate-800"><?= $totalBarang; ?></h3>
                    <span class="text-muted fs-7">Total Jenis Barang</span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3 col-sm-6">
        <div class="card stat-card p-3">
            <div class="d-flex align-items-center">
                <div class="stat-icon bg-light-info me-3">
                    <i class="bi bi-tags-fill"></i>
                </div>
                <div>
                    <h3 class="fw-bold m-0 text-slate-800"><?= $totalKategori; ?></h3>
                    <span class="text-muted fs-7">Kategori Barang</span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3 col-sm-6">
        <div class="card stat-card p-3">
            <div class="d-flex align-items-center">
                <div class="stat-icon bg-light-success me-3">
                    <i class="bi bi-box-arrow-in-down-right"></i>
                </div>
                <div>
                    <h3 class="fw-bold m-0 text-success">+<?= $totalMasukBulanIni; ?></h3>
                    <span class="text-muted fs-7">Masuk (Bulan Ini)</span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3 col-sm-6">
        <div class="card stat-card p-3">
            <div class="d-flex align-items-center">
                <div class="stat-icon bg-light-danger me-3">
                    <i class="bi bi-box-arrow-up-right"></i>
                </div>
                <div>
                    <h3 class="fw-bold m-0 text-danger">-<?= $totalKeluarBulanIni; ?></h3>
                    <span class="text-muted fs-7">Keluar (Bulan Ini)</span>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Low Stock Alert Widget -->
    <div class="col-lg-5">
        <div class="card card-table border-0 shadow-sm h-100">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                <h6 class="m-0 fw-bold text-danger"><i class="bi bi-exclamation-triangle-fill me-2"></i>Peringatan Stok Menipis (Stok ≤ 5)</h6>
                <a href="/modules/laporan/stok.php" class="btn btn-sm btn-link text-decoration-none fs-7">Lihat Semua</a>
            </div>
            <div class="card-body p-0">
                <?php if (empty($lowStockItems)): ?>
                    <div class="text-center text-muted py-4">
                        <i class="bi bi-check-circle-fill text-success fs-1 d-block mb-1"></i>
                        Semua stok barang dalam kondisi aman.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Kode</th>
                                    <th>Nama Barang</th>
                                    <th class="text-center">Sisa Stok</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($lowStockItems as $ls): ?>
                                    <tr>
                                        <td><span class="badge bg-light text-dark border font-monospace"><?= htmlspecialchars($ls['kode_barang']); ?></span></td>
                                        <td class="fw-semibold text-slate-800"><?= htmlspecialchars($ls['nama_barang']); ?></td>
                                        <td class="text-center">
                                            <?php if ($ls['stok'] <= 0): ?>
                                                <span class="badge bg-danger">Habis (0)</span>
                                            <?php else: ?>
                                                <span class="badge bg-warning text-dark"><?= $ls['stok']; ?> <?= htmlspecialchars($ls['satuan']); ?></span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Quick Activity Summary -->
    <div class="col-lg-7">
        <div class="card card-table border-0 shadow-sm h-100">
            <div class="card-header bg-white py-3 border-bottom">
                <ul class="nav nav-tabs card-header-tabs" id="recentTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active fw-semibold text-success" id="masuk-tab" data-bs-toggle="tab" data-bs-target="#masuk-tab-pane" type="button" role="tab">
                            <i class="bi bi-box-arrow-in-down-right me-1"></i> Transaksi Masuk Terakhir
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fw-semibold text-danger" id="keluar-tab" data-bs-toggle="tab" data-bs-target="#keluar-tab-pane" type="button" role="tab">
                            <i class="bi bi-box-arrow-up-right me-1"></i> Transaksi Keluar Terakhir
                        </button>
                    </li>
                </ul>
            </div>
            <div class="card-body p-0">
                <div class="tab-content" id="recentTabContent">
                    <!-- Tab Masuk -->
                    <div class="tab-pane fade show active" id="masuk-tab-pane" role="tabpanel" tabindex="0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>No Transaksi</th>
                                        <th>Tanggal</th>
                                        <th>Supplier</th>
                                        <th class="text-center">Total Qty</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($recentMasuk)): ?>
                                        <tr><td colspan="4" class="text-center text-muted py-4">Belum ada transaksi masuk.</td></tr>
                                    <?php else: ?>
                                        <?php foreach ($recentMasuk as $rm): ?>
                                            <tr>
                                                <td class="font-monospace fw-bold text-success"><?= htmlspecialchars($rm['no_transaksi']); ?></td>
                                                <td><?= format_tanggal($rm['tanggal']); ?></td>
                                                <td class="fw-semibold"><?= htmlspecialchars($rm['supplier']); ?></td>
                                                <td class="text-center fw-bold text-success">+<?= $rm['total_qty']; ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Tab Keluar -->
                    <div class="tab-pane fade" id="keluar-tab-pane" role="tabpanel" tabindex="0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>No Transaksi</th>
                                        <th>Tanggal</th>
                                        <th>Tujuan</th>
                                        <th class="text-center">Total Qty</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($recentKeluar)): ?>
                                        <tr><td colspan="4" class="text-center text-muted py-4">Belum ada transaksi keluar.</td></tr>
                                    <?php else: ?>
                                        <?php foreach ($recentKeluar as $rk): ?>
                                            <tr>
                                                <td class="font-monospace fw-bold text-danger"><?= htmlspecialchars($rk['no_transaksi']); ?></td>
                                                <td><?= format_tanggal($rk['tanggal']); ?></td>
                                                <td class="fw-semibold"><?= htmlspecialchars($rk['tujuan']); ?></td>
                                                <td class="text-center fw-bold text-danger">-<?= $rk['total_qty']; ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
