<?php
require_once 'config/database.php';
require_once 'config/session.php';
require_once 'config/email.php';
requireLogin();

// Function to get status color
function getStatusColor($status) {
    return match($status) {
        'Diterima' => 'primary',
        'Dicuci' => 'info',
        'Dikeringkan' => 'warning',
        'Disetrika' => 'warning',
        'Selesai' => 'success',
        'Diambil' => 'secondary',
        default => 'light'
    };
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $stmt = $pdo->prepare("
                    INSERT INTO transaksi (
                        id_pelanggan, id_user, id_laundry, tanggal_terima,
                        jumlah_kilo, catatan, total_bayar, status_laundry,
                        status_pembayaran, status_pengembalian
                    ) VALUES (?, ?, ?, NOW(), ?, ?, ?, 'Diterima', 'Belum Lunas', 'Belum')
                ");
                $stmt->execute([
                    $_POST['id_pelanggan'],
                    $_SESSION['id_user'],
                    $_POST['id_laundry'],
                    $_POST['jumlah_kilo'],
                    $_POST['catatan'],
                    $_POST['total_bayar']
                ]);
                break;

            case 'update_status':
                $stmt = $pdo->prepare("
                    UPDATE transaksi 
                    SET status_laundry = ?, tanggal_selesai = ?, status_pengembalian = ?
                    WHERE id_transaksi = ?
                ");
                $stmt->execute([
                    $_POST['status'], 
                    $_POST['tanggal_selesai'],
                    $_POST['status_pengembalian'],
                    $_POST['id_transaksi']
                ]);
                break;

            case 'update_payment':
                $stmt = $pdo->prepare("
                    UPDATE transaksi 
                    SET status_pembayaran = 'Lunas' 
                    WHERE id_transaksi = ?
                ");
                $stmt->execute([$_POST['id_transaksi']]);
                break;

            case 'delete':
                $stmt = $pdo->prepare("
                    DELETE FROM transaksi 
                    WHERE id_transaksi = ?
                ");
                $stmt->execute([$_POST['id_transaksi']]);
                break;
        }
        header('Location: transactions.php');
        exit();
    }
}

// Get all transactions
$payment_filter = $_GET['payment_status'] ?? 'all';

$query = "
    SELECT t.*, p.name_pelanggan, l.jenis_laundry, l.harga
    FROM transaksi t 
    JOIN pelanggan p ON t.id_pelanggan = p.id_pelanggan 
    JOIN paket_laundry l ON t.id_laundry = l.id_laundry 
";

if ($payment_filter !== 'all') {
    $query .= " WHERE t.status_pembayaran = ?";
    $stmt = $pdo->prepare($query . " ORDER BY t.tanggal_terima DESC");
    $stmt->execute([$payment_filter]);
} else {
    $stmt = $pdo->prepare($query . " ORDER BY t.tanggal_terima DESC");
    $stmt->execute();
}

$transactions = $stmt->fetchAll();

// Handle AJAX request for transaction details
if (isset($_GET['action']) && $_GET['action'] === 'get_detail' && isset($_GET['id'])) {
    try {
        $id_transaksi = $_GET['id'];
        
        // Validate ID
        if (!is_numeric($id_transaksi)) {
            throw new Exception('Invalid transaction ID');
        }

        $stmt = $pdo->prepare("
            SELECT 
                t.*, 
                p.name_pelanggan, p.alamat_pelanggan, p.jenis_kelamin_pelanggan, p.no_telepon_pelanggan, p.email_pelanggan,
                l.jenis_laundry, l.harga,
                u.name AS kasir_name
            FROM transaksi t 
            JOIN pelanggan p ON t.id_pelanggan = p.id_pelanggan 
            JOIN paket_laundry l ON t.id_laundry = l.id_laundry 
            JOIN user u ON t.id_user = u.id_user
            WHERE t.id_transaksi = ?
        ");
        
        if (!$stmt->execute([$id_transaksi])) {
            throw new Exception('Database query failed');
        }
        
        $transaction_detail = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$transaction_detail) {
            throw new Exception('Transaction not found');
        }

        // Prepare data for JSON response
        $response = [
            'tanggal' => date('Y-m-d', strtotime($transaction_detail['tanggal_terima'])),
            'no_order' => 'LD-' . str_pad($transaction_detail['id_transaksi'], 4, '0', STR_PAD_LEFT),
            'pelanggan' => $transaction_detail['name_pelanggan'],
            'email' => $transaction_detail['email_pelanggan'],
            'alamat' => $transaction_detail['alamat_pelanggan'],
            'jenis_kelamin' => $transaction_detail['jenis_kelamin_pelanggan'],
            'no_telp' => $transaction_detail['no_telepon_pelanggan'],
            'tanggal_selesai' => $transaction_detail['tanggal_selesai'] ? date('Y-m-d', strtotime($transaction_detail['tanggal_selesai'])) : '',
            'catatan' => $transaction_detail['catatan'],
            'status_pembayaran' => $transaction_detail['status_pembayaran'],
            'status_laundry' => $transaction_detail['status_laundry'],
            'status_pengambilan' => $transaction_detail['status_pengembalian'],
            'kasir' => $transaction_detail['kasir_name'],
            'items' => [
                [
                    'no' => 1,
                    'tanggal_terima' => date('Y-m-d', strtotime($transaction_detail['tanggal_terima'])),
                    'jenis_layanan' => $transaction_detail['jenis_laundry'],
                    'tanggal_selesai' => $transaction_detail['tanggal_selesai'] ? date('Y-m-d', strtotime($transaction_detail['tanggal_selesai'])) : '',
                    'berat_cucian' => $transaction_detail['jumlah_kilo'],
                    'harga_kg' => $transaction_detail['harga'],
                    'total_bayar' => $transaction_detail['total_bayar']
                ]
            ],
            'total_pesanan' => $transaction_detail['total_bayar']
        ];

        header('Content-Type: application/json');
        echo json_encode($response);
        exit();
    } catch (Exception $e) {
        http_response_code(500);
        header('Content-Type: application/json');
        echo json_encode(['error' => $e->getMessage()]);
        exit();
    }
}

// Get customers for dropdown
$stmt = $pdo->query("SELECT * FROM pelanggan ORDER BY name_pelanggan");
$customers = $stmt->fetchAll();

// Get laundry packages for dropdown
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
    <title>Transaksi Laundry - Laundry</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <?php include 'components/styles.php'; ?>
    <style>
        /* General body and container styling for better spacing */
        body {
            background-color: #f8f9fa; /* Light gray background */
            font-family: sans-serif; /* Use a common sans-serif font */
        }

        .custom-container {
            max-width: 1400px;
            margin: 20px auto; /* Add margin top/bottom */
            padding: 0 15px; /* Add horizontal padding */
        }

        .d-flex.justify-content-between.align-items-center.mb-4 {
            margin-bottom: 1.5rem !important; /* Increase bottom margin for header */
        }

        .table-container {
            margin-top: 20px;
            overflow-x: auto;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.05); /* Add subtle shadow to table container */
            border-radius: 8px; /* Match card border radius */
            background-color: #fff; /* White background for table */
        }
        .table thead th {
            position: sticky;
            top: 0;
            background-color: #e9ecef; /* Light gray background for table header */
            z-index: 1;
            box-shadow: 0 2px 2px -1px rgba(0, 0, 0, 0.1);
            white-space: nowrap;
            font-weight: 600; /* Slightly bolder header font */
            color: #495057; /* Darker text color */
        }
        .table {
            margin-bottom: 0;
            width: 100%;
        }
        .table td, .table th {
            padding: 12px 15px;
            vertical-align: middle;
            border-bottom: 1px solid #dee2e6; /* Add subtle bottom border to cells */
        }
        .table tbody tr:last-child td {
            border-bottom: none; /* Remove bottom border for last row */
        }

        .card {
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            border: none; /* Remove default card border */
        }
        .card-header {
            background-color: #ffffff; /* White background */
            border-bottom: 1px solid #e9ecef; /* Lighter border */
            padding: 15px 20px;
            font-size: 1.1rem; /* Slightly larger header font size */
            font-weight: 600;
        }
        .card-body {
            padding: 20px;
        }
        .btn-group {
            gap: 5px;
        }
        /* Style for status badges */
        .badge {
            padding: 0.5em 0.75em;
            font-size: 0.85em;
            font-weight: 600;
        }

        /* Action buttons styling */
        .action-buttons button {
            margin-right: 5px;
        }
        .action-buttons button:last-child {
            margin-right: 0;
        }
         .action-buttons .btn {
            padding: 0.375rem 0.75rem; /* Bootstrap default button padding */
            font-size: 0.9rem; /* Slightly smaller font size for action buttons */
            flex-grow: 1; /* Allow buttons to grow and fill the container */
            text-align: center; /* Center the text within the button */
        }
        /* Ensure action button divs don't add extra vertical space when wrapping */
        .action-buttons > div {
             display: flex;
            flex-wrap: wrap;
            gap: 5px;
            margin-bottom: 5px; /* Space between rows of buttons */
            justify-content: flex-end; /* Align buttons to the right */
        }
         .action-buttons > div:last-child {
            margin-bottom: 0;
        }

        /* Modal Detail Styling */
        #transactionDetailModal .modal-body {
            padding: 20px 30px; /* Add more padding inside modal */
        }
         #transactionDetailModal .modal-header {
            border-bottom: 1px solid #e9ecef; /* Lighter border */
        }
         #transactionDetailModal .modal-footer {
            border-top: 1px solid #e9ecef; /* Lighter border */
        }
        #transactionDetailModal .row.mb-3 {
            margin-bottom: 1rem !important; /* Standardize row margin */
        }
         #transactionDetailModal strong {
            color: #555; /* Slightly darker color for labels */
         }
         #transactionDetailModal h6.mt-4 {
            margin-top: 1.5rem !important; /* More space above section headers */
            margin-bottom: 1rem !important;
             border-bottom: 1px solid #eee; /* Subtle line below section header */
            padding-bottom: 5px;
         }
         #transactionDetailModal .table-bordered th,
         #transactionDetailModal .table-bordered td {
             border: 1px solid #dee2e6 !important; /* Ensure table borders are visible */
         }
        #transactionDetailModal .table-responsive {
            margin-top: 10px; /* Space above detail table */
        }
        #transactionDetailModal .text-end strong {
             font-size: 1.1rem; /* Slightly larger font for total */
             color: #333;
        }
         #transactionDetailModal .text-center .btn {
             margin-top: 1rem; /* Space above print button */
         }

        /* Add this CSS to prevent wrapping in detail table cells */
        #transactionDetailModal .table-bordered td {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* Card View for Responsive Table - Refined */
        @media (max-width: 768px) {
             .custom-container {
                padding: 0 10px; /* Less padding on smaller screens */
             }
            .d-flex.justify-content-between.align-items-center button.btn.btn-primary {
                padding: .375rem .75rem;
            }
            .table-responsive {
                border: 0;
            }
            .table thead {
                display: none;
            }
            .table, .table tbody, .table tr, .table td, .table th {
                display: block;
                width: 100%;
            }
            .table tr {
                margin-bottom: 15px;
                border: 1px solid #dee2e6;
                border-radius: .25rem;
                background-color: #fff;
                display: flex;
                flex-direction: column;
                padding: 10px;
                box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05); /* Add subtle shadow to each row card */
            }
             .table tr:last-child {
                 margin-bottom: 0; /* Remove margin for the last row */
             }

            .table td {
                border: none;
                border-bottom: 1px solid #eee !important;
                position: relative;
                padding-left: 50% !important;
                text-align: right;
                white-space: normal;
                font-size: 0.95rem; /* Slightly smaller font for table data */
            }
            .table td::before {
                content: attr(data-label);
                position: absolute;
                left: 15px;
                width: calc(50% - 30px);
                padding-right: 10px;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
                font-weight: bold;
                text-align: left;
                color: #555; /* Slightly darker color for labels */
            }
            .table td:last-child {
                border-bottom: 0 !important;
                padding-bottom: 0 !important; /* Reduce padding for the last cell */
            }
            .action-buttons {
                text-align: right;
                width: 100%;
                padding-top: 10px; /* Add padding above action buttons in card view */
                border-top: 1px solid #eee; /* Add a line above action buttons */
            }
            .action-buttons .btn-group {
                flex-direction: row; /* Keep buttons in a row even on small screens */
                justify-content: flex-end; /* Align buttons to the right */
                width: auto;
            }
             .action-buttons > div {
                justify-content: flex-end; /* Align buttons to the right */
                margin-bottom: 0; /* Remove margin between rows of buttons in card view */
            }
             .action-buttons > div:last-child {
                margin-bottom: 0;
            }
        }
    </style>
</head>
<body>
    <?php include 'components/navbar.php'; ?>
    <div class="d-flex">
        <?php include 'components/sidebar.php'; ?>

        <div class="container custom-container">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Transaksi Laundry</h2>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addTransactionModal">
                    <i class="bi bi-plus-lg"></i> <span class="d-none d-md-inline">Tambah Transaksi</span>
                </button>
            </div>

            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Daftar Transaksi</h5>
                        <div class="btn-group">
                            <a href="?payment_status=all" class="btn btn-outline-primary <?php echo $payment_filter === 'all' ? 'active' : ''; ?>">Semua</a>
                            <a href="?payment_status=Belum Lunas" class="btn btn-outline-primary <?php echo $payment_filter === 'Belum Lunas' ? 'active' : ''; ?>">Belum Lunas</a>
                            <a href="?payment_status=Lunas" class="btn btn-outline-primary <?php echo $payment_filter === 'Lunas' ? 'active' : ''; ?>">Lunas</a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Pelanggan</th>
                                    <th>Jenis Layanan</th>
                                    <th>Tgl. Terima</th>
                                    <th>Tgl. Selesai</th>
                                    <th style='white-space: nowrap;'>Status Laundry</th>
                                    <th style='white-space: nowrap;'>Status Ambil</th>
                                    <th style='white-space: nowrap;'>Status Bayar</th>
                                    <th style='white-space: nowrap;'>Total Bayar</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $no = 1;
                                foreach ($transactions as $transaction) {
                                    echo "<tr>";
                                    echo "<td data-label='No'>{$no}</td>";
                                    echo "<td data-label='Pelanggan'>{$transaction['name_pelanggan']}</td>";
                                    echo "<td data-label='Jenis Layanan' style='white-space: nowrap;'>{$transaction['jenis_laundry']}</td>";
                                    echo "<td data-label='Tgl. Terima'>" . date('d/m/Y', strtotime($transaction['tanggal_terima'])) . "</td>";
                                    echo "<td data-label='Tgl. Selesai'>" . ($transaction['tanggal_selesai'] ? date('d/m/Y', strtotime($transaction['tanggal_selesai'])) : '-') . "</td>";
                                    echo "<td data-label='Status Laundry'><span class='badge bg-" . getStatusColor($transaction['status_laundry']) . "'>{$transaction['status_laundry']}</span></td>";
                                    echo "<td data-label='Status Ambil'><span class='badge bg-" . ($transaction['status_pengembalian'] == 'Sudah' ? 'success' : 'warning') . "'>{$transaction['status_pengembalian']}</span></td>";
                                    echo "<td data-label='Status Bayar'><span class='badge bg-" . ($transaction['status_pembayaran'] == 'Lunas' ? 'success' : 'warning') . "'>{$transaction['status_pembayaran']}</span></td>";
                                    echo "<td data-label='Total Bayar' style='white-space: nowrap;'>Rp " . number_format($transaction['total_bayar'], 0, ',', '.') . "</td>";
                                    echo "<td data-label='Aksi' class='action-buttons'>";
                                    echo "<div>"; // Start first row div
                                    echo "<button type='button' class='btn btn-primary mb-1' onclick='showDetail({$transaction['id_transaksi']})'><i class='bi bi-eye'></i> Detail</button>";
                                    echo "<button type='button' class='btn btn-primary mb-1' onclick='updateStatus({$transaction['id_transaksi']})'><i class='bi bi-pencil'></i> Update</button>";
                                    echo "</div>"; // End first row div
                                    echo "<div>"; // Start second row div
                                    if ($transaction['status_pembayaran'] == 'Belum Lunas') {
                                        echo "<button type='button' class='btn btn-success me-1' onclick='updatePayment({$transaction['id_transaksi']})'><i class='bi bi-cash'></i> Lunasi</button>";
                                    } else {
                                        echo "<button type='button' class='btn btn-success me-1' onclick='printReceipt({$transaction['id_transaksi']})'><i class='bi bi-printer'></i> Cetak</button>";
                                    }
                                    echo "<button type='button' class='btn btn-danger' onclick='deleteTransaction({$transaction['id_transaksi']})'><i class='bi bi-trash'></i> Hapus</button>";
                                    echo "</div>"; // End second row div
                                    echo "</td>";
                                    echo "</tr>";
                                    $no++;
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Transaction Modal -->
    <div class="modal fade" id="addTransactionModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Transaksi Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="" method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add">
                        
                        <div class="mb-3">
                            <label class="form-label">Pelanggan</label>
                            <select name="id_pelanggan" class="form-select" required>
                                <option value="">Pilih Pelanggan</option>
                                <?php foreach ($customers as $customer): ?>
                                    <option value="<?php echo $customer['id_pelanggan']; ?>"><?php echo $customer['name_pelanggan']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Paket Laundry</label>
                            <select name="id_laundry" class="form-select" required>
                                <option value="">Pilih Paket</option>
                                <?php foreach ($packages as $package): ?>
                                    <option value="<?php echo $package['id_laundry']; ?>"><?php echo $package['jenis_laundry']; ?> - Rp <?php echo number_format($package['harga'], 0, ',', '.'); ?>/kg</option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Jumlah (kg)</label>
                            <input type="number" name="jumlah_kilo" class="form-control" step="0.1" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Catatan</label>
                            <textarea name="catatan" class="form-control" rows="3"></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Total Bayar</label>
                            <input type="number" name="total_bayar" class="form-control" required>
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

    <!-- Update Status Modal -->
    <div class="modal fade" id="updateStatusModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Update Status Laundry</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="" method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="update_status">
                        <input type="hidden" name="id_transaksi" id="update_status_id">
                        
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select" required>
                                <option value="Diterima">Diterima</option>
                                <option value="Dicuci">Dicuci</option>
                                <option value="Dikeringkan">Dikeringkan</option>
                                <option value="Disetrika">Disetrika</option>
                                <option value="Selesai">Selesai</option>
                                <option value="Diambil">Diambil</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Tanggal Selesai</label>
                            <input type="date" name="tanggal_selesai" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Status Pengembalian</label>
                            <select name="status_pengembalian" class="form-select" required>
                                <option value="Belum">Belum</option>
                                <option value="Sudah">Sudah</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Transaction Detail Modal -->
    <div class="modal fade" id="transactionDetailModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detail Transaksi Laundry</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Tanggal:</strong> <span id="detail_tanggal"></span>
                        </div>
                        <div class="col-md-6">
                            <strong>No. Order:</strong> <span id="detail_no_order"></span>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Pelanggan:</strong> <span id="detail_pelanggan"></span>
                        </div>
                        <div class="col-md-6">
                            <strong>Email:</strong> <span id="detail_email"></span>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Alamat:</strong> <span id="detail_alamat"></span>
                        </div>
                        <div class="col-md-6">
                            <strong>No. Telp:</strong> <span id="detail_no_telp"></span>
                        </div>
                    </div>
                     <div class="row mb-3">
                        <div class="col-md-6">
                             <strong>Jenis Kelamin:</strong> <span id="detail_jenis_kelamin"></span>
                        </div>
                         <div class="col-md-6">
                            <strong>Tanggal Selesai:</strong> <span id="detail_tanggal_selesai"></span>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                             <strong>Catatan Laundry:</strong> <span id="detail_catatan"></span>
                        </div>
                         <div class="col-md-6">
                            <strong>Status Pembayaran:</strong> <span id="detail_status_pembayaran"></span>
                        </div>
                    </div>
                     <div class="row mb-3">
                         <div class="col-md-6">
                            <strong>Status Laundry:</strong> <span id="detail_status_laundry"></span>
                        </div>
                         <div class="col-md-6">
                            <strong>Status Pengambilan Baju:</strong> <span id="detail_status_pengambilan"></span>
                        </div>
                    </div>
                     <div class="row mb-3">
                         <div class="col-md-6">
                            <strong>Kasir:</strong> <span id="detail_kasir"></span>
                        </div>
                    </div>

                    <h6 class="mt-4">Detail Pesanan:</h6>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Tanggal Terima</th>
                                    <th>Jenis Layanan</th>
                                    <th>Tanggal Selesai</th>
                                    <th>Berat Cucian</th>
                                    <th>Harga/Kg</th>
                                    <th>Total Bayar</th>
                                </tr>
                            </thead>
                            <tbody id="detail_items_body">
                                <!-- Transaction items will be populated here by JavaScript -->
                            </tbody>
                        </table>
                    </div>
                    <div class="row">
                        <div class="col-md-12 text-end">
                            <strong>TOTAL PESANAN:</strong> <span id="detail_total_pesanan"></span>
                        </div>
                    </div>
                     <div class="row mt-3">
                        <div class="col-md-12 text-center">
                            <button type="button" class="btn btn-primary me-2" onclick="printReceipt(document.getElementById('detail_no_order').innerText.replace('LD-', ''))">
                                <i class="bi bi-printer"></i> Cetak Invoice
                            </button>
                            <button type="button" class="btn btn-success" onclick="sendNotification(document.getElementById('detail_no_order').innerText.replace('LD-', ''))">
                                <i class="bi bi-bell"></i> Kirim Notifikasi
                            </button>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <?php include 'components/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Function to show transaction details modal
        function showDetail(id) {
            console.log('Fetching details for transaction ID:', id);

            fetch(`transactions.php?action=get_detail&id=${id}`)
                .then(response => {
                    if (!response.ok) {
                        return response.text().then(text => {
                            throw new Error(`HTTP error! status: ${response.status}, message: ${text}`);
                        });
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('Received data:', data);
                    if (!data || typeof data !== 'object') {
                        throw new Error('Invalid data received from server');
                    }
                    // Populate the modal with data
                    document.getElementById('detail_tanggal').innerText = data.tanggal || '-';
                    document.getElementById('detail_no_order').innerText = data.no_order || '-';
                    document.getElementById('detail_pelanggan').innerText = data.pelanggan || '-';
                    document.getElementById('detail_email').innerText = data.email || '-';
                    document.getElementById('detail_alamat').innerText = data.alamat || '-';
                    document.getElementById('detail_jenis_kelamin').innerText = data.jenis_kelamin || '-';
                    document.getElementById('detail_no_telp').innerText = data.no_telp || '-';
                    document.getElementById('detail_tanggal_selesai').innerText = data.tanggal_selesai || '-';
                    document.getElementById('detail_catatan').innerText = data.catatan || '-';
                    document.getElementById('detail_status_pembayaran').innerText = data.status_pembayaran || '-';
                    document.getElementById('detail_status_laundry').innerText = data.status_laundry || '-';
                    document.getElementById('detail_status_pengambilan').innerText = data.status_pengambilan || '-';
                    document.getElementById('detail_kasir').innerText = data.kasir || '-';

                    // Populate the items table
                    const itemsBody = document.getElementById('detail_items_body');
                    itemsBody.innerHTML = ''; // Clear previous items
                    if (data.items && Array.isArray(data.items)) {
                        data.items.forEach(item => {
                            const row = itemsBody.insertRow();
                            row.insertCell(0).innerText = item.no || '-';
                            row.insertCell(1).innerText = item.tanggal_terima || '-';
                            row.insertCell(2).innerText = item.jenis_layanan || '-';
                            row.insertCell(3).innerText = item.tanggal_selesai || '-';
                            row.insertCell(4).innerText = (item.berat_cucian ? item.berat_cucian + ' Kg' : '-');
                            row.insertCell(5).innerText = item.harga_kg ? 'Rp. ' + parseFloat(item.harga_kg).toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 0 }) : '-';
                            row.insertCell(6).innerText = item.total_bayar ? 'Rp. ' + parseFloat(item.total_bayar).toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 0 }) : '-';
                        });
                    }

                    // Populate total
                    document.getElementById('detail_total_pesanan').innerText = data.total_pesanan ? 'Rp. ' + parseFloat(data.total_pesanan).toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 0 }) : '-';

                    // Show the modal
                    new bootstrap.Modal(document.getElementById('transactionDetailModal')).show();
                })
                .catch(error => {
                    console.error('Error fetching details:', error);
                    alert('Failed to load transaction details: ' + error.message);
                });
        }

        function updateStatus(id) {
            document.getElementById('update_status_id').value = id;
            new bootstrap.Modal(document.getElementById('updateStatusModal')).show();
        }

        function updatePayment(id) {
            if (confirm('Apakah Anda yakin ingin menandai transaksi ini sebagai Lunas?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="update_payment">
                    <input type="hidden" name="id_transaksi" value="${id}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }

        function deleteTransaction(id) {
            if (confirm('Apakah Anda yakin ingin menghapus transaksi ini?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id_transaksi" value="${id}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }

        function sendNotification(transaksiId) {
            fetch('ajax/send_notification.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    transaksi_id: transaksiId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Notifikasi berhasil dikirim ke pelanggan!');
                } else {
                    alert('Gagal mengirim notifikasi: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan saat mengirim notifikasi');
            });
        }

        function sendInvoiceEmail(id) {
            fetch(`transactions.php?action=get_detail&id=${id}`)
                .then(response => {
                    if (!response.ok) {
                        return response.text().then(text => { throw new Error(`HTTP error! status: ${response.status}, message: ${text}`); });
                    }
                    return response.json();
                })
                .then(data => {
                    if (data) {
                        const invoiceHTML = generateInvoiceHTML(data);
                        // Send email using AJAX
                        fetch('ajax/send_invoice.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify({
                                transaksi_id: id,
                                invoice_html: invoiceHTML
                            })
                        })
                        .then(response => response.json())
                        .then(result => {
                            if (result.success) {
                                alert('Invoice berhasil dikirim ke email pelanggan!');
                            } else {
                                alert('Gagal mengirim invoice: ' + result.message);
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert('Terjadi kesalahan saat mengirim invoice');
                        });
                    } else {
                        alert('Failed to get transaction data for email.');
                    }
                })
                .catch(error => {
                    console.error('Error fetching data for email:', error);
                    alert('Error fetching transaction data for email: ' + error.message);
                });
        }

        // Calculate total based on package price and weight
        document.querySelector('select[name="id_laundry"]').addEventListener('change', function() {
            const package = this.options[this.selectedIndex].text;
            const price = parseFloat(package.split('Rp ')[1].replace('.', '').replace(',', '.'));
            const weight = document.querySelector('input[name="jumlah_kilo"]').value;
            if (price && weight) {
                document.querySelector('input[name="total_bayar"]').value = price * weight;
            }
        });

        document.querySelector('input[name="jumlah_kilo"]').addEventListener('input', function() {
            const package = document.querySelector('select[name="id_laundry"]').options[document.querySelector('select[name="id_laundry"]').selectedIndex].text;
            const price = parseFloat(package.split('Rp ')[1].replace('.', '').replace(',', '.'));
            const weight = this.value;
            if (price && weight) {
                document.querySelector('input[name="total_bayar"]').value = price * weight;
            }
        });

        // Function to generate the HTML for the invoice
        function generateInvoiceHTML(data) {
            let itemsRows = '';
            if (data.items && Array.isArray(data.items)) {
                data.items.forEach(item => {
                    itemsRows += `
                        <tr>
                            <td style="text-align: center;">${item.no || '-'}</td>
                            <td>${item.tanggal_terima || '-'}</td>
                            <td>${item.jenis_layanan || '-'}</td>
                            <td>${item.tanggal_selesai || '-'}</td>
                            <td style="text-align: right;">${(item.berat_cucian ? item.berat_cucian + ' Kg' : '-')}</td>
                            <td style="text-align: right;">${item.harga_kg ? 'Rp. ' + parseFloat(item.harga_kg).toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 0 }) : '-'}</td>
                            <td style="text-align: right;">${item.total_bayar ? 'Rp. ' + parseFloat(item.total_bayar).toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 0 }) : '-'}</td>
                        </tr>
                    `;
                });
            }

            return `
                <!DOCTYPE html>
                <html>
                <head>
                    <title>Invoice Transaksi ${data.no_order || ''}</title>
                    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
                    <style>
                        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
                        
                        :root {
                            --primary-color: #4361ee;
                            --secondary-color: #3f37c9;
                            --accent-color: #4895ef;
                            --success-color: #4cc9f0;
                            --text-primary: #2b2d42;
                            --text-secondary: #8d99ae;
                            --bg-light: #f8f9fa;
                            --border-color: #e9ecef;
                        }
                        
                        body { 
                            font-family: 'Poppins', sans-serif;
                            margin: 0;
                            padding: 10px;
                            background-color: var(--bg-light);
                            color: var(--text-primary);
                            line-height: 1.4;
                            font-size: 12px;
                        }
                        
                        .container { 
                            width: 100%;
                            max-width: 800px;
                            margin: 0 auto;
                            background: white;
                            padding: 15px;
                            border-radius: 8px;
                            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
                        }
                        
                        .invoice-header {
                            display: flex;
                            align-items: center;
                            margin-bottom: 15px;
                            padding-bottom: 10px;
                            border-bottom: 1px solid var(--border-color);
                        }
                        
                        .invoice-header .logo img {
                            max-width: 100px;
                            height: auto;
                        }
                        
                        .invoice-header .company-info h2 {
                            margin: 0;
                            color: var(--primary-color);
                            font-size: 18px;
                            font-weight: 700;
                        }
                        
                        .invoice-header .company-info p {
                            margin: 2px 0 0;
                            color: var(--text-secondary);
                            font-size: 11px;
                            display: flex;
                            align-items: center;
                            gap: 4px;
                        }
                        
                        .invoice-details {
                            margin-bottom: 15px;
                            padding: 10px;
                            background: var(--bg-light);
                            border-radius: 6px;
                        }
                        
                        .detail-row {
                            display: flex;
                            margin-bottom: 6px;
                            flex-wrap: wrap;
                            gap: 8px;
                        }
                        
                        .detail-item {
                            flex: 1 1 calc(50% - 8px);
                            min-width: 200px;
                            display: flex;
                            align-items: flex-start;
                            background: white;
                            padding: 4px 8px;
                            border-radius: 4px;
                        }
                        
                        .invoice-details strong {
                            width: 120px;
                            flex-shrink: 0;
                            margin-right: 8px;
                            color: var(--text-primary);
                            font-weight: 600;
                            font-size: 11px;
                        }
                        
                        .invoice-details span {
                            flex-grow: 1;
                            word-break: break-word;
                            color: var(--text-primary);
                            font-size: 11px;
                        }
                        
                        .detail-header {
                            margin: 15px 0 10px;
                            font-size: 14px;
                            font-weight: 600;
                            color: var(--primary-color);
                            padding-bottom: 5px;
                            border-bottom: 1px solid var(--border-color);
                        }
                        
                        table {
                            width: 100%;
                            border-collapse: separate;
                            border-spacing: 0;
                            margin-bottom: 15px;
                            border-radius: 4px;
                            overflow: hidden;
                        }
                        
                        th {
                            background-color: var(--primary-color);
                            color: white;
                            font-weight: 500;
                            padding: 8px;
                            text-align: left;
                            font-size: 11px;
                            text-transform: uppercase;
                            letter-spacing: 0.5px;
                        }
                        
                        td {
                            padding: 6px 8px;
                            border-bottom: 1px solid var(--border-color);
                            color: #000; /* Warna hitam */
                            font-size: 11px;
                            background: white;
                        }
                        
                        .total-row {
                            text-align: right;
                            font-size: 14px;
                            font-weight: 600;
                            color: var(--primary-color);
                            padding: 10px;
                            background: var(--bg-light);
                            border-radius: 6px;
                            margin-top: 10px;
                        }
                        
                        .footer {
                            margin-top: 15px;
                            padding-top: 10px;
                            border-top: 1px solid var(--border-color);
                            text-align: center;
                            color: var(--text-secondary);
                            font-size: 11px;
                        }
                        
                        .status-badge {
                            display: inline-block;
                            padding: 2px 6px;
                            border-radius: 4px;
                            font-size: 10px;
                            font-weight: 500;
                            text-transform: uppercase;
                            letter-spacing: 0.5px;
                        }
                        
                        .status-paid {
                            background-color: #d4edda;
                            color: #155724;
                            border: 1px solid #c3e6cb;
                        }
                        
                        .status-unpaid {
                            background-color: #f8d7da;
                            color: #721c24;
                            border: 1px solid #f5c6cb;
                        }
                        
                        @media print {
                            body {
                                padding: 0;
                                margin: 0;
                            }
                            .container {
                                box-shadow: none;
                                padding: 10px;
                            }
                        }
                    </style>
                </head>
                <body>
                    <div class="container">
                        <div class="invoice-header">
                            <div class="logo">
                                <img src="assets/images/logoinvoice.png" alt="Laundry App Logo">
                            </div>
                            <div class="company-info">
                                <h2>Laundry</h2>
                                <p><i class="bi bi-geo-alt"></i>Jl. Prof. M.Yamin No.74, Kota Baru, Kec. Pontianak Sel., Kota Pontianak, Kalimantan Barat 78113</p>
                                <p><i class="bi bi-telephone"></i>(021) 123-4567</p>
                                <p><i class="bi bi-envelope"></i>info@laundry.com</p>
                            </div>
                        </div>

                        <div class="invoice-details">
                            <div class="detail-row">
                                <div class="detail-item">
                                    <strong>Invoice No:</strong>
                                    <span>${data.no_order || '-'}</span>
                                </div>
                                <div class="detail-item">
                                    <strong>Tanggal:</strong>
                                    <span>${data.tanggal || '-'}</span>
                                </div>
                            </div>
                            <div class="detail-row">
                                <div class="detail-item">
                                    <strong>Pelanggan:</strong>
                                    <span>${data.pelanggan || '-'}</span>
                                </div>
                                <div class="detail-item">
                                    <strong>Email:</strong>
                                    <span>${data.email || '-'}</span>
                                </div>
                            </div>
                            <div class="detail-row">
                                <div class="detail-item">
                                    <strong>Alamat:</strong>
                                    <span>${data.alamat || '-'}</span>
                                </div>
                                <div class="detail-item">
                                    <strong>No. Telp:</strong>
                                    <span>${data.no_telp || '-'}</span>
                                </div>
                            </div>
                            <div class="detail-row">
                                <div class="detail-item">
                                    <strong>Kasir:</strong>
                                    <span>${data.kasir || '-'}</span>
                                </div>
                            </div>
                            <div class="detail-row">
                                <div class="detail-item">
                                    <strong>Status Pembayaran:</strong>
                                    <span class="status-badge ${data.status_pembayaran === 'Lunas' ? 'status-paid' : 'status-unpaid'}">
                                        ${data.status_pembayaran || '-'}
                                    </span>
                                </div>
                                <div class="detail-item">
                                    <strong>Status Laundry:</strong>
                                    <span>${data.status_laundry || '-'}</span>
                                </div>
                            </div>
                        </div>

                        <div class="detail-header">Detail Pesanan</div>
                        <table>
                            <thead>
                                <tr>
                                    <th style="width: 5%; text-align: center;">No</th>
                                    <th style="width: 15%;">Tanggal Terima</th>
                                    <th style="width: 20%;">Jenis Layanan</th>
                                    <th style="width: 15%;">Tanggal Selesai</th>
                                    <th style="width: 10%; text-align: right;">Berat</th>
                                    <th style="width: 15%; text-align: right;">Harga/Kg</th>
                                    <th style="width: 20%; text-align: right;">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${itemsRows}
                            </tbody>
                        </table>

                        <div class="total-row">
                            TOTAL PESANAN: ${data.total_pesanan ? 'Rp. ' + parseFloat(data.total_pesanan).toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 0 }) : '-'}
                        </div>

                        <div class="footer">
                            <p>Terima kasih telah menggunakan layanan kami!</p>
                        </div>
                    </div>
                </body>
                </html>
            `;
        }

        // Replace printReceipt function with sendInvoiceEmail
        function printReceipt(id) {
            fetch(`transactions.php?action=get_detail&id=${id}`)
                .then(response => {
                    if (!response.ok) {
                        return response.text().then(text => { throw new Error(`HTTP error! status: ${response.status}, message: ${text}`); });
                    }
                    return response.json();
                })
                .then(data => {
                    if (data) {
                        const invoiceHTML = generateInvoiceHTML(data);
                        const printWindow = window.open('', '_blank');
                        if (printWindow) {
                            printWindow.document.write(invoiceHTML);
                            printWindow.document.close();
                            // Wait for content to load before printing
                            printWindow.onload = function() {
                                printWindow.print();
                                // Optional: close the window after printing
                                // printWindow.close();
                            };
                        } else {
                            alert('Please allow pop-ups for printing.');
                        }
                    } else {
                        alert('Failed to get transaction data for printing.');
                    }
                })
                .catch(error => {
                    console.error('Error fetching data for printing:', error);
                    alert('Error fetching transaction data for printing: ' + error.message);
                });
        }
    </script>
</body>
</html> 