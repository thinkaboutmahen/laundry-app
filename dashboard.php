<?php
ob_start();
require_once 'config/database.php';
require_once 'config/session.php';
requireLogin();

// Get counts for dashboard
$stmt = $pdo->query("SELECT COUNT(*) as total FROM transaksi WHERE status_laundry != 'Diambil'");
$activeOrders = $stmt->fetch()['total'];

$stmt = $pdo->query("SELECT COUNT(*) as total FROM pelanggan");
$totalCustomers = $stmt->fetch()['total'];

$stmt = $pdo->query("SELECT COUNT(*) as total FROM transaksi WHERE status_pembayaran = 'Belum Lunas'");
$unpaidOrders = $stmt->fetch()['total'];

$stmt = $pdo->query("SELECT COUNT(*) as total FROM transaksi WHERE status_laundry = 'Selesai'");
$completedOrders = $stmt->fetch()['total'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Beranda</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <?php include 'components/styles.php'; ?>
</head>
<body>
    <?php include 'components/navbar.php'; ?>
    <div class="d-flex">
        <?php include 'components/sidebar.php'; ?>

        <div class="container custom-container mt-4">
            <h2 class="mb-4">Beranda</h2>
            
            <div class="row">
                <div class="col-md-3 mb-4">
                    <div class="card bg-primary text-white">
                        <div class="card-body">
                            <h5 class="card-title">Transaksi Aktif</h5>
                            <h4 class="card-text"><?php echo $activeOrders; ?></h4>
                            <p class="card-text small"><small>Transaksi yang sedang berlangsung</small></p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3 mb-4">
                    <div class="card bg-success text-white">
                        <div class="card-body">
                            <h5 class="card-title">Total Pelanggan</h5>
                            <h2 class="card-text"><?php echo $totalCustomers; ?></h2>
                            <p class="card-text small"><small>Pelanggan yang terdaftar</small></p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3 mb-4">
                    <div class="card bg-warning text-white">
                        <div class="card-body">
                            <h5 class="card-title">Transaksi Belum Lunas</h5>
                            <h2 class="card-text"><?php echo $unpaidOrders; ?></h2>
                            <p class="card-text small"><small>Pembayaran yang belum lunas</small></p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3 mb-4">
                    <div class="card bg-info text-white">
                        <div class="card-body">
                            <h5 class="card-title">Transaksi Selesai</h5>
                            <h2 class="card-text"><?php echo $completedOrders; ?></h2>
                            <p class="card-text small"><small>Transaksi yang telah selesai</small></p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mt-4">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Transaksi Terakhir</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Pelanggan</th>
                                            <th>Paket</th>
                                            <th>Status</th>
                                            <th>Pembayaran</th>
                                            <th>Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $stmt = $pdo->query("
                                            SELECT t.*, p.name_pelanggan, l.jenis_laundry 
                                            FROM transaksi t 
                                            JOIN pelanggan p ON t.id_pelanggan = p.id_pelanggan 
                                            JOIN paket_laundry l ON t.id_laundry = l.id_laundry 
                                            ORDER BY t.tanggal_terima DESC LIMIT 5
                                        ");
                                        $no = 1;
                                        while ($row = $stmt->fetch()) {
                                            echo "<tr>";
                                            echo "<td data-label='No'>" . $no++ . "</td>";
                                            echo "<td data-label='Pelanggan'>{$row['name_pelanggan']}</td>";
                                            echo "<td data-label='Paket'>{$row['jenis_laundry']}</td>";
                                            echo "<td data-label='Status'>{$row['status_laundry']}</td>";
                                            echo "<td data-label='Pembayaran'><span class='badge bg-" . ($row['status_pembayaran'] == 'Lunas' ? 'success' : 'danger') . "'>{$row['status_pembayaran']}</span></td>";
                                            echo "<td data-label='Total'>Rp " . number_format($row['total_bayar'], 0, ',', '.') . "</td>";
                                            echo "</tr>";
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'components/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 