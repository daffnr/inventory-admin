<?php
// modules/barang/index.php - Master Barang (List & Search)
$pageTitle = 'Master Barang';
$activeMenu = 'barang';

require_once __DIR__ . '/../../includes/header.php';

// Handle Delete Operation
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['kode'])) {
    $kode = sanitize($_GET['kode']);
    try {
        // Check if item is used in transactions
        $checkMasuk = $pdo->prepare("SELECT COUNT(*) FROM transaksi_masuk_detail WHERE kode_barang = ?");
        $checkMasuk->execute([$kode]);
        $checkKeluar = $pdo->prepare("SELECT COUNT(*) FROM transaksi_keluar_detail WHERE kode_barang = ?");
        $checkKeluar->execute([$kode]);

        if ($checkMasuk->fetchColumn() > 0 || $checkKeluar->fetchColumn() > 0) {
            set_flash_message('danger', 'Barang tidak dapat dihapus karena sudah memiliki riwayat transaksi!');
        } else {
            $stmt = $pdo->prepare("DELETE FROM barang WHERE kode_barang = ?");
            $stmt->execute([$kode]);
            set_flash_message('success', "Barang '$kode' berhasil dihapus!");
        }
    } catch (PDOException $e) {
        set_flash_message('danger', 'Gagal menghapus barang: ' . $e->getMessage());
    }
    header('Location: index.php');
    exit;
}

// Search & Filter Parameter
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$kategoriFilter = isset($_GET['kategori_id']) ? (int)$_GET['kategori_id'] : 0;

// Fetch Categories for Dropdown Filter
$kategoris = $pdo->query("SELECT * FROM kategori ORDER BY nama_kategori ASC")->fetchAll();

// Build SQL Query
$sql = "SELECT b.*, k.nama_kategori 
        FROM barang b 
        JOIN kategori k ON b.kategori_id = k.id 
        WHERE 1=1";
$params = [];

if (!empty($search)) {
    $sql .= " AND (b.kode_barang LIKE ? OR b.nama_barang LIKE ? OR k.nama_kategori LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($kategoriFilter > 0) {
    $sql .= " AND b.kategori_id = ?";
    $params[] = $kategoriFilter;
}

$sql .= " ORDER BY b.kode_barang ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$barangs = $stmt->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
    <div>
        <h4 class="fw-bold m-0 text-slate-800">Master Data Barang</h4>
        <p class="text-muted small m-0">Kelola informasi katalog barang, kategori, harga, dan stok yang tersedia.</p>
    </div>
    <div>
        <a href="create.php" class="btn btn-primary shadow-sm">
            <i class="bi bi-plus-lg me-1"></i> Tambah Barang Baru
        </a>
    </div>
</div>

<div class="card card-table border-0 shadow-sm">
    <div class="card-header bg-white py-3 border-bottom">
        <form action="index.php" method="GET" class="row g-3 align-items-center">
            <div class="col-md-5 col-sm-12">
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-secondary"></i></span>
                    <input type="text" name="search" class="form-control border-start-0" 
                           placeholder="Cari berdasarkan Kode, Nama Barang, atau Kategori..." value="<?= htmlspecialchars($search); ?>">
                </div>
            </div>
            <div class="col-md-4 col-sm-12">
                <select name="kategori_id" class="form-select">
                    <option value="0">-- Semua Kategori --</option>
                    <?php foreach ($kategoris as $kat): ?>
                        <option value="<?= $kat['id']; ?>" <?= ($kategoriFilter === $kat['id']) ? 'selected' : ''; ?>>
                            <?= htmlspecialchars($kat['nama_kategori']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3 col-sm-12 d-flex gap-2">
                <button type="submit" class="btn btn-outline-primary flex-grow-1">Filter</button>
                <?php if (!empty($search) || $kategoriFilter > 0): ?>
                    <a href="index.php" class="btn btn-outline-secondary">Reset</a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th style="width: 120px;">Kode</th>
                    <th>Nama Barang</th>
                    <th>Kategori</th>
                    <th>Satuan</th>
                    <th class="text-end">Harga</th>
                    <th class="text-center" style="width: 120px;">Stok</th>
                    <th class="text-end" style="width: 130px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($barangs)): ?>
                    <tr>
                        <td colspan="7" class="text-center text-muted py-5">
                            <i class="bi bi-box-seam fs-1 d-block mb-2 text-secondary"></i>
                            Tidak ada barang ditemukan.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($barangs as $b): ?>
                        <tr>
                            <td>
                                <span class="badge bg-light text-dark border font-monospace px-2 py-1 fs-7">
                                    <?= htmlspecialchars($b['kode_barang']); ?>
                                </span>
                            </td>
                            <td class="fw-semibold text-slate-800">
                                <?= htmlspecialchars($b['nama_barang']); ?>
                            </td>
                            <td>
                                <span class="badge bg-light-info text-primary border border-primary-subtle">
                                    <?= htmlspecialchars($b['nama_kategori']); ?>
                                </span>
                            </td>
                            <td class="text-muted"><?= htmlspecialchars($b['satuan']); ?></td>
                            <td class="text-end fw-medium text-slate-700">
                                <?= format_rupiah($b['harga']); ?>
                            </td>
                            <td class="text-center">
                                <?php if ($b['stok'] <= 0): ?>
                                    <span class="badge bg-danger badge-stok">Habis (0)</span>
                                <?php elseif ($b['stok'] <= 5): ?>
                                    <span class="badge bg-warning text-dark badge-stok"><?= $b['stok']; ?> <?= htmlspecialchars($b['satuan']); ?></span>
                                <?php else: ?>
                                    <span class="badge bg-success badge-stok"><?= $b['stok']; ?> <?= htmlspecialchars($b['satuan']); ?></span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <a href="edit.php?kode=<?= urlencode($b['kode_barang']); ?>" class="btn btn-sm btn-outline-warning me-1" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <a href="index.php?action=delete&kode=<?= urlencode($b['kode_barang']); ?>" 
                                   class="btn btn-sm btn-outline-danger" 
                                   onclick="return confirm('Apakah Anda yakin ingin menghapus barang <?= htmlspecialchars($b['nama_barang']); ?>?')" title="Hapus">
                                    <i class="bi bi-trash"></i>
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
