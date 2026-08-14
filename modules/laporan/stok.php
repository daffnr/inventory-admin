<?php
// modules/laporan/stok.php - Laporan Stok Barang (dengan Pencarian Nama & Kategori)
$pageTitle = 'Laporan Stok Barang';
$activeMenu = 'laporan_stok';

require_once __DIR__ . '/../../includes/header.php';

// Filter Parameter
$searchNama = isset($_GET['nama_barang']) ? sanitize($_GET['nama_barang']) : '';
$kategoriFilter = isset($_GET['kategori_id']) ? (int)$_GET['kategori_id'] : 0;

// Fetch Categories for Dropdown Filter
$kategoris = $pdo->query("SELECT * FROM kategori ORDER BY nama_kategori ASC")->fetchAll();

// Build Query
$sql = "SELECT b.kode_barang, b.nama_barang, b.satuan, b.stok, k.nama_kategori 
        FROM barang b 
        JOIN kategori k ON b.kategori_id = k.id 
        WHERE 1=1";
$params = [];

if (!empty($searchNama)) {
    $sql .= " AND b.nama_barang LIKE ?";
    $params[] = "%$searchNama%";
}

if ($kategoriFilter > 0) {
    $sql .= " AND b.kategori_id = ?";
    $params[] = $kategoriFilter;
}

$sql .= " ORDER BY b.kode_barang ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$stokList = $stmt->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
    <div>
        <h4 class="fw-bold m-0 text-slate-800">Laporan Stok Barang</h4>
        <p class="text-muted small m-0">Menampilkan rekapitulasi jumlah stok seluruh barang yang tersedia di sistem.</p>
    </div>
    <div>
        <button onclick="window.print()" class="btn btn-outline-primary btn-print-hide shadow-sm">
            <i class="bi bi-printer me-1"></i> Cetak Laporan
        </button>
    </div>
</div>

<div class="card card-table border-0 shadow-sm">
    <div class="card-header bg-white py-3 border-bottom btn-print-hide">
        <form action="stok.php" method="GET" class="row g-3 align-items-center">
            <div class="col-md-5 col-sm-12">
                <label for="nama_barang" class="form-label fs-7 fw-semibold text-muted mb-1">Cari Nama Barang</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-search"></i></span>
                    <input type="text" id="nama_barang" name="nama_barang" class="form-control border-start-0" 
                           placeholder="Ketik nama barang..." value="<?= htmlspecialchars($searchNama); ?>">
                </div>
            </div>
            <div class="col-md-4 col-sm-12">
                <label for="kategori_id" class="form-label fs-7 fw-semibold text-muted mb-1">Kategori Barang</label>
                <select id="kategori_id" name="kategori_id" class="form-select">
                    <option value="0">-- Semua Kategori --</option>
                    <?php foreach ($kategoris as $kat): ?>
                        <option value="<?= $kat['id']; ?>" <?= ($kategoriFilter === $kat['id']) ? 'selected' : ''; ?>>
                            <?= htmlspecialchars($kat['nama_kategori']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3 col-sm-12 d-flex align-items-end gap-2" style="height: 68px;">
                <button type="submit" class="btn btn-primary flex-grow-1">
                    <i class="bi bi-filter me-1"></i> Filter
                </button>
                <?php if (!empty($searchNama) || $kategoriFilter > 0): ?>
                    <a href="stok.php" class="btn btn-outline-secondary">Reset</a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th style="width: 120px;" class="text-center">Kode</th>
                    <th>Nama Barang</th>
                    <th>Kategori</th>
                    <th class="text-center" style="width: 120px;">Satuan</th>
                    <th class="text-center" style="width: 140px;">Stok</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($stokList)): ?>
                    <tr>
                        <td colspan="5" class="text-center text-muted py-5">
                            <i class="bi bi-inbox fs-1 d-block mb-2 text-secondary"></i>
                            Tidak ada data stok barang ditemukan.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php 
                    $totalStokKeseluruhan = 0;
                    foreach ($stokList as $s): 
                        $totalStokKeseluruhan += $s['stok'];
                    ?>
                        <tr>
                            <td class="text-center font-monospace fw-bold"><?= htmlspecialchars($s['kode_barang']); ?></td>
                            <td class="fw-semibold text-slate-800"><?= htmlspecialchars($s['nama_barang']); ?></td>
                            <td><span class="badge bg-light-info text-primary border"><?= htmlspecialchars($s['nama_kategori']); ?></span></td>
                            <td class="text-center text-muted"><?= htmlspecialchars($s['satuan']); ?></td>
                            <td class="text-center">
                                <?php if ($s['stok'] <= 0): ?>
                                    <span class="badge bg-danger px-3 py-1">Habis (0)</span>
                                <?php elseif ($s['stok'] <= 5): ?>
                                    <span class="badge bg-warning text-dark px-3 py-1"><?= $s['stok']; ?></span>
                                <?php else: ?>
                                    <span class="badge bg-success px-3 py-1"><?= $s['stok']; ?></span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
            <?php if (!empty($stokList)): ?>
                <tfoot>
                    <tr class="table-light fw-bold">
                        <td colspan="4" class="text-end">Total Accumulation Stok:</td>
                        <td class="text-center text-primary fs-6"><?= number_format($totalStokKeseluruhan, 0, ',', '.'); ?> Unit</td>
                    </tr>
                </tfoot>
            <?php endif; ?>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
