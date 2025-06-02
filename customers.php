<?php
require_once 'config/database.php';
require_once 'config/session.php';
requireLogin();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                // Handle photo upload
                $pelangganFoto = '';
                if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
                    $uploadDir = 'uploads/';
                    $fileName = time() . '_' . basename($_FILES['photo']['name']);
                    $uploadFile = $uploadDir . $fileName;
                    
                    // Create uploads directory if it doesn't exist
                    if (!file_exists($uploadDir)) {
                        mkdir($uploadDir, 0777, true);
                    }
                    
                    if (move_uploaded_file($_FILES['photo']['tmp_name'], $uploadFile)) {
                        $pelangganFoto = $fileName;
                    }
                }

                $stmt = $pdo->prepare("
                    INSERT INTO pelanggan (
                        name_pelanggan, jenis_kelamin_pelanggan,
                        alamat_pelanggan, no_telepon_pelanggan,
                        pelanggan_Foto, email_pelanggan
                    ) VALUES (?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $_POST['name_pelanggan'],
                    $_POST['jenis_kelamin_pelanggan'],
                    $_POST['alamat_pelanggan'],
                    $_POST['no_telepon_pelanggan'],
                    $pelangganFoto,
                    $_POST['email_pelanggan']
                ]);
                break;

            case 'edit':
                $sql = "
                    UPDATE pelanggan 
                    SET name_pelanggan = ?,
                        jenis_kelamin_pelanggan = ?,
                        alamat_pelanggan = ?,
                        no_telepon_pelanggan = ?,
                        email_pelanggan = ?
                ";
                $params = [
                    $_POST['name_pelanggan'],
                    $_POST['jenis_kelamin_pelanggan'],
                    $_POST['alamat_pelanggan'],
                    $_POST['no_telepon_pelanggan'],
                    $_POST['email_pelanggan']
                ];

                // Handle photo upload
                if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
                    $uploadDir = 'uploads/';
                    $fileName = time() . '_' . basename($_FILES['photo']['name']);
                    $uploadFile = $uploadDir . $fileName;
                    
                    // Create uploads directory if it doesn't exist
                    if (!file_exists($uploadDir)) {
                        mkdir($uploadDir, 0777, true);
                    }
                    
                    if (move_uploaded_file($_FILES['photo']['tmp_name'], $uploadFile)) {
                        $sql .= ", pelanggan_Foto = ?";
                        $params[] = $fileName;
                    }
                }

                $sql .= " WHERE id_pelanggan = ?";
                $params[] = $_POST['id_pelanggan'];
                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);
                break;

            case 'delete':
                $stmt = $pdo->prepare("DELETE FROM pelanggan WHERE id_pelanggan = ?");
                $stmt->execute([$_POST['id_pelanggan']]);
                break;
        }
        header('Location: customers.php');
        exit();
    }
}

// Get all customers
$stmt = $pdo->query("SELECT * FROM pelanggan ORDER BY name_pelanggan");
$customers = $stmt->fetchAll();

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
    <title>Data Pelanggan - Laundry Management System</title>
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
                <h2>Data Pelanggan</h2>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCustomerModal">
                    <i class="bi bi-plus-lg"></i> Tambah Pelanggan
                </button>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Daftar Pelanggan</h5>
                </div>
                <div class="card-body">
                    <div class="table-container">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Foto</th>
                                    <th>Nama</th>
                                    <th>Jenis Kelamin</th>
                                    <th>Alamat</th>
                                    <th>No. Telepon</th>
                                    <th>Email</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($customers as $customer): ?>
                                <tr>
                                    <td data-label="ID"><?php echo $customer['id_pelanggan']; ?></td>
                                    <td data-label="Foto">
                                        <?php if (!empty($customer['pelanggan_Foto'])): ?>
                                            <img src="uploads/<?php echo htmlspecialchars($customer['pelanggan_Foto']); ?>" 
                                                 alt="Customer Photo" 
                                                 class="rounded-circle" 
                                                 style="width: 50px; height: 50px; object-fit: cover;">
                                        <?php else: ?>
                                            <div class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center" 
                                                 style="width: 50px; height: 50px;">
                                                <i class="bi bi-person-fill"></i>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td data-label="Nama"><?php echo $customer['name_pelanggan']; ?></td>
                                    <td data-label="Jenis Kelamin"><?php echo $customer['jenis_kelamin_pelanggan']; ?></td>
                                    <td data-label="Alamat"><?php echo $customer['alamat_pelanggan']; ?></td>
                                    <td data-label="No. Telepon"><?php echo $customer['no_telepon_pelanggan']; ?></td>
                                    <td data-label="Email"><?php echo $customer['email_pelanggan']; ?></td>
                                    <td data-label="Aksi">
                                        <div class="action-buttons">
                                            <button type="button" class="btn btn-sm btn-primary" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#editCustomerModal<?php echo $customer['id_pelanggan']; ?>">
                                                <i class="bi bi-pencil"></i>
                                                <span class="d-none d-md-inline">Edit</span>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-danger" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#deleteCustomerModal<?php echo $customer['id_pelanggan']; ?>">
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

    <!-- Add Customer Modal -->
    <div class="modal fade" id="addCustomerModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Pelanggan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add">
                        <div class="mb-3">
                            <label class="form-label">Foto</label>
                            <input type="file" name="photo" class="form-control" accept="image/*">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nama</label>
                            <input type="text" class="form-control" name="name_pelanggan" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Jenis Kelamin</label>
                            <select class="form-select" name="jenis_kelamin_pelanggan" required>
                                <option value="Laki-laki">Laki-laki</option>
                                <option value="Perempuan">Perempuan</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Alamat</label>
                            <textarea class="form-control" name="alamat_pelanggan" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">No. Telepon</label>
                            <input type="text" class="form-control" name="no_telepon_pelanggan" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email_pelanggan" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Customer Modals -->
    <?php foreach ($customers as $customer): ?>
    <div class="modal fade" id="editCustomerModal<?php echo $customer['id_pelanggan']; ?>" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Pelanggan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="edit">
                        <input type="hidden" name="id_pelanggan" value="<?php echo $customer['id_pelanggan']; ?>">
                        <div class="mb-3">
                            <label class="form-label">Foto</label>
                            <input type="file" name="photo" class="form-control" accept="image/*">
                            <small class="text-muted">Biarkan kosong untuk menjaga foto saat ini</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nama</label>
                            <input type="text" class="form-control" name="name_pelanggan" 
                                   value="<?php echo $customer['name_pelanggan']; ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Jenis Kelamin</label>
                            <select class="form-select" name="jenis_kelamin_pelanggan" required>
                                <option value="Laki-laki" <?php echo $customer['jenis_kelamin_pelanggan'] === 'Laki-laki' ? 'selected' : ''; ?>>Laki-laki</option>
                                <option value="Perempuan" <?php echo $customer['jenis_kelamin_pelanggan'] === 'Perempuan' ? 'selected' : ''; ?>>Perempuan</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Alamat</label>
                            <textarea class="form-control" name="alamat_pelanggan" required><?php echo $customer['alamat_pelanggan']; ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">No. Telepon</label>
                            <input type="text" class="form-control" name="no_telepon_pelanggan" 
                                   value="<?php echo $customer['no_telepon_pelanggan']; ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email_pelanggan" 
                                   value="<?php echo $customer['email_pelanggan']; ?>" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Customer Modal -->
    <div class="modal fade" id="deleteCustomerModal<?php echo $customer['id_pelanggan']; ?>" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Hapus Pelanggan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id_pelanggan" value="<?php echo $customer['id_pelanggan']; ?>">
                        <p>Apakah Anda yakin ingin menghapus pelanggan ini?</p>
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

    <?php include 'components/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 