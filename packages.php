<?php
require_once 'config/database.php';
require_once 'config/session.php';
requireLogin();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $stmt = $pdo->prepare("
                    INSERT INTO paket_laundry (
                        jenis_laundry, estimasi_waktu, harga
                    ) VALUES (?, ?, ?)
                ");
                $stmt->execute([
                    $_POST['jenis_laundry'],
                    $_POST['estimasi_waktu'],
                    $_POST['harga']
                ]);
                break;

            case 'edit':
                $stmt = $pdo->prepare("
                    UPDATE paket_laundry 
                    SET jenis_laundry = ?,
                        estimasi_waktu = ?,
                        harga = ?
                    WHERE id_laundry = ?
                ");
                $stmt->execute([
                    $_POST['jenis_laundry'],
                    $_POST['estimasi_waktu'],
                    $_POST['harga'],
                    $_POST['id_laundry']
                ]);
                break;

            case 'delete':
                $stmt = $pdo->prepare("DELETE FROM paket_laundry WHERE id_laundry = ?");
                $stmt->execute([$_POST['id_laundry']]);
                break;
        }
        header('Location: packages.php');
        exit();
    }
}

// Get all packages
$stmt = $pdo->query("SELECT * FROM paket_laundry ORDER BY jenis_laundry");
$packages = $stmt->fetchAll();

// Tambahkan query jumlah user, pelanggan, paket, transaksi, pengeluaran
define('SHOW_SIDEBAR', true);
$userCount = $pdo->query("SELECT COUNT(*) as total FROM user")->fetch()['total'];
$customerCount = $pdo->query("SELECT COUNT(*) as total FROM pelanggan")->fetch()['total'];
$packageCount = $pdo->query("SELECT COUNT(*) as total FROM paket_laundry")->fetch()['total'];
$transactionCount = $pdo->query("SELECT COUNT(*) as total FROM transaksi")->fetch()['total'];
$expenseCount = $pdo->query("SELECT COUNT(*) as total FROM pengeluaran")->fetch()['total'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jenis Layanan - Laundry Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <?php include 'components/styles.php'; ?>
</head>
<body>
    <?php include 'components/navbar.php'; ?>
    <div class="d-flex">
        <?php include 'components/sidebar.php'; ?>

        <div class="container custom-container">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Jenis Laundry</h2>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addPackageModal">
                    <i class="bi bi-plus-lg"></i> Tambah Paket
                </button>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Daftar Paket</h5>
                </div>
                <div class="card-body">
                    <div class="table-container">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama Paket</th>
                                    <th>Estimasi Waktu</th>
                                    <th>Harga</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $no = 1;
                                foreach ($packages as $package): 
                                ?>
                                <tr>
                                    <td data-label="No"><?php echo $no++; ?></td>
                                    <td data-label="Nama Paket"><?php echo $package['jenis_laundry']; ?></td>
                                    <td data-label="Estimasi Waktu"><?php echo $package['estimasi_waktu']; ?></td>
                                    <td data-label="Harga">Rp <?php echo number_format($package['harga'], 0, ',', '.'); ?></td>
                                    <td data-label="Aksi">
                                        <div class="action-buttons">
                                            <button type="button" class="btn btn-sm btn-primary" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#editPackageModal<?php echo $package['id_laundry']; ?>"
                                                    data-id="<?php echo $package['id_laundry']; ?>"
                                                    data-type="<?php echo $package['jenis_laundry']; ?>"
                                                    data-time="<?php echo $package['estimasi_waktu']; ?>"
                                                    data-price="<?php echo $package['harga']; ?>">
                                                <i class="bi bi-pencil"></i>
                                                <span class="d-none d-md-inline">Edit</span>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-danger" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#deletePackageModal<?php echo $package['id_laundry']; ?>">
                                                <i class="bi bi-trash"></i>
                                                <span class="d-none d-md-inline">Hapus</span>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Package Modal -->
    <div class="modal fade" id="addPackageModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Paket Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add">
                        
                        <div class="mb-3">
                            <label class="form-label">Jenis Laundry</label>
                            <input type="text" name="jenis_laundry" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Estimasi Waktu</label>
                            <select name="estimasi_waktu" class="form-select" required>
                                <option value="">Pilih Estimasi Waktu</option>
                                <option value="2 Hari">2 Hari</option>
                                <option value="3 Hari">3 Hari</option>
                                <option value="4 Hari">4 Hari</option>
                                <option value="7 Hari">7 Hari</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Harga (per kg)</label>
                            <input type="number" name="harga" class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Tambah Paket</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Package Modals -->
    <?php foreach ($packages as $package): ?>
    <div class="modal fade" id="editPackageModal<?php echo $package['id_laundry']; ?>" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Paket</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="edit">
                        <input type="hidden" name="id_laundry" value="<?php echo $package['id_laundry']; ?>">
                        
                        <div class="mb-3">
                            <label class="form-label">Jenis Laundry</label>
                            <input type="text" name="jenis_laundry" class="form-control" value="<?php echo $package['jenis_laundry']; ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Estimasi Waktu</label>
                            <select name="estimasi_waktu" class="form-select" required>
                                <option value="2 Hari" <?php echo $package['estimasi_waktu'] == '2 Hari' ? 'selected' : ''; ?>>2 Hari</option>
                                <option value="3 Hari" <?php echo $package['estimasi_waktu'] == '3 Hari' ? 'selected' : ''; ?>>3 Hari</option>
                                <option value="4 Hari" <?php echo $package['estimasi_waktu'] == '4 Hari' ? 'selected' : ''; ?>>4 Hari</option>
                                <option value="7 Hari" <?php echo $package['estimasi_waktu'] == '7 Hari' ? 'selected' : ''; ?>>7 Hari</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Harga (per kg)</label>
                            <input type="number" name="harga" class="form-control" value="<?php echo $package['harga']; ?>" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endforeach; ?>

    <!-- Delete Package Modal -->
    <?php foreach ($packages as $package): ?>
    <div class="modal fade" id="deletePackageModal<?php echo $package['id_laundry']; ?>" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Hapus Paket</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id_laundry" value="<?php echo $package['id_laundry']; ?>">
                        <p>Apakah Anda yakin ingin menghapus paket "<?php echo $package['jenis_laundry']; ?>"?</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-danger">Hapus</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endforeach; ?>

    </div>
</div>

<?php include 'components/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Reset form Tambah Paket setiap kali modal ditutup
    const addPackageModal = document.getElementById('addPackageModal');
    addPackageModal.addEventListener('hidden.bs.modal', function () {
        const form = addPackageModal.querySelector('form');
        form.reset();
        // Set kembali dropdown estimasi waktu ke default
        const select = form.querySelector('select[name="estimasi_waktu"]');
        if (select) select.selectedIndex = 0;
    });

    // Validasi form sebelum submit
    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', function(e) {
            const estimasiWaktu = this.querySelector('select[name="estimasi_waktu"]');
            if (!estimasiWaktu.value) {
                e.preventDefault();
                alert('Silakan pilih estimasi waktu terlebih dahulu!');
                estimasiWaktu.focus();
            }
        });
    });
</script>
</body>
</html> 