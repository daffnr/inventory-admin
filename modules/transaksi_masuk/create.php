<?php
// modules/transaksi_masuk/create.php - Form Input Transaksi Barang Masuk (Multi Item)
$pageTitle = 'Input Transaksi Barang Masuk';
$activeMenu = 'transaksi_masuk';

require_once __DIR__ . '/../../includes/header.php';

$autoNoTransaksi = generate_no_transaksi('BM');
$today = date('Y-m-d');
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $noTransaksi = sanitize($_POST['no_transaksi'] ?? '');
    $tanggal = sanitize($_POST['tanggal'] ?? '');
    $supplier = sanitize($_POST['supplier'] ?? '');
    $items = $_POST['items'] ?? [];

    // Validasi Header
    if (empty($noTransaksi)) $errors[] = 'Nomor Transaksi wajib diisi.';
    if (empty($tanggal)) $errors[] = 'Tanggal Transaksi wajib diisi.';
    if (empty($supplier)) $errors[] = 'Nama Supplier wajib diisi.';
    if (empty($items) || !is_array($items)) {
        $errors[] = 'Minimal 1 barang harus dimasukkan dalam transaksi.';
    }

    // Process Validations & Aggregate Qty
    $processedItems = [];
    if (empty($errors)) {
        foreach ($items as $idx => $item) {
            $kodeBarang = sanitize($item['kode_barang'] ?? '');
            $jumlah = (int)($item['jumlah'] ?? 0);

            if (empty($kodeBarang)) {
                $errors[] = "Baris ke-" . ($idx + 1) . ": Barang belum dipilih.";
                continue;
            }
            if ($jumlah <= 0) {
                $errors[] = "Baris ke-" . ($idx + 1) . ": Jumlah barang masuk harus lebih dari 0.";
                continue;
            }

            if (isset($processedItems[$kodeBarang])) {
                $processedItems[$kodeBarang] += $jumlah;
            } else {
                $processedItems[$kodeBarang] = $jumlah;
            }
        }
    }

    // Execute Database Transaction
    if (empty($errors)) {
        try {
            $pdo->beginTransaction();

            // 1. Save Header
            $stmtHeader = $pdo->prepare("INSERT INTO transaksi_masuk (no_transaksi, tanggal, supplier) VALUES (?, ?, ?)");
            $stmtHeader->execute([$noTransaksi, $tanggal, $supplier]);

            // Prepared Statements for Detail & Stock Update
            $stmtDetail = $pdo->prepare("INSERT INTO transaksi_masuk_detail (no_transaksi, kode_barang, jumlah) VALUES (?, ?, ?)");
            $stmtUpdateStok = $pdo->prepare("UPDATE barang SET stok = stok + ? WHERE kode_barang = ?");

            // 2. Save Details & Update Stock
            foreach ($processedItems as $kodeBarang => $jumlah) {
                $stmtDetail->execute([$noTransaksi, $kodeBarang, $jumlah]);
                $stmtUpdateStok->execute([$jumlah, $kodeBarang]);
            }

            $pdo->commit();

            set_flash_message('success', "🎉 Transaksi Barang Masuk <strong>$noTransaksi</strong> berhasil disimpan! Stok barang otomatis bertambah.");
            header('Location: index.php');
            exit;
        } catch (PDOException $e) {
            $pdo->rollBack();
            if ($e->getCode() == 23000) {
                $errors[] = "Nomor Transaksi '$noTransaksi' sudah ada. Silakan gunakan nomor transaksi lain.";
            } else {
                $errors[] = 'Gagal menyimpan transaksi: ' . $e->getMessage();
            }
        }
    }
}
?>

<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="card card-table border-0 shadow-sm">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center">
                <a href="index.php" class="btn btn-sm btn-outline-secondary me-3"><i class="bi bi-arrow-left"></i></a>
                <h5 class="m-0 fw-bold"><i class="bi bi-box-arrow-in-down-right me-2 text-success"></i>Form Transaksi Barang Masuk</h5>
            </div>

            <div class="card-body p-4">
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger shadow-sm mb-4" role="alert">
                        <div class="fw-bold mb-1"><i class="bi bi-exclamation-triangle-fill me-1"></i> Transaksi Gagal Diproses:</div>
                        <ul class="mb-0 ps-3">
                            <?php foreach ($errors as $err): ?>
                                <li><?= htmlspecialchars($err); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form action="create.php" method="POST" id="form-transaksi-masuk">
                    <!-- Header Info -->
                    <div class="row g-3 mb-4 p-3 bg-light rounded-3 border">
                        <div class="col-md-4">
                            <label for="no_transaksi" class="form-label fw-semibold">No Transaksi <span class="text-danger">*</span></label>
                            <input type="text" class="form-control font-monospace fw-bold" id="no_transaksi" name="no_transaksi" 
                                   value="<?= htmlspecialchars($_POST['no_transaksi'] ?? $autoNoTransaksi); ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label for="tanggal" class="form-label fw-semibold">Tanggal <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="tanggal" name="tanggal" 
                                   value="<?= htmlspecialchars($_POST['tanggal'] ?? $today); ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label for="supplier" class="form-label fw-semibold">Supplier / Vendor <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="supplier" name="supplier" 
                                   value="<?= htmlspecialchars($_POST['supplier'] ?? ''); ?>" placeholder="Contoh: PT ABC, CV Jaya..." required>
                        </div>
                    </div>

                    <!-- Multi Item Table -->
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="fw-bold m-0 text-slate-800"><i class="bi bi-list-check me-2"></i>Daftar Barang Masuk</h6>
                        <button type="button" class="btn btn-sm btn-outline-success" onclick="addTransactionRow('masuk')">
                            <i class="bi bi-plus-circle me-1"></i> Tambah Baris Barang
                        </button>
                    </div>

                    <div class="table-responsive mb-4">
                        <table class="table table-bordered align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Pilih Barang <span class="text-danger">*</span></th>
                                    <th style="width: 160px;" class="text-center">Info Stok Current</th>
                                    <th style="width: 100px;" class="text-center">Satuan</th>
                                    <th style="width: 140px;" class="text-center">Qty Masuk <span class="text-danger">*</span></th>
                                    <th style="width: 60px;" class="text-center">Hapus</th>
                                </tr>
                            </thead>
                            <tbody id="transaction-items-body" data-type="masuk">
                                <!-- Dynamic Rows created by main.js -->
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-end gap-2 border-top pt-3">
                        <a href="index.php" class="btn btn-outline-secondary">Batal</a>
                        <button type="submit" class="btn btn-success px-4 fw-bold">
                            <i class="bi bi-check-circle me-1"></i> Simpan Transaksi Masuk
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
