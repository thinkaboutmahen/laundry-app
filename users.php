<?php
require_once 'config/database.php';
require_once 'config/session.php';
requireLogin();
requireAdmin();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $hashedPassword = password_hash($_POST['password'], PASSWORD_DEFAULT);
                
                // Handle photo upload
                $userFoto = '';
                if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
                    $uploadDir = 'uploads/';
                    $fileName = time() . '_' . basename($_FILES['photo']['name']);
                    $uploadFile = $uploadDir . $fileName;
                    
                    // Create uploads directory if it doesn't exist
                    if (!file_exists($uploadDir)) {
                        mkdir($uploadDir, 0777, true);
                    }
                    
                    if (move_uploaded_file($_FILES['photo']['tmp_name'], $uploadFile)) {
                        $userFoto = $fileName;
                    }
                }

                $stmt = $pdo->prepare("
                    INSERT INTO user (
                        username, password, name, jenis_kelamin,
                        alamat, no_telepon, level, userFoto
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $_POST['username'],
                    $hashedPassword,
                    $_POST['name'],
                    $_POST['jenis_kelamin'],
                    $_POST['alamat'],
                    $_POST['no_telepon'],
                    $_POST['level'],
                    $userFoto
                ]);
                break;

            case 'edit':
                $sql = "
                    UPDATE user 
                    SET username = ?,
                        name = ?,
                        jenis_kelamin = ?,
                        alamat = ?,
                        no_telepon = ?,
                        level = ?
                ";
                $params = [
                    $_POST['username'],
                    $_POST['name'],
                    $_POST['jenis_kelamin'],
                    $_POST['alamat'],
                    $_POST['no_telepon'],
                    $_POST['level']
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
                        $sql .= ", userFoto = ?";
                        $params[] = $fileName;
                    }
                }

                // Only update password if provided
                if (!empty($_POST['password'])) {
                    $sql .= ", password = ?";
                    $params[] = password_hash($_POST['password'], PASSWORD_DEFAULT);
                }

                $sql .= " WHERE id_user = ?";
                $params[] = $_POST['id_user'];
                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);
                break;

            case 'delete':
                $stmt = $pdo->prepare("DELETE FROM user WHERE id_user = ?");
                $stmt->execute([$_POST['id_user']]);
                break;
        }
        header('Location: users.php');
        exit();
    }
}

// Get all users
$stmt = $pdo->query("SELECT * FROM user ORDER BY name");
$users = $stmt->fetchAll();

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
    <title>Data Users - Laundry Management System</title>
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
                <h2>Data Pengguna</h2>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
                    <i class="bi bi-plus-lg"></i> Tambah Pengguna
                </button>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Daftar Pengguna</h5>
                </div>
                <div class="card-body">
                    <div class="table-container">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Foto</th>
                                    <th>Username</th>
                                    <th>Nama</th>
                                    <th>Jenis Kelamin</th>
                                    <th>No. Telepon</th>
                                    <th>Role</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $no = 1;
                                foreach ($users as $user): 
                                ?>
                                <tr>
                                    <td data-label="No"><?php echo $no++; ?></td>
                                    <td data-label="Foto">
                                        <?php if (!empty($user['userFoto'])): ?>
                                            <img src="uploads/<?php echo htmlspecialchars($user['userFoto']); ?>" 
                                                 alt="User Photo" 
                                                 class="rounded-circle" 
                                                 style="width: 50px; height: 50px; object-fit: cover;">
                                        <?php else: ?>
                                            <div class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center" 
                                                 style="width: 50px; height: 50px;">
                                                <i class="bi bi-person-fill"></i>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td data-label="Username"><?php echo $user['username']; ?></td>
                                    <td data-label="Nama"><?php echo $user['name']; ?></td>
                                    <td data-label="Jenis Kelamin"><?php echo $user['jenis_kelamin']; ?></td>
                                    <td data-label="No. Telepon"><?php echo $user['no_telepon']; ?></td>
                                    <td data-label="Role">
                                        <span class="badge bg-<?php echo $user['level'] === 'Admin' ? 'danger' : 'primary'; ?>">
                                            <?php echo $user['level']; ?>
                                        </span>
                                    </td>
                                    <td data-label="Aksi">
                                        <div class="action-buttons">
                                            <button type="button" class="btn btn-sm btn-primary edit-user-btn" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#editUserModal"
                                                    data-id="<?php echo $user['id_user']; ?>"
                                                    data-username="<?php echo htmlspecialchars($user['username']); ?>"
                                                    data-name="<?php echo htmlspecialchars($user['name']); ?>"
                                                    data-gender="<?php echo htmlspecialchars($user['jenis_kelamin']); ?>"
                                                    data-address="<?php echo htmlspecialchars($user['alamat']); ?>"
                                                    data-phone="<?php echo htmlspecialchars($user['no_telepon']); ?>"
                                                    data-level="<?php echo htmlspecialchars($user['level']); ?>">
                                                <i class="bi bi-pencil"></i>
                                                <span class="d-none d-md-inline">Edit</span>
                                            </button>
                                            <?php if ($user['id_user'] !== $_SESSION['id_user']): ?>
                                            <button type="button" class="btn btn-sm btn-danger" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#deleteUserModal<?php echo $user['id_user']; ?>">
                                                <i class="bi bi-trash"></i>
                                                <span class="d-none d-md-inline">Hapus</span>
                                            </button>
                                            <?php endif; ?>
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

    <!-- Add User Modal -->
    <div class="modal fade" id="addUserModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Pengguna Baru</h5>
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
                            <label class="form-label">Username</label>
                            <input type="text" name="username" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Nama</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Jenis Kelamin</label>
                            <select name="jenis_kelamin" class="form-select" required>
                                <option value="Laki-laki">Laki-laki</option>
                                <option value="Perempuan">Perempuan</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Alamat</label>
                            <textarea name="alamat" class="form-control" rows="3" required></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">No. Telepon</label>
                            <input type="tel" name="no_telepon" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Role</label>
                            <select name="level" class="form-select" required>
                                <option value="Kasir">Kasir</option>
                                <option value="Admin">Admin</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Tambah Pengguna</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit User Modal -->
    <div class="modal fade" id="editUserModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Pengguna</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="edit">
                        <input type="hidden" name="id_user" id="edit_id_user">
                        
                        <div class="mb-3">
                            <label class="form-label">Foto</label>
                            <input type="file" name="photo" class="form-control" accept="image/*">
                            <small class="text-muted">Biarkan kosong untuk menjaga foto saat ini</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Nama Pengguna</label>
                            <input type="text" name="username" id="edit_username" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" class="form-control">
                            <small class="text-muted">Biarkan kosong untuk menjaga password saat ini</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Nama</label>
                            <input type="text" name="name" id="edit_name" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Jenis Kelamin</label>
                            <select name="jenis_kelamin" id="edit_jenis_kelamin" class="form-select" required>
                                <option value="Laki-laki">Laki-laki</option>
                                <option value="Perempuan">Perempuan</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Alamat</label>
                            <textarea name="alamat" id="edit_alamat" class="form-control" rows="3" required></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">No. Telepon</label>
                            <input type="tel" name="no_telepon" id="edit_no_telepon" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Role</label>
                            <select name="level" id="edit_level" class="form-select" required>
                                <option value="Kasir">Kasir</option>
                                <option value="Admin">Admin</option>
                            </select>
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

    <?php include 'components/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Edit user modal
        document.addEventListener('DOMContentLoaded', function() {
            const editButtons = document.querySelectorAll('.edit-user-btn');
            editButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const id = this.getAttribute('data-id');
                    const username = this.getAttribute('data-username');
                    const name = this.getAttribute('data-name');
                    const gender = this.getAttribute('data-gender');
                    const address = this.getAttribute('data-address');
                    const phone = this.getAttribute('data-phone');
                    const level = this.getAttribute('data-level');

                    document.getElementById('edit_id_user').value = id;
                    document.getElementById('edit_username').value = username;
                    document.getElementById('edit_name').value = name;
                    document.getElementById('edit_jenis_kelamin').value = gender;
                    document.getElementById('edit_alamat').value = address;
                    document.getElementById('edit_no_telepon').value = phone;
                    document.getElementById('edit_level').value = level;
                });
            });
        });
    </script>
</body>
</html> 