<?php
require_once 'api_config.php';
requireLogin();

$message = '';
$pelanggans = callAPI('GET', '/pelanggan')['response'] ?? [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $data = [
        'nama' => $_POST['nama'] ?? '',
        'telepon' => $_POST['telepon'] ?? '',
        'alamat' => $_POST['alamat'] ?? ''
    ];

    if ($_POST['action'] === 'create') {
        $result = callAPI('POST', '/pelanggan', $data);
        $message = $result['code'] == 200
            ? '<div class="alert alert-success">Pelanggan berhasil ditambahkan</div>'
            : '<div class="alert alert-danger">Gagal: ' . htmlspecialchars($result['response']['detail'] ?? 'Error') . '</div>';
    } elseif ($_POST['action'] === 'update') {
        $id = $_POST['id'];
        $result = callAPI('PUT', "/pelanggan/$id", $data);
        $message = $result['code'] == 200
            ? '<div class="alert alert-success">Pelanggan berhasil diupdate</div>'
            : '<div class="alert alert-danger">Gagal: ' . htmlspecialchars($result['response']['detail'] ?? 'Error') . '</div>';
    } elseif ($_POST['action'] === 'delete') {
        $id = $_POST['id'];
        $result = callAPI('DELETE', "/pelanggan/$id");
        $message = $result['code'] == 200
            ? '<div class="alert alert-success">Pelanggan berhasil dihapus</div>'
            : '<div class="alert alert-danger">Gagal: ' . htmlspecialchars($result['response']['detail'] ?? 'Error') . '</div>';
    }

    $pelanggans = callAPI('GET', '/pelanggan')['response'] ?? [];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Pelanggan</title>
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
                    <li class="nav-item"><a class="nav-link active" href="pelanggan.php">Pelanggan</a></li>
                    <li class="nav-item"><a class="nav-link" href="transaksi.php">Transaksi</a></li>
                    <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2 class="mb-0">Manajemen Pelanggan</h2>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalPelanggan" onclick="resetForm()">Tambah Pelanggan</button>
        </div>

        <?= $message ?>

        <div class="table-responsive">
            <table class="table table-bordered table-striped align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Nama</th>
                        <th>Telepon</th>
                        <th>Alamat</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($pelanggans)): ?>
                    <tr>
                        <td colspan="5" class="text-center text-muted">Belum ada data pelanggan</td>
                    </tr>
                    <?php endif; ?>
                    <?php foreach ($pelanggans as $pelanggan): ?>
                    <tr>
                        <td><?= $pelanggan['id'] ?></td>
                        <td><?= htmlspecialchars($pelanggan['nama']) ?></td>
                        <td><?= htmlspecialchars($pelanggan['telepon'] ?? '-') ?></td>
                        <td><?= nl2br(htmlspecialchars($pelanggan['alamat'] ?? '-')) ?></td>
                        <td class="text-center">
                            <button
                                class="btn btn-sm btn-warning"
                                data-bs-toggle="modal"
                                data-bs-target="#modalPelanggan"
                                onclick='editPelanggan(<?= htmlspecialchars(json_encode($pelanggan), ENT_QUOTES, "UTF-8") ?>)'
                            >Edit</button>
                            <form method="POST" class="d-inline" onsubmit="return confirm('Hapus pelanggan ini?')">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= $pelanggan['id'] ?>">
                                <button class="btn btn-sm btn-danger">Hapus</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="modal fade" id="modalPelanggan" tabindex="-1">
        <div class="modal-dialog">
            <form method="POST" class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Tambah Pelanggan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" id="formAction" value="create">
                    <input type="hidden" name="id" id="pelangganId">
                    <div class="mb-3">
                        <label for="nama" class="form-label">Nama Pelanggan</label>
                        <input type="text" class="form-control" id="nama" name="nama" required>
                    </div>
                    <div class="mb-3">
                        <label for="telepon" class="form-label">Telepon</label>
                        <input type="text" class="form-control" id="telepon" name="telepon">
                    </div>
                    <div class="mb-3">
                        <label for="alamat" class="form-label">Alamat</label>
                        <textarea class="form-control" id="alamat" name="alamat" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function resetForm() {
            document.getElementById('formAction').value = 'create';
            document.getElementById('pelangganId').value = '';
            document.getElementById('nama').value = '';
            document.getElementById('telepon').value = '';
            document.getElementById('alamat').value = '';
            document.getElementById('modalTitle').innerText = 'Tambah Pelanggan';
        }

        function editPelanggan(pelanggan) {
            document.getElementById('formAction').value = 'update';
            document.getElementById('pelangganId').value = pelanggan.id;
            document.getElementById('nama').value = pelanggan.nama || '';
            document.getElementById('telepon').value = pelanggan.telepon || '';
            document.getElementById('alamat').value = pelanggan.alamat || '';
            document.getElementById('modalTitle').innerText = 'Edit Pelanggan';
        }
    </script>
</body>
</html>
