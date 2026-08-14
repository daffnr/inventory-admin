<?php
// modules/laporan/barang_masuk.php - Laporan Barang Masuk (Filter Tanggal & Total Barang)
$pageTitle = 'Laporan Barang Masuk';
$activeMenu = 'laporan_masuk';

require_once __DIR__ . '/../../includes/header.php';

// Filter Date Parameters
$tglDari = isset($_GET['tgl_dari']) ? sanitize($_GET['tgl_dari']) : '';
$tglSampai = isset($_GET['tgl_sampai']) ? sanitize($_GET['tgl_sampai']) : '';

// Build SQL Query joining header and details
$sql = "SELECT tm.no_transaksi, tm.tanggal, tm.supplier, b.nama_barang, b.satuan, tmd.jumlah
        FROM transaksi_masuk_detail tmd
        JOIN transaksi_masuk tm ON tmd.no_transaksi = tm.no_transaksi
        JOIN barang b ON tmd.kode_barang = b.kode_barang
        WHERE 1=1";
$params = [];

if (!empty($tglDari)) {
    $sql .= " AND tm.tanggal >= ?";
    $params[] = $tglDari;
}

if (!empty($tglSampai)) {
    $sql .= " AND tm.tanggal <= ?";
    $params[] = $tglSampai;
}

$sql .= " ORDER BY tm.tanggal DESC, tm.no_transaksi DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$reportItems = $stmt->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
    <div>
        <h4 class="fw-bold m-0 text-slate-800">Laporan Barang Masuk</h4>
        <p class="text-muted small m-0">Rekapitulasi riwayat rincian barang masuk berdasarkan periode tanggal.</p>
    </div>
    <div>
        <button onclick="window.print()" class="btn btn-outline-primary btn-print-hide shadow-sm">
            <i class="bi bi-printer me-1"></i> Cetak Laporan
        </button>
    </div>
</div>

<div class="card card-table border-0 shadow-sm">
    <!-- Date Filter Form -->
    <div class="card-header bg-white py-3 border-bottom btn-print-hide">
        <form action="barang_masuk.php" method="GET" class="row g-3 align-items-center">
            <div class="col-md-4 col-sm-12">
                <label for="tgl_dari" class="form-label fs-7 fw-semibold text-muted mb-1">Tanggal Dari</label>
                <input type="date" id="tgl_dari" name="tgl_dari" class="form-control" value="<?= htmlspecialchars($tglDari); ?>">
            </div>
            <div class="col-md-4 col-sm-12">
                <label for="tgl_sampai" class="form-label fs-7 fw-semibold text-muted mb-1">Tanggal Sampai</label>
                <input type="date" id="tgl_sampai" name="tgl_sampai" class="form-control" value="<?= htmlspecialchars($tglSampai); ?>">
            </div>
            <div class="col-md-4 col-sm-12 d-flex align-items-end gap-2" style="height: 68px;">
                <button type="submit" class="btn btn-success flex-grow-1">
                    <i class="bi bi-filter me-1"></i> Tampilkan Laporan
                </button>
                <?php if (!empty($tglDari) || !empty($tglSampai)): ?>
                    <a href="barang_masuk.php" class="btn btn-outline-secondary">Reset</a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- Active Filter Badge Info -->
    <?php if (!empty($tglDari) || !empty($tglSampai)): ?>
        <div class="p-3 bg-light-info border-bottom d-flex align-items-center gap-2">
            <i class="bi bi-info-circle text-primary fs-5"></i>
            <span class="fs-7 text-dark">
                Periode Laporan: 
                <strong><?= $tglDari ? format_tanggal($tglDari) : 'Awal'; ?></strong> s/d 
                <strong><?= $tglSampai ? format_tanggal($tglSampai) : 'Hari Ini'; ?></strong>
            </span>
        </div>
    <?php endif; ?>

    <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th style="width: 140px;" class="text-center">No Transaksi</th>
                    <th style="width: 120px;" class="text-center">Tanggal</th>
                    <th>Supplier</th>
                    <th>Nama Barang</th>
                    <th style="width: 140px;" class="text-center">Jumlah</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($reportItems)): ?>
                    <tr>
                        <td colspan="5" class="text-center text-muted py-5">
                            <i class="bi bi-file-earmark-x fs-1 d-block mb-2 text-secondary"></i>
                            Tidak ada transaksi barang masuk pada periode tanggal ini.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php 
                    $totalBarangMasuk = 0;
                    foreach ($reportItems as $row): 
                        $totalBarangMasuk += $row['jumlah'];
                    ?>
                        <tr>
                            <td class="text-center font-monospace fw-bold">
                                <?= htmlspecialchars($row['no_transaksi']); ?>
                            </td>
                            <td class="text-center"><?= format_tanggal($row['tanggal']); ?></td>
                            <td class="fw-medium text-slate-700"><?= htmlspecialchars($row['supplier']); ?></td>
                            <td class="fw-semibold text-slate-800"><?= htmlspecialchars($row['nama_barang']); ?></td>
                            <td class="text-center fw-bold text-success">+<?= $row['jumlah']; ?> <?= htmlspecialchars($row['satuan']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
            <?php if (!empty($reportItems)): ?>
                <tfoot>
                    <tr class="table-light fw-bold">
                        <td colspan="4" class="text-end fs-6">Total Barang:</td>
                        <td class="text-center text-success fs-5">+<?= number_format($totalBarangMasuk, 0, ',', '.'); ?></td>
                    </tr>
                </tfoot>
            <?php endif; ?>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
