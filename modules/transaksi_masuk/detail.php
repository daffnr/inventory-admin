<?php
// modules/transaksi_masuk/detail.php - Detail Transaksi Barang Masuk
$pageTitle = 'Detail Transaksi Barang Masuk';
$activeMenu = 'transaksi_masuk';

require_once __DIR__ . '/../../includes/header.php';

$no = isset($_GET['no']) ? sanitize($_GET['no']) : '';
if (empty($no)) {
    set_flash_message('danger', 'Nomor Transaksi tidak valid.');
    header('Location: index.php');
    exit;
}

// Fetch Header
$stmt = $pdo->prepare("SELECT * FROM transaksi_masuk WHERE no_transaksi = ?");
$stmt->execute([$no]);
$header = $stmt->fetch();

if (!$header) {
    set_flash_message('danger', 'Data transaksi tidak ditemukan.');
    header('Location: index.php');
    exit;
}

// Fetch Items
$stmtItems = $pdo->prepare("SELECT tmd.*, b.nama_barang, b.satuan, k.nama_kategori 
                            FROM transaksi_masuk_detail tmd 
                            JOIN barang b ON tmd.kode_barang = b.kode_barang 
                            JOIN kategori k ON b.kategori_id = k.id 
                            WHERE tmd.no_transaksi = ? 
                            ORDER BY tmd.id ASC");
$stmtItems->execute([$no]);
$details = $stmtItems->fetchAll();
?>

<div class="row justify-content-center">
    <div class="col-lg-9">
        <div class="card card-table border-0 shadow-sm">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center">
                    <a href="index.php" class="btn btn-sm btn-outline-secondary me-3 btn-print-hide"><i class="bi bi-arrow-left"></i> Kembali</a>
                    <h5 class="m-0 fw-bold"><i class="bi bi-file-text me-2 text-success"></i>Detail Transaksi Barang Masuk</h5>
                </div>
                <button onclick="window.print()" class="btn btn-sm btn-outline-primary btn-print-hide">
                    <i class="bi bi-printer me-1"></i> Cetak Detail
                </button>
            </div>

            <div class="card-body p-4">
                <div class="row mb-4 p-3 bg-light rounded border">
                    <div class="col-md-4">
                        <span class="text-muted fs-7 d-block">No Transaksi:</span>
                        <span class="fw-bold font-monospace fs-5 text-success"><?= htmlspecialchars($header['no_transaksi']); ?></span>
                    </div>
                    <div class="col-md-4">
                        <span class="text-muted fs-7 d-block">Tanggal Transaksi:</span>
                        <span class="fw-semibold text-slate-800"><?= format_tanggal($header['tanggal']); ?></span>
                    </div>
                    <div class="col-md-4">
                        <span class="text-muted fs-7 d-block">Supplier / Vendor:</span>
                        <span class="fw-semibold text-slate-800"><?= htmlspecialchars($header['supplier']); ?></span>
                    </div>
                </div>

                <h6 class="fw-bold mb-3"><i class="bi bi-box-seam me-2"></i>Daftar Barang Masuk</h6>

                <div class="table-responsive mb-4">
                    <table class="table table-bordered align-middle">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 50px;" class="text-center">No</th>
                                <th style="width: 120px;">Kode</th>
                                <th>Nama Barang</th>
                                <th>Kategori</th>
                                <th class="text-center" style="width: 100px;">Satuan</th>
                                <th class="text-center" style="width: 130px;">Jumlah Masuk</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $noIdx = 1;
                            $totalQty = 0;
                            foreach ($details as $d): 
                                $totalQty += $d['jumlah'];
                            ?>
                                <tr>
                                    <td class="text-center text-muted"><?= $noIdx++; ?></td>
                                    <td><span class="badge bg-light text-dark border font-monospace"><?= htmlspecialchars($d['kode_barang']); ?></span></td>
                                    <td class="fw-semibold"><?= htmlspecialchars($d['nama_barang']); ?></td>
                                    <td><span class="badge bg-light-info text-primary border"><?= htmlspecialchars($d['nama_kategori']); ?></span></td>
                                    <td class="text-center text-muted"><?= htmlspecialchars($d['satuan']); ?></td>
                                    <td class="text-center fw-bold text-success">+<?= $d['jumlah']; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr class="table-light fw-bold">
                                <td colspan="5" class="text-end">Total Barang Masuk:</td>
                                <td class="text-center text-success fs-6">+<?= $totalQty; ?></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
