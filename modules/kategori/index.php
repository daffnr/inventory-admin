<?php
$pageTitle = 'Master Kategori';
$activeMenu = 'kategori';

require_once __DIR__ . '/../../includes/header.php';

$action = isset($_GET['action']) ? $_GET['action'] : '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $namaKategori = sanitize($_POST['nama_kategori'] ?? '');

    if (empty($namaKategori)) {
        set_flash_message('danger', 'Nama Kategori tidak boleh kosong!');
        header('Location: index.php');
        exit;
    }

    if (isset($_POST['id']) && !empty($_POST['id'])) {
        $id = (int) $_POST['id'];
        try {
            $stmt = $pdo->prepare("UPDATE kategori SET nama_kategori = ? WHERE id = ?");
            $stmt->execute([$namaKategori, $id]);
            set_flash_message('success', 'Kategori berhasil diperbarui!');
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                set_flash_message('danger', 'Nama kategori sudah ada!');
            } else {
                set_flash_message('danger', 'Gagal memperbarui kategori: ' . $e->getMessage());
            }
        }
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO kategori (nama_kategori) VALUES (?)");
            $stmt->execute([$namaKategori]);
            set_flash_message('success', 'Kategori baru berhasil ditambahkan!');
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                set_flash_message('danger', 'Nama kategori sudah ada!');
            } else {
                set_flash_message('danger', 'Gagal menambahkan kategori: ' . $e->getMessage());
            }
        }
    }
    header('Location: index.php');
    exit;
}

if ($action === 'delete' && isset($_GET['id'])) {
    $id = (int) $_GET['id'];
    try {
        $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM barang WHERE kategori_id = ?");
        $checkStmt->execute([$id]);
        if ($checkStmt->fetchColumn() > 0) {
            set_flash_message('danger', 'Kategori tidak dapat dihapus karena masih digunakan pada data barang!');
        } else {
            $stmt = $pdo->prepare("DELETE FROM kategori WHERE id = ?");
            $stmt->execute([$id]);
            set_flash_message('success', 'Kategori berhasil dihapus!');
        }
    } catch (PDOException $e) {
        set_flash_message('danger', 'Gagal menghapus kategori: ' . $e->getMessage());
    }
    header('Location: index.php');
    exit;
}

$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
if (!empty($search)) {
    $stmt = $pdo->prepare("SELECT k.*, COUNT(b.kode_barang) as total_barang 
                           FROM kategori k 
                           LEFT JOIN barang b ON k.id = b.kategori_id 
                           WHERE k.nama_kategori LIKE ? 
                           GROUP BY k.id 
                           ORDER BY k.id ASC");
    $stmt->execute(['%' . $search . '%']);
} else {
    $stmt = $pdo->query("SELECT k.*, COUNT(b.kode_barang) as total_barang 
                         FROM kategori k 
                         LEFT JOIN barang b ON k.id = b.kategori_id 
                         GROUP BY k.id 
                         ORDER BY k.id ASC");
}
$kategoris = $stmt->fetchAll();

$editData = null;
if ($action === 'edit' && isset($_GET['id'])) {
    $editId = (int) $_GET['id'];
    $stmtEdit = $pdo->prepare("SELECT * FROM kategori WHERE id = ?");
    $stmtEdit->execute([$editId]);
    $editData = $stmtEdit->fetch();
}
?>

<div class="row g-4">
    <div class="col-md-4">
        <div class="card card-table border-0 shadow-sm">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center">
                <i class="bi bi-pencil-square me-2 text-primary"></i>
                <h6 class="m-0 fw-bold"><?= $editData ? 'Edit Kategori' : 'Tambah Kategori Baru'; ?></h6>
            </div>
            <div class="card-body p-4">
                <form action="index.php" method="POST">
                    <?php if ($editData): ?>
                        <input type="hidden" name="id" value="<?= $editData['id']; ?>">
                    <?php endif; ?>

                    <div class="mb-3">
                        <label for="nama_kategori" class="form-label font-weight-bold">Nama Kategori <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="nama_kategori" name="nama_kategori" 
                               value="<?= htmlspecialchars($editData['nama_kategori'] ?? ''); ?>" 
                               placeholder="Contoh: Elektronik, Aksesoris..." required autofocus>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary flex-grow-1">
                            <i class="bi bi-save me-1"></i> Simpan
                        </button>
                        <?php if ($editData): ?>
                            <a href="index.php" class="btn btn-outline-secondary">
                                Batal
                            </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card card-table border-0 shadow-sm">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h6 class="m-0 fw-bold"><i class="bi bi-tags-fill me-2 text-primary"></i>Daftar Master Kategori</h6>
                
                <form action="index.php" method="GET" class="d-flex gap-2">
                    <input type="text" name="search" class="form-control form-control-sm" placeholder="Cari kategori..." value="<?= htmlspecialchars($search); ?>">
                    <button type="submit" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-search"></i>
                    </button>
                    <?php if (!empty($search)): ?>
                        <a href="index.php" class="btn btn-sm btn-outline-secondary">Reset</a>
                    <?php endif; ?>
                </form>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th style="width: 70px;">ID</th>
                            <th>Nama Kategori</th>
                            <th class="text-center" style="width: 130px;">Jumlah Barang</th>
                            <th class="text-end" style="width: 140px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($kategoris)): ?>
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">
                                    <i class="bi bi-inbox fs-2 d-block mb-2 text-secondary"></i>
                                    Tidak ada data kategori ditemukan.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($kategoris as $kat): ?>
                                <tr>
                                    <td class="fw-bold text-secondary"><?= $kat['id']; ?></td>
                                    <td class="fw-semibold"><?= htmlspecialchars($kat['nama_kategori']); ?></td>
                                    <td class="text-center">
                                        <span class="badge bg-light-info text-primary px-3 py-2 rounded-pill">
                                            <?= $kat['total_barang']; ?> Barang
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <a href="index.php?action=edit&id=<?= $kat['id']; ?>" class="btn btn-sm btn-outline-warning me-1" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <a href="index.php?action=delete&id=<?= $kat['id']; ?>" 
                                           class="btn btn-sm btn-outline-danger" 
                                           onclick="return confirm('Apakah Anda yakin ingin menghapus kategori ini?')" title="Hapus">
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
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
