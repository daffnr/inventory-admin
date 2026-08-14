<?php
// modules/transaksi_keluar/index.php - List Riwayat Transaksi Barang Keluar
$pageTitle = 'Transaksi Barang Keluar';
$activeMenu = 'transaksi_keluar';

require_once __DIR__ . '/../../includes/header.php';

// Fetch Transaksi Keluar History with total items and total qty
$sql = "SELECT tk.*, 
               COUNT(tkd.id) as total_item, 
               SUM(tkd.jumlah) as total_qty
        FROM transaksi_keluar tk
        LEFT JOIN transaksi_keluar_detail tkd ON tk.no_transaksi = tkd.no_transaksi
        GROUP BY tk.no_transaksi
        ORDER BY tk.tanggal DESC, tk.created_at DESC";
$stmt = $pdo->query($sql);
$transaksis = $stmt->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
    <div>
        <h4 class="fw-bold m-0 text-slate-800">Transaksi Barang Keluar</h4>
        <p class="text-muted small m-0">Pengeluaran barang dari gudang dengan validasi ketersediaan stok secara otomatis.</p>
    </div>
    <div>
        <a href="create.php" class="btn btn-danger shadow-sm">
            <i class="bi bi-plus-lg me-1"></i> Input Transaksi Keluar
        </a>
    </div>
</div>

<div class="card card-table border-0 shadow-sm">
    <div class="card-header bg-white py-3 border-bottom">
        <h6 class="m-0 fw-bold"><i class="bi bi-box-arrow-up-right me-2 text-danger"></i>Riwayat Transaksi Barang Keluar</h6>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th style="width: 140px;">No Transaksi</th>
                    <th style="width: 130px;">Tanggal</th>
                    <th>Tujuan / Penerima</th>
                    <th class="text-center">Jumlah Variasi Item</th>
                    <th class="text-center">Total Qty Keluar</th>
                    <th class="text-end" style="width: 120px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($transaksis)): ?>
                    <tr>
                        <td colspan="6" class="text-center text-muted py-5">
                            <i class="bi bi-inbox fs-1 d-block mb-2 text-secondary"></i>
                            Belum ada riwayat transaksi barang keluar.
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
                            <td class="fw-semibold text-slate-800"><?= htmlspecialchars($t['tujuan']); ?></td>
                            <td class="text-center">
                                <span class="badge bg-light-danger text-danger rounded-pill px-3 py-1">
                                    <?= $t['total_item']; ?> Jenis Barang
                                </span>
                            </td>
                            <td class="text-center fw-bold text-danger">
                                -<?= $t['total_qty']; ?>
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
