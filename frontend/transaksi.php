<?php
require_once 'api_config.php';
requireLogin();

$transaksis = callAPI('GET', '/transaksi')['response'] ?? [];

function formatRupiah($value) {
    return 'Rp ' . number_format((float)$value, 0, ',', '.');
}

function formatTanggal($value) {
    if (!$value) return '-';
    return date('d/m/Y H:i', strtotime($value));
}

function labelStatus($status) {
    return $status === 'lunas' ? 'Lunas' : 'Belum Lunas';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Transaksi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">Penjualan App</a>
            <div class="collapse navbar-collapse">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="kategori.php">Kategori</a></li>
                    <li class="nav-item"><a class="nav-link" href="produk.php">Produk</a></li>
                    <li class="nav-item"><a class="nav-link" href="pelanggan.php">Pelanggan</a></li>
                    <li class="nav-item"><a class="nav-link active" href="transaksi.php">Transaksi</a></li>
                    <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2 class="mb-0">Daftar Transaksi</h2>
            <a href="transaksi_create.php" class="btn btn-primary" id="btnBuatTransaksi">Buat Transaksi Baru</a>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-striped align-middle" id="tabelTransaksi">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Pelanggan</th>
                        <th>Tanggal</th>
                        <th>Total</th>
                        <th>Metode</th>
                        <th>Status</th>
                        <th class="text-center">Detail</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($transaksis)): ?>
                    <tr>
                        <td colspan="7" class="text-center text-muted">Belum ada transaksi</td>
                    </tr>
                    <?php endif; ?>
                    <?php foreach ($transaksis as $transaksi): ?>
                    <?php
                        $pelanggan = $transaksi['pelanggan']['nama'] ?? 'Pelanggan #' . $transaksi['pelanggan_id'];
                        $detailJson = htmlspecialchars(json_encode($transaksi), ENT_QUOTES, 'UTF-8');
                    ?>
                    <tr>
                        <td><?= $transaksi['id'] ?></td>
                        <td><?= htmlspecialchars($pelanggan) ?></td>
                        <td><?= formatTanggal($transaksi['tanggal'] ?? '') ?></td>
                        <td><?= formatRupiah($transaksi['total'] ?? 0) ?></td>
                        <td><?= strtoupper(htmlspecialchars($transaksi['metode_pembayaran'] ?? '-')) ?></td>
                        <td>
                            <span class="badge <?= ($transaksi['status_pembayaran'] ?? '') === 'lunas' ? 'text-bg-success' : 'text-bg-warning' ?>">
                                <?= labelStatus($transaksi['status_pembayaran'] ?? '') ?>
                            </span>
                        </td>
                        <td class="text-center">
                            <button class="btn btn-sm btn-info text-white" data-bs-toggle="modal" data-bs-target="#modalDetail" onclick='showDetail(<?= $detailJson ?>)'>Detail</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="modal fade" id="modalDetail" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalDetailTitle">Detail Transaksi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <div class="text-muted small">Pelanggan</div>
                            <div id="detailPelanggan" class="fw-semibold">-</div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-muted small">Metode</div>
                            <div id="detailMetode" class="fw-semibold">-</div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-muted small">Status</div>
                            <div id="detailStatus" class="fw-semibold">-</div>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <thead>
                                <tr>
                                    <th>Produk</th>
                                    <th class="text-end">Harga</th>
                                    <th class="text-end">Qty</th>
                                    <th class="text-end">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody id="detailItems"></tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="3" class="text-end">Total</th>
                                    <th class="text-end" id="detailTotal">Rp 0</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function formatRupiah(value) {
            return 'Rp ' + Number(value || 0).toLocaleString('id-ID');
        }

        function showDetail(transaksi) {
            const pelanggan = transaksi.pelanggan ? transaksi.pelanggan.nama : 'Pelanggan #' + transaksi.pelanggan_id;
            document.getElementById('modalDetailTitle').innerText = 'Detail Transaksi #' + transaksi.id;
            document.getElementById('detailPelanggan').innerText = pelanggan;
            document.getElementById('detailMetode').innerText = (transaksi.metode_pembayaran || '-').toUpperCase();
            document.getElementById('detailStatus').innerText = transaksi.status_pembayaran === 'lunas' ? 'Lunas' : 'Belum Lunas';
            document.getElementById('detailTotal').innerText = formatRupiah(transaksi.total);

            const tbody = document.getElementById('detailItems');
            tbody.innerHTML = '';
            (transaksi.detail || []).forEach(item => {
                const produk = item.produk ? item.produk.nama : 'Produk #' + item.produk_id;
                tbody.innerHTML += `<tr>
                    <td>${produk}</td>
                    <td class="text-end">${formatRupiah(item.harga_satuan)}</td>
                    <td class="text-end">${item.qty}</td>
                    <td class="text-end">${formatRupiah(item.subtotal)}</td>
                </tr>`;
            });
            if ((transaksi.detail || []).length === 0) {
                tbody.innerHTML = '<tr><td colspan="4" class="text-center text-muted">Detail item tidak tersedia</td></tr>';
            }
        }
    </script>
</body>
</html>
