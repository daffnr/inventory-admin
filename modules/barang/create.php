<?php
// modules/barang/create.php - Form Tambah Barang Baru
$pageTitle = 'Tambah Barang Baru';
$activeMenu = 'barang';

require_once __DIR__ . '/../../includes/header.php';

// Fetch Categories
$kategoris = $pdo->query("SELECT * FROM kategori ORDER BY nama_kategori ASC")->fetchAll();

$autoKode = generate_kode_barang();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $kodeBarang = sanitize($_POST['kode_barang'] ?? '');
    $namaBarang = sanitize($_POST['nama_barang'] ?? '');
    $kategoriId = (int)($_POST['kategori_id'] ?? 0);
    $satuan = sanitize($_POST['satuan'] ?? '');
    $harga = (float)($_POST['harga'] ?? 0);
    $stok = (int)($_POST['stok'] ?? 0);

    // Validasi Input
    if (empty($kodeBarang)) $errors[] = 'Kode Barang wajib diisi.';
    if (empty($namaBarang)) $errors[] = 'Nama Barang wajib diisi.';
    if ($kategoriId <= 0) $errors[] = 'Pilih Kategori Barang.';
    if (empty($satuan)) $errors[] = 'Satuan wajib diisi.';
    if ($harga < 0) $errors[] = 'Harga tidak boleh negatif.';
    if ($stok < 0) $errors[] = 'Stok awal tidak boleh negatif.';

    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO barang (kode_barang, nama_barang, kategori_id, satuan, harga, stok) 
                                   VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$kodeBarang, $namaBarang, $kategoriId, $satuan, $harga, $stok]);
            
            set_flash_message('success', "Barang '$namaBarang' ($kodeBarang) berhasil ditambahkan!");
            header('Location: index.php');
            exit;
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $errors[] = "Kode Barang '$kodeBarang' sudah digunakan. Gunakan kode lain.";
            } else {
                $errors[] = 'Gagal menyimpan barang: ' . $e->getMessage();
            }
        }
    }
}
?>

<div class="row justify-content-center">
    <div class="col-md-8 col-lg-7">
        <div class="card card-table border-0 shadow-sm">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center">
                <a href="index.php" class="btn btn-sm btn-outline-secondary me-3"><i class="bi bi-arrow-left"></i></a>
                <h5 class="m-0 fw-bold"><i class="bi bi-plus-circle me-2 text-primary"></i>Tambah Barang Baru</h5>
            </div>
            
            <div class="card-body p-4">
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger shadow-sm mb-4" role="alert">
                        <ul class="mb-0">
                            <?php foreach ($errors as $err): ?>
                                <li><?= htmlspecialchars($err); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form action="create.php" method="POST">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="kode_barang" class="form-label fw-semibold">Kode Barang <span class="text-danger">*</span></label>
                            <input type="text" class="form-control font-monospace" id="kode_barang" name="kode_barang" 
                                   value="<?= htmlspecialchars($_POST['kode_barang'] ?? $autoKode); ?>" required>
                            <div class="form-text fs-7">Format otomatis: BRG-XXX</div>
                        </div>

                        <div class="col-md-6">
                            <label for="kategori_id" class="form-label fw-semibold">Kategori <span class="text-danger">*</span></label>
                            <select class="form-select" id="kategori_id" name="kategori_id" required>
                                <option value="">-- Pilih Kategori --</option>
                                <?php foreach ($kategoris as $kat): ?>
                                    <option value="<?= $kat['id']; ?>" <?= (isset($_POST['kategori_id']) && $_POST['kategori_id'] == $kat['id']) ? 'selected' : ''; ?>>
                                        <?= htmlspecialchars($kat['nama_kategori']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-12">
                            <label for="nama_barang" class="form-label fw-semibold">Nama Barang <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="nama_barang" name="nama_barang" 
                                   value="<?= htmlspecialchars($_POST['nama_barang'] ?? ''); ?>" placeholder="Contoh: Laptop Asus Vivobook" required>
                        </div>

                        <div class="col-md-4">
                            <label for="satuan" class="form-label fw-semibold">Satuan <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="satuan" name="satuan" 
                                   value="<?= htmlspecialchars($_POST['satuan'] ?? 'Pcs'); ?>" placeholder="Unit, Pcs, Box, Rim..." required>
                        </div>

                        <div class="col-md-4">
                            <label for="harga" class="form-label fw-semibold">Harga (Rp)</label>
                            <input type="number" step="500" class="form-control" id="harga" name="harga" 
                                   value="<?= htmlspecialchars($_POST['harga'] ?? '0'); ?>" min="0" required>
                        </div>

                        <div class="col-md-4">
                            <label for="stok" class="form-label fw-semibold">Stok Awal</label>
                            <input type="number" class="form-control" id="stok" name="stok" 
                                   value="<?= htmlspecialchars($_POST['stok'] ?? '0'); ?>" min="0" required>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                        <a href="index.php" class="btn btn-outline-secondary">Batal</a>
                        <button type="submit" class="btn btn-primary px-4"><i class="bi bi-save me-1"></i> Simpan Barang</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
