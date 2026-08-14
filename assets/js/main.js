let barangList = [];

async function loadBarangData() {
  try {
    const res = await fetch('/api/get_barang.php');
    const json = await res.json();
    if (json.status === 'success') {
      barangList = json.data;
    }
  } catch (err) {
    console.error('Gagal memuat data barang:', err);
  }
}

function buildBarangOptions(selectedKode = '') {
  let html = '<option value="">-- Pilih Barang --</option>';
  barangList.forEach(b => {
    const isSelected = b.kode_barang === selectedKode ? 'selected' : '';
    html += `<option value="${b.kode_barang}" data-stok="${b.stok}" data-satuan="${b.satuan}" ${isSelected}>
              [${b.kode_barang}] ${b.nama_barang} (Stok: ${b.stok} ${b.satuan})
            </option>`;
  });
  return html;
}

function addTransactionRow(type = 'masuk') {
  const tableBody = document.getElementById('transaction-items-body');
  if (!tableBody) return;

  const rowIndex = tableBody.children.length;
  const tr = document.createElement('tr');
  tr.className = 'item-row';

  const barangSelectHtml = buildBarangOptions();

  tr.innerHTML = `
    <td>
      <select name="items[${rowIndex}][kode_barang]" class="form-select select-barang" required onchange="handleBarangChange(this, '${type}')">
        ${barangSelectHtml}
      </select>
    </td>
    <td class="text-center align-middle">
      <span class="badge bg-secondary info-stok">-</span>
    </td>
    <td class="text-center align-middle">
      <span class="info-satuan text-muted">-</span>
    </td>
    <td>
      <input type="number" name="items[${rowIndex}][jumlah]" class="form-control input-qty text-center" min="1" value="1" required oninput="validateRowQty(this, '${type}')">
    </td>
    <td class="text-center">
      <button type="button" class="btn btn-sm btn-outline-danger btn-remove-row" onclick="removeTransactionRow(this)">
        <i class="bi bi-trash"></i>
      </button>
    </td>
  `;

  tableBody.appendChild(tr);
}

function removeTransactionRow(btn) {
  const tableBody = document.getElementById('transaction-items-body');
  if (tableBody.children.length > 1) {
    btn.closest('tr').remove();
  } else {
    alert('Minimal 1 barang harus dimasukkan dalam transaksi!');
  }
}

function handleBarangChange(selectElem, type) {
  const tr = selectElem.closest('tr');
  const selectedOption = selectElem.options[selectElem.selectedIndex];
  const stokBadge = tr.querySelector('.info-stok');
  const satuanSpan = tr.querySelector('.info-satuan');
  const qtyInput = tr.querySelector('.input-qty');

  if (selectElem.value) {
    const stok = parseInt(selectedOption.getAttribute('data-stok') || '0');
    const satuan = selectedOption.getAttribute('data-satuan') || '-';

    satuanSpan.textContent = satuan;

    if (type === 'keluar') {
      if (stok <= 0) {
        stokBadge.className = 'badge bg-danger info-stok';
        stokBadge.textContent = 'Stok Habis (0)';
      } else if (stok <= 5) {
        stokBadge.className = 'badge bg-warning text-dark info-stok';
        stokBadge.textContent = `Tersedia: ${stok}`;
      } else {
        stokBadge.className = 'badge bg-success info-stok';
        stokBadge.textContent = `Tersedia: ${stok}`;
      }
      qtyInput.max = stok;
    } else {
      stokBadge.className = 'badge bg-info info-stok';
      stokBadge.textContent = `Stok Saat Ini: ${stok}`;
    }
  } else {
    stokBadge.className = 'badge bg-secondary info-stok';
    stokBadge.textContent = '-';
    satuanSpan.textContent = '-';
  }

  validateRowQty(qtyInput, type);
}

function validateRowQty(inputElem, type) {
  if (type !== 'keluar') return;

  const tr = inputElem.closest('tr');
  const selectElem = tr.querySelector('.select-barang');
  const selectedOption = selectElem.options[selectElem.selectedIndex];

  if (selectElem.value) {
    const stok = parseInt(selectedOption.getAttribute('data-stok') || '0');
    const qty = parseInt(inputElem.value || '0');

    if (qty > stok) {
      inputElem.classList.add('is-invalid');
    } else {
      inputElem.classList.remove('is-invalid');
    }
  }
}

document.addEventListener('DOMContentLoaded', async () => {
  await loadBarangData();

  const tableBody = document.getElementById('transaction-items-body');
  if (tableBody) {
    const type = tableBody.getAttribute('data-type') || 'masuk';
    if (tableBody.children.length === 0) {
      addTransactionRow(type);
    }
  }

  const sidebarToggle = document.getElementById('sidebarToggle');
  const wrapper = document.getElementById('wrapper');
  const backdrop = document.getElementById('sidebar-backdrop');

  if (sidebarToggle && wrapper) {
    sidebarToggle.addEventListener('click', (e) => {
      e.stopPropagation();
      wrapper.classList.toggle('toggled');
    });
  }

  if (backdrop && wrapper) {
    backdrop.addEventListener('click', () => {
      wrapper.classList.remove('toggled');
    });
  }
});
