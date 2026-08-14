<?php
// modules/transaksi_keluar/create.php - Form Input Transaksi Barang Keluar dengan Validasi Stok Ketat
$pageTitle = 'Input Transaksi Barang Keluar';
$activeMenu = 'transaksi_keluar';

require_once __DIR__ . '/../../includes/header.php';

$autoNoTransaksi = generate_no_transaksi('BK');
$today = date('Y-m-d');
$errors = [];
$rejectedItems = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $noTransaksi = sanitize($_POST['no_transaksi'] ?? '');
    $tanggal = sanitize($_POST['tanggal'] ?? '');
    $tujuan = sanitize($_POST['tujuan'] ?? '');
    $items = $_POST['items'] ?? [];

    // Validasi Header
    if (empty($noTransaksi)) $errors[] = 'Nomor Transaksi wajib diisi.';
    if (empty($tanggal)) $errors[] = 'Tanggal Transaksi wajib diisi.';
    if (empty($tujuan)) $errors[] = 'Tujuan / Penerima barang wajib diisi.';
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
                $errors[] = "Baris ke-" . ($idx + 1) . ": Jumlah barang keluar harus lebih dari 0.";
                continue;
            }

            if (isset($processedItems[$kodeBarang])) {
                $processedItems[$kodeBarang] += $jumlah;
            } else {
                $processedItems[$kodeBarang] = $jumlah;
            }
        }
    }

    // STAGE 2: VALIDASI STOK KETAT (PERIKSA STOK TERSEDIA PADA DATABASE)
    if (empty($errors) && !empty($processedItems)) {
        try {
            $pdo->beginTransaction();

            $placeholders = implode(',', array_fill(0, count($processedItems), '?'));
            $stmtStok = $pdo->prepare("SELECT kode_barang, nama_barang, stok, satuan FROM barang WHERE kode_barang IN ($placeholders) FOR UPDATE");
            $stmtStok->execute(array_keys($processedItems));
            $stockResults = $stmtStok->fetchAll();

            $stockMap = [];
            foreach ($stockResults as $row) {
                $stockMap[$row['kode_barang']] = $row;
            }

            // Periksa ketersediaan stok
            foreach ($processedItems as $kodeBarang => $qtyRequested) {
                if (!isset($stockMap[$kodeBarang])) {
                    $errors[] = "Barang dengan kode '$kodeBarang' tidak ditemukan di database.";
                    continue;
                }

                $barangData = $stockMap[$kodeBarang];
                $stokTersedia = (int)$barangData['stok'];
                $namaBarang = $barangData['nama_barang'];
                $satuan = $barangData['satuan'];

                if ($qtyRequested > $stokTersedia) {
                    $rejectedItems[] = [
                        'kode' => $kodeBarang,
                        'nama' => $namaBarang,
                        'diminta' => $qtyRequested,
                        'tersedia' => $stokTersedia,
                        'satuan' => $satuan
                    ];
                }
            }

            // JIKA ADA BARANG YANG QTY-NYA MELEBIHI STOK TERSEDIA -> TRANSAKSI DITOLAK!
            if (!empty($rejectedItems)) {
                $pdo->rollBack();
                // Flag penolakan transaksi
                $errors[] = "❌ Transaksi Ditolak! Terdapat barang dengan jumlah keluar melebihi stok tersedia.";
            } else {
                // STAGE 3: EXECUTE OUTGOING TRANSACTION & DEDUCT STOCK
                $stmtHeader = $pdo->prepare("INSERT INTO transaksi_keluar (no_transaksi, tanggal, tujuan) VALUES (?, ?, ?)");
                $stmtHeader->execute([$noTransaksi, $tanggal, $tujuan]);

                $stmtDetail = $pdo->prepare("INSERT INTO transaksi_keluar_detail (no_transaksi, kode_barang, jumlah) VALUES (?, ?, ?)");
                $stmtDeductStok = $pdo->prepare("UPDATE barang SET stok = stok - ? WHERE kode_barang = ?");

                foreach ($processedItems as $kodeBarang => $jumlah) {
                    $stmtDetail->execute([$noTransaksi, $kodeBarang, $jumlah]);
                    $stmtDeductStok->execute([$jumlah, $kodeBarang]);
                }

                $pdo->commit();

                set_flash_message('success', "🎉 Transaksi Barang Keluar <strong>$noTransaksi</strong> berhasil disimpan! Stok barang otomatis berkurang.");
                header('Location: index.php');
                exit;
            }

        } catch (PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            if ($e->getCode() == 23000) {
                $errors[] = "Nomor Transaksi '$noTransaksi' sudah ada. Silakan gunakan nomor transaksi lain.";
            } else {
                $errors[] = 'Gagal menyimpan transaksi keluar: ' . $e->getMessage();
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
                <h5 class="m-0 fw-bold"><i class="bi bi-box-arrow-up-right me-2 text-danger"></i>Form Transaksi Barang Keluar</h5>
            </div>

            <div class="card-body p-4">
                <!-- Rejection Notice Banner -->
                <?php if (!empty($rejectedItems)): ?>
                    <div class="alert alert-danger shadow-sm mb-4 border-2 border-danger" role="alert">
                        <div class="d-flex align-items-center mb-2">
                            <i class="bi bi-x-circle-fill fs-3 text-danger me-2"></i>
                            <h5 class="m-0 fw-bold text-danger">❌ Transaksi Ditolak</h5>
                        </div>
                        <p class="mb-2 fs-7 text-dark">Sistem membatalkan transaksi ini karena jumlah barang yang diminat melebihi sisa stok gudang:</p>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered bg-white mb-0 align-middle">
                                <thead class="table-danger">
                                    <tr>
                                        <th>Kode</th>
                                        <th>Nama Barang</th>
                                        <th class="text-center">Stok Tersedia</th>
                                        <th class="text-center">Diminta</th>
                                        <th class="text-center">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($rejectedItems as $rej): ?>
                                        <tr>
                                            <td class="font-monospace fw-bold"><?= $rej['kode']; ?></td>
                                            <td class="fw-semibold"><?= htmlspecialchars($rej['nama']); ?></td>
                                            <td class="text-center"><span class="badge bg-warning text-dark"><?= $rej['tersedia']; ?> <?= $rej['satuan']; ?></span></td>
                                            <td class="text-center"><span class="badge bg-danger"><?= $rej['diminta']; ?> <?= $rej['satuan']; ?></span></td>
                                            <td class="text-center text-danger fw-bold"><i class="bi bi-x-circle me-1"></i> Tidak Cukup</td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if (!empty($errors) && empty($rejectedItems)): ?>
                    <div class="alert alert-danger shadow-sm mb-4" role="alert">
                        <div class="fw-bold mb-1"><i class="bi bi-exclamation-triangle-fill me-1"></i> Transaksi Gagal Diproses:</div>
                        <ul class="mb-0 ps-3">
                            <?php foreach ($errors as $err): ?>
                                <li><?= htmlspecialchars($err); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form action="create.php" method="POST" id="form-transaksi-keluar">
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
                            <label for="tujuan" class="form-label fw-semibold">Tujuan / Customer <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="tujuan" name="tujuan" 
                                   value="<?= htmlspecialchars($_POST['tujuan'] ?? ''); ?>" placeholder="Contoh: Divisi IT, Toko Maju..." required>
                        </div>
                    </div>

                    <!-- Multi Item Table -->
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="fw-bold m-0 text-slate-800"><i class="bi bi-list-check me-2"></i>Daftar Barang Keluar</h6>
                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="addTransactionRow('keluar')">
                            <i class="bi bi-plus-circle me-1"></i> Tambah Baris Barang
                        </button>
                    </div>

                    <div class="table-responsive mb-4">
                        <table class="table table-bordered align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Pilih Barang <span class="text-danger">*</span></th>
                                    <th style="width: 170px;" class="text-center">Stok Tersedia</th>
                                    <th style="width: 100px;" class="text-center">Satuan</th>
                                    <th style="width: 140px;" class="text-center">Qty Keluar <span class="text-danger">*</span></th>
                                    <th style="width: 60px;" class="text-center">Hapus</th>
                                </tr>
                            </thead>
                            <tbody id="transaction-items-body" data-type="keluar">
                                <!-- Dynamic Rows created by main.js -->
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-end gap-2 border-top pt-3">
                        <a href="index.php" class="btn btn-outline-secondary">Batal</a>
                        <button type="submit" class="btn btn-danger px-4 fw-bold">
                            <i class="bi bi-box-arrow-up-right me-1"></i> Simpan Transaksi Keluar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
