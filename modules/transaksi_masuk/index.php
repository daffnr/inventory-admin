<?php
// modules/transaksi_masuk/index.php - List Riwayat Transaksi Barang Masuk
$pageTitle = 'Transaksi Barang Masuk';
$activeMenu = 'transaksi_masuk';

require_once __DIR__ . '/../../includes/header.php';

// Fetch Transaksi Masuk History with total items and total qty
$sql = "SELECT tm.*, 
               COUNT(tmd.id) as total_item, 
               SUM(tmd.jumlah) as total_qty
        FROM transaksi_masuk tm
        LEFT JOIN transaksi_masuk_detail tmd ON tm.no_transaksi = tmd.no_transaksi
        GROUP BY tm.no_transaksi
        ORDER BY tm.tanggal DESC, tm.created_at DESC";
$stmt = $pdo->query($sql);
$transaksis = $stmt->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
    <div>
        <h4 class="fw-bold m-0 text-slate-800">Transaksi Barang Masuk</h4>
        <p class="text-muted small m-0">Riwayat penerimaan barang masuk dari supplier dan penambahan stok otomatis.</p>
    </div>
    <div>
        <a href="create.php" class="btn btn-success shadow-sm">
            <i class="bi bi-plus-lg me-1"></i> Input Transaksi Masuk
        </a>
    </div>
</div>

<div class="card card-table border-0 shadow-sm">
    <div class="card-header bg-white py-3 border-bottom">
        <h6 class="m-0 fw-bold"><i class="bi bi-box-arrow-in-down-right me-2 text-success"></i>Riwayat Transaksi Barang Masuk</h6>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th style="width: 140px;">No Transaksi</th>
                    <th style="width: 130px;">Tanggal</th>
                    <th>Supplier</th>
                    <th class="text-center">Jumlah Variasi Item</th>
                    <th class="text-center">Total Qty Masuk</th>
                    <th class="text-end" style="width: 120px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($transaksis)): ?>
                    <tr>
                        <td colspan="6" class="text-center text-muted py-5">
                            <i class="bi bi-inbox fs-1 d-block mb-2 text-secondary"></i>
                            Belum ada riwayat transaksi barang masuk.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($transaksis as $t): ?>
                        <tr>
                            <td>
                                <span class="badge bg-light text-dark border font-monospace px-2 py-1 fs-7">
                                    <?= htmlspecialchars($t['no_transaksi']); ?>
                                </span>
                            </td>
                            <td><?= format_tanggal($t['tanggal']); ?></td>
                            <td class="fw-semibold text-slate-800"><?= htmlspecialchars($t['supplier']); ?></td>
                            <td class="text-center">
                                <span class="badge bg-light-info text-primary rounded-pill px-3 py-1">
                                    <?= $t['total_item']; ?> Jenis Barang
                                </span>
                            </td>
                            <td class="text-center fw-bold text-success">
                                +<?= $t['total_qty']; ?>
                            </td>
                            <td class="text-end">
                                <a href="detail.php?no=<?= urlencode($t['no_transaksi']); ?>" class="btn btn-sm btn-outline-info" title="Detail Transaksi">
                                    <i class="bi bi-eye me-1"></i> Detail
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
