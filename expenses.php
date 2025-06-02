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
                    INSERT INTO pengeluaran (
                        tanggal_pengeluaran, jumlah_pengeluaran, keterangan, id_user
                    ) VALUES (?, ?, ?, ?)
                ");
                $stmt->execute([
                    $_POST['tanggal_pengeluaran'],
                    $_POST['jumlah_pengeluaran'],
                    $_POST['keterangan'],
                    $_SESSION['id_user']
                ]);
                break;

            case 'edit':
                $stmt = $pdo->prepare("
                    UPDATE pengeluaran 
                    SET tanggal_pengeluaran = ?,
                        jumlah_pengeluaran = ?,
                        keterangan = ?
                    WHERE id_pengeluaran = ?
                ");
                $stmt->execute([
                    $_POST['tanggal_pengeluaran'],
                    $_POST['jumlah_pengeluaran'],
                    $_POST['keterangan'],
                    $_POST['id_pengeluaran']
                ]);
                break;

            case 'delete':
                $stmt = $pdo->prepare("DELETE FROM pengeluaran WHERE id_pengeluaran = ?");
                $stmt->execute([$_POST['id_pengeluaran']]);
                break;
        }
        header('Location: expenses.php');
        exit();
    }
}

// Get all expenses
$stmt = $pdo->query("
    SELECT pengeluaran.*, user.name as user_name 
    FROM pengeluaran 
    JOIN user ON pengeluaran.id_user = user.id_user 
    ORDER BY pengeluaran.tanggal_pengeluaran DESC
");
$expenses = $stmt->fetchAll();

// Get total expenses
$totalExpenses = $pdo->query("SELECT COALESCE(SUM(jumlah_pengeluaran), 0) as total FROM pengeluaran")->fetch()['total'];

// Get all transactions
$stmt = $pdo->query("
    SELECT t.*, 
           p.name_pelanggan,
           l.jenis_laundry,
           u.name as user_name
    FROM transaksi t
    JOIN pelanggan p ON t.id_pelanggan = p.id_pelanggan
    JOIN paket_laundry l ON t.id_laundry = l.id_laundry
    JOIN user u ON t.id_user = u.id_user
    ORDER BY t.tanggal_terima DESC
");
$transactions = $stmt->fetchAll();

// Get total transactions
$totalTransactions = $pdo->query("SELECT COALESCE(SUM(total_bayar), 0) as total FROM transaksi")->fetch()['total'];

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
    <title>Data Pengeluaran & Transaksi - Laundry</title>
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
                <h2>Data Pengeluaran & Transaksi</h2>
                <div>
                    <button type="button" class="btn btn-success me-2" data-bs-toggle="modal" data-bs-target="#printReportModal">
                        <i class="bi bi-printer"></i> Cetak Laporan
                    </button>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addExpenseModal">
                        <i class="bi bi-plus-lg"></i> Tambah Pengeluaran
                    </button>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Total Pengeluaran</h5>
                            <h3 class="text-danger">Rp <?php echo number_format($totalExpenses, 0, ',', '.'); ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Total Pendapatan</h5>
                            <h3 class="text-success">Rp <?php echo number_format($totalTransactions, 0, ',', '.'); ?></h3>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Expenses Table -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Data Pengeluaran</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Tanggal</th>
                                    <th>Jumlah</th>
                                    <th>Keterangan</th>
                                    <th>Dibuat Oleh</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $no = 1;
                                foreach ($expenses as $expense) {
                                    echo "<tr>";
                                    echo "<td>{$no}</td>";
                                    echo "<td>" . date('d/m/Y', strtotime($expense['tanggal_pengeluaran'])) . "</td>";
                                    echo "<td>Rp " . number_format($expense['jumlah_pengeluaran'], 0, ',', '.') . "</td>";
                                    echo "<td>{$expense['keterangan']}</td>";
                                    echo "<td>{$expense['user_name']}</td>";
                                    echo "<td>";
                                    echo "<button type='button' class='btn btn-sm btn-primary me-1' onclick='editExpense({$expense['id_pengeluaran']})'><i class='bi bi-pencil'></i></button>";
                                    echo "<button type='button' class='btn btn-sm btn-danger' onclick='deleteExpense({$expense['id_pengeluaran']})'><i class='bi bi-trash'></i></button>";
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

            <!-- Transactions Table -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Data Transaksi</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Tanggal</th>
                                    <th>Pelanggan</th>
                                    <th>Jenis Layanan</th>
                                    <th>Total Bayar</th>
                                    <th>Status</th>
                                    <th>Dibuat Oleh</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $no = 1;
                                foreach ($transactions as $transaction) {
                                    echo "<tr>";
                                    echo "<td>{$no}</td>";
                                    echo "<td>" . date('d/m/Y', strtotime($transaction['tanggal_terima'])) . "</td>";
                                    echo "<td>{$transaction['name_pelanggan']}</td>";
                                    echo "<td>{$transaction['jenis_laundry']}</td>";
                                    echo "<td>Rp " . number_format($transaction['total_bayar'], 0, ',', '.') . "</td>";
                                    echo "<td><span class='badge bg-" . ($transaction['status_pembayaran'] == 'Lunas' ? 'success' : 'warning') . "'>{$transaction['status_pembayaran']}</span></td>";
                                    echo "<td>{$transaction['user_name']}</td>";
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

    <!-- Add Expense Modal -->
    <div class="modal fade" id="addExpenseModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <input type="hidden" name="action" value="add">
                    <div class="modal-header">
                        <h5 class="modal-title">Tambah Pengeluaran</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Tanggal</label>
                            <input type="date" name="tanggal_pengeluaran" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Jumlah</label>
                            <input type="number" name="jumlah_pengeluaran" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Keterangan</label>
                            <textarea name="keterangan" class="form-control" rows="3" required></textarea>
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

    <!-- Edit Expense Modal -->
    <div class="modal fade" id="editExpenseModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="id_pengeluaran" id="edit_id_pengeluaran">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Pengeluaran</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Tanggal</label>
                            <input type="date" name="tanggal_pengeluaran" id="edit_tanggal_pengeluaran" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Jumlah</label>
                            <input type="number" name="jumlah_pengeluaran" id="edit_jumlah_pengeluaran" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Keterangan</label>
                            <textarea name="keterangan" id="edit_keterangan" class="form-control" rows="3" required></textarea>
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

    <!-- Delete Expense Modal -->
    <div class="modal fade" id="deleteExpenseModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id_pengeluaran" id="delete_id_pengeluaran">
                    <div class="modal-header">
                        <h5 class="modal-title">Hapus Pengeluaran</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p>Apakah Anda yakin ingin menghapus pengeluaran ini?</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-danger">Hapus</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Print Report Modal -->
    <div class="modal fade" id="printReportModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Cetak Laporan Keuangan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Periode Laporan</label>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Dari Tanggal</label>
                                <input type="date" id="startDate" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Sampai Tanggal</label>
                                <input type="date" id="endDate" class="form-control" required>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" onclick="printReportWithDateRange()">Cetak</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Add PHP variables to JavaScript
        const totalExpenses = <?php echo json_encode($totalExpenses ?? 0); ?>;
        const totalTransactions = <?php echo json_encode($totalTransactions ?? 0); ?>;
        console.log('Initialized variables:', { totalExpenses, totalTransactions });

        // Set default date range (current month) and add date validation
        document.addEventListener('DOMContentLoaded', function() {
            const today = new Date();
            const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
            const lastDay = new Date(today.getFullYear(), today.getMonth() + 1, 0);
            
            // Format today's date as YYYY-MM-DD
            const todayFormatted = today.toISOString().split('T')[0];
            
            // Set max date to today for both date inputs
            document.getElementById('startDate').max = todayFormatted;
            document.getElementById('endDate').max = todayFormatted;
            
            // Set default values
            document.getElementById('startDate').value = firstDay.toISOString().split('T')[0];
            document.getElementById('endDate').value = lastDay.toISOString().split('T')[0];

            // Add event listeners for date validation
            document.getElementById('startDate').addEventListener('change', validateDates);
            document.getElementById('endDate').addEventListener('change', validateDates);
        });

        function validateDates() {
            const startDate = document.getElementById('startDate');
            const endDate = document.getElementById('endDate');
            const today = new Date();
            today.setHours(0, 0, 0, 0); // Reset time to start of day

            // Convert input values to Date objects
            const startDateValue = new Date(startDate.value);
            const endDateValue = new Date(endDate.value);

            // Reset time to start of day for comparison
            startDateValue.setHours(0, 0, 0, 0);
            endDateValue.setHours(0, 0, 0, 0);

            // Validate start date
            if (startDateValue > today) {
                alert('Tanggal mulai tidak boleh lebih besar dari hari ini');
                startDate.value = today.toISOString().split('T')[0];
                return false;
            }

            // Validate end date
            if (endDateValue > today) {
                alert('Tanggal akhir tidak boleh lebih besar dari hari ini');
                endDate.value = today.toISOString().split('T')[0];
                return false;
            }

            // Validate date range
            if (startDateValue > endDateValue) {
                alert('Tanggal mulai tidak boleh lebih besar dari tanggal akhir');
                startDate.value = endDate.value;
                return false;
            }

            return true;
        }

        function printReportWithDateRange() {
            const startDate = document.getElementById('startDate').value;
            const endDate = document.getElementById('endDate').value;

            if (!startDate || !endDate) {
                alert('Silakan pilih periode laporan');
                return;
            }

            if (!validateDates()) {
                return;
            }

            console.log('Print report function called with date range:', { startDate, endDate });

            const printWindow = window.open('', '_blank');
            if (!printWindow) {
                alert('Could not open print window. Please allow pop-ups for this site.');
                return;
            }

            // Fetch all financial report data with date range
            fetch(`get_financial_report_data.php?start_date=${startDate}&end_date=${endDate}`)
                .then(response => {
                    if (!response.ok) {
                        return response.text().then(text => {
                            throw new Error(`HTTP error! status: ${response.status}, message: ${text}`);
                        });
                    }
                    return response.json();
                })
                .then(data => {
                    // Filter expenses based on date range
                    const filteredExpenses = data.expenses.filter(expense => {
                        const expenseDate = new Date(expense.tanggal_pengeluaran);
                        return expenseDate >= new Date(startDate) && expenseDate <= new Date(endDate);
                    });

                    // Filter transactions based on date range
                    const filteredTransactions = data.transactions.filter(transaction => {
                        const transactionDate = new Date(transaction.tanggal_terima);
                        return transactionDate >= new Date(startDate) && transactionDate <= new Date(endDate);
                    });

                    // Calculate totals for filtered data
                    const totalExpenses = filteredExpenses.reduce((sum, expense) => sum + parseFloat(expense.jumlah_pengeluaran), 0);
                    const totalTransactions = filteredTransactions.reduce((sum, transaction) => sum + parseFloat(transaction.total_bayar), 0);

                    console.log('Filtered data:', {
                        expenses: filteredExpenses,
                        transactions: filteredTransactions,
                        totalExpenses,
                        totalTransactions
                    });

                    const formatDate = (date) => {
                        return new Date(date).toLocaleDateString('id-ID', {
                            day: 'numeric',
                            month: 'long',
                            year: 'numeric'
                        });
                    };

                    const currentDate = new Date().toLocaleDateString('id-ID', {
                        day: 'numeric',
                        month: 'long',
                        year: 'numeric'
                    });

                    const formatRupiah = (number) => {
                        const num = parseFloat(number);
                        if (isNaN(num)) {
                            return '-';
                        }
                        return new Intl.NumberFormat('id-ID').format(num);
                    };

                    // Generate Expenses Table HTML with filtered data
                    let expensesTableHTML = `
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Tanggal</th>
                                    <th>Jumlah</th>
                                    <th>Keterangan</th>
                                    <th>Dibuat Oleh</th>
                                </tr>
                            </thead>
                            <tbody>
                    `;
                    if (filteredExpenses.length > 0) {
                        filteredExpenses.forEach((expense, index) => {
                            expensesTableHTML += `
                                <tr>
                                    <td>${index + 1}</td>
                                    <td>${formatDate(expense.tanggal_pengeluaran)}</td>
                                    <td>Rp ${formatRupiah(expense.jumlah_pengeluaran)}</td>
                                    <td>${expense.keterangan || '-'}</td>
                                    <td>${expense.user_name || '-'}</td>
                                </tr>
                            `;
                        });
                    } else {
                        expensesTableHTML += `<tr><td colspan="5" class="text-center">Tidak ada data pengeluaran.</td></tr>`;
                    }
                    expensesTableHTML += `
                            </tbody>
                        </table>
                    `;

                    // Generate Transactions Table HTML with filtered data
                    let transactionsTableHTML = `
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Tanggal</th>
                                    <th>Pelanggan</th>
                                    <th>Jenis Layanan</th>
                                    <th>Total Bayar</th>
                                    <th>Status Pembayaran</th>
                                    <th>Dibuat Oleh</th>
                                </tr>
                            </thead>
                            <tbody>
                    `;
                    if (filteredTransactions.length > 0) {
                        filteredTransactions.forEach((transaction, index) => {
                            transactionsTableHTML += `
                                <tr>
                                    <td>${index + 1}</td>
                                    <td>${formatDate(transaction.tanggal_terima)}</td>
                                    <td>${transaction.name_pelanggan || '-'}</td>
                                    <td>${transaction.jenis_laundry || '-'}</td>
                                    <td>Rp ${formatRupiah(transaction.total_bayar)}</td>
                                    <td><span class='badge bg-${transaction.status_pembayaran == 'Lunas' ? 'success' : 'warning'}'>${transaction.status_pembayaran || '-'}</span></td>
                                    <td>${transaction.user_name || '-'}</td>
                                </tr>
                            `;
                        });
                    } else {
                        transactionsTableHTML += `<tr><td colspan="7" class="text-center">Tidak ada data transaksi.</td></tr>`;
                    }
                    transactionsTableHTML += `
                            </tbody>
                        </table>
                    `;

                    const printContent = `
                        <!DOCTYPE html>
                        <html>
                        <head>
                            <title>Laporan Keuangan Laundry</title>
                            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
                            <style>
                                body {
                                    padding: 30px; /* Increased padding */
                                    font-family: 'Arial', sans-serif;
                                    color: #333;
                                    line-height: 1.6;
                                    font-size: 10pt; /* Slightly smaller base font for reports */
                                }
                                .header {
                                    text-align: center;
                                    margin-bottom: 40px; /* More space below header */
                                    border-bottom: 2px solid #000; /* Stronger border for header */
                                    padding-bottom: 25px; /* More padding below header */
                                    position: relative;
                                }
                                .header img {
                                    position: absolute;
                                    top: 0;
                                    left: 0;
                                    height: 70px; /* Slightly larger logo height */
                                    width: auto; /* Maintain aspect ratio */
                                    max-width: 150px; /* Prevent excessively large logo */
                                }
                                .header h2 {
                                    margin-bottom: 5px;
                                    color: #333;
                                    font-size: 2em; /* Larger main heading */
                                    font-weight: bold;
                                }
                                .header p {
                                    color: #666;
                                    margin: 0;
                                    font-size: 1.2em; /* Larger period font */
                                }
                                .section {
                                    margin-bottom: 30px;
                                    page-break-inside: avoid;
                                }
                                .section h4 {
                                    margin-bottom: 15px;
                                    border-bottom: 1px solid #ccc; /* Lighter border for section titles */
                                    padding-bottom: 8px; /* More padding below section title */
                                    color: #555; /* Slightly lighter color for section titles */
                                    font-size: 1.5em; /* Section heading size */
                                    font-weight: bold;
                                }
                                .summary {
                                    margin-bottom: 20px;
                                }
                                .total-section {
                                    background-color: #f0f0f0; /* Lighter grey background */
                                    padding: 25px; /* More padding */
                                    border-radius: 8px;
                                    margin: 20px 0 40px 0; /* More space below summary */
                                    border: 1px solid #ddd; /* Lighter border */
                                    box-shadow: 0 4px 8px rgba(0,0,0,0.1); /* More pronounced shadow */
                                }
                                .total-section h4 {
                                    color: #333;
                                    margin-bottom: 20px; /* More space below summary title */
                                    font-size: 1.6em;
                                    text-align: center;
                                    border-bottom: none; /* Remove border from summary title */
                                    padding-bottom: 0;
                                }
                                .total-row {
                                    display: flex;
                                    justify-content: space-between;
                                    margin-bottom: 12px; /* More space between rows */
                                    padding: 10px 0; /* Consistent padding */
                                    border-bottom: 1px dashed #ccc;
                                    align-items: center;
                                    font-size: 1.1em; /* Slightly larger font in summary */
                                }
                                .total-row:last-child {
                                    border-bottom: none;
                                    font-weight: bold;
                                    font-size: 1.4em; /* Larger font for saldo */
                                    color: #000;
                                    margin-top: 20px; /* More space above saldo */
                                    padding-top: 20px;
                                    border-top: 2px solid #aaa; /* Stronger top border for saldo */
                                }
                                .total-row span:first-child {
                                    flex-basis: 60%;
                                    padding-right: 10px; /* Add some spacing */
                                }
                                .total-row span:last-child {
                                    flex-basis: 40%;
                                    text-align: right;
                                    padding-left: 10px; /* Add some spacing */
                                }
                                table {
                                    width: 100%;
                                    border-collapse: collapse;
                                    margin-bottom: 1rem;
                                    box-shadow: 0 1px 3px rgba(0,0,0,0.05);
                                    font-size: 0.95em; /* Slightly smaller font in tables */
                                }
                                th, td {
                                    padding: 12px 15px; /* More padding in cells */
                                    border: 1px solid #ddd; /* Lighter border */
                                    text-align: left;
                                }
                                th {
                                    background-color: #e9ecef;
                                    font-weight: bold;
                                    color: #495057;
                                    border-bottom: 2px solid #ccc; /* Lighter but visible border */
                                }
                                tbody tr:nth-child(even) {
                                    background-color: #f8f9fa;
                                }
                                tbody tr:hover {
                                    background-color: #e2e6ea;
                                }
                                .text-success {
                                    color: #28a745 !important;
                                }
                                .text-danger {
                                    color: #dc3545 !important;
                                }
                                .text-muted {
                                     color: #6c757d !important;
                                     font-size: 0.9em; /* Smaller font for total rows below title */
                                     margin-top: -10px; /* Pull up closer to title */
                                }
                                .badge {
                                    padding: 0.4em 0.7em; /* Slightly more padding */
                                    border-radius: 0.25rem;
                                    font-size: 0.85em; /* Slightly larger badge font */
                                    font-weight: bold;
                                    display: inline-block; /* Ensure padding works */
                                }
                                .badge.bg-success {
                                     background-color: #28a745 !important;
                                     color: #fff !important;
                                }
                                .badge.bg-warning {
                                     background-color: #ffc107 !important;
                                     color: #212529 !important;
                                }
                                /* Print specific styles */
                                @media print {
                                    body {
                                        padding: 15px; /* Less padding for printing */
                                        font-size: 9pt; /* Smaller font for printing */
                                    }
                                    .no-print {
                                        display: none;
                                    }
                                    .container {
                                        width: 100%;
                                        max-width: none;
                                    }
                                     .header {
                                         margin-bottom: 30px;
                                         padding-bottom: 20px;
                                         border-bottom: 1px solid #000;
                                     }
                                     .header img {
                                         position: static;
                                         float: left; /* Float logo to the left */
                                         margin-right: 20px; /* Space next to logo */
                                         height: 50px; /* Smaller logo for print */
                                     }
                                    .header h2, .header p {
                                         text-align: left; /* Align text left next to logo */
                                    }
                                     .total-section {
                                         background-color: #fff !important;
                                         border: 1px solid #000;
                                         box-shadow: none;
                                         border-radius: 0;
                                         margin: 15px 0;
                                         padding: 15px;
                                     }
                                    .total-section h4 {
                                         text-align: left; /* Align summary title left */
                                         margin-bottom: 10px;
                                    }
                                    .total-row {
                                         font-size: 1em;
                                         margin-bottom: 8px;
                                         padding: 5px 0;
                                         border-bottom: 1px dashed #888;
                                    }
                                    .total-row:last-child {
                                        font-size: 1.1em;
                                        margin-top: 10px;
                                        padding-top: 10px;
                                        border-top: 1px solid #000; /* Solid border for saldo in print */
                                    }
                                    table {
                                        page-break-inside: auto;
                                        box-shadow: none;
                                        border-collapse: collapse; /* Ensure collapse for print */
                                    }
                                    th, td {
                                         border: 1px solid #000; /* Black borders for print */
                                         padding: 8px 10px; /* Less padding for print */
                                    }
                                    th {
                                        background-color: #eee !important; /* Light grey header for print */
                                        -webkit-print-color-adjust: exact; /* Ensure background prints */
                                        color: #000 !important;
                                        border-bottom: 1px solid #000;
                                    }
                                    tbody tr:nth-child(even) {
                                         background-color: #f0f0f0 !important; /* Lighter zebra for print */
                                        -webkit-print-color-adjust: exact;
                                    }
                                     tbody tr:hover {
                                         background-color: #f0f0f0 !important;
                                     }
                                     .text-success, .text-danger {
                                         -webkit-print-color-adjust: exact;
                                     }
                                }
                            </style>
                        </head>
                        <body>
                            <div class="container">
                                <div class="header">
                                    <!-- Place your logo file (e.g., logoinvoice.png) in the 'assets' directory -->
                                    <!-- and update the src attribute below if the path is different. -->
                                    <img src="assets/logoinvoice.png" alt="Company Logo">
                                    <h2>Laporan Keuangan Laundry</h2>
                                    <p>Periode: ${formatDate(startDate)} - ${formatDate(endDate)}</p>
                                    <p>Dicetak pada: ${currentDate}</p>
                                </div>

                                <div class="total-section">
                                    <h4>Ringkasan Keuangan</h4>
                                    <div class="total-row">
                                        <span>Total Pemasukan:</span>
                                        <span class="text-success">Rp ${formatRupiah(totalTransactions)}</span>
                                    </div>
                                    <div class="total-row">
                                        <span>Total Pengeluaran:</span>
                                        <span class="text-danger">Rp ${formatRupiah(totalExpenses)}</span>
                                    </div>
                                    <div class="total-row">
                                        <span>Saldo:</span>
                                        <span class="${totalTransactions - totalExpenses >= 0 ? 'text-success' : 'text-danger'}">
                                            Rp ${formatRupiah(totalTransactions - totalExpenses)}
                                        </span>
                                    </div>
                                </div>

                                <div class="section">
                                    <h4>Detail Pemasukan</h4>
                                    <p class="text-muted mb-3">Total Pemasukan: Rp ${formatRupiah(totalTransactions)}</p>
                                    ${transactionsTableHTML}
                                </div>

                                <div class="section">
                                    <h4>Detail Pengeluaran</h4>
                                    <p class="text-muted mb-3">Total Pengeluaran: Rp ${formatRupiah(totalExpenses)}</p>
                                    ${expensesTableHTML}
                                </div>

                                <div class="text-center mt-4 no-print">
                                    <button onclick="window.print()" class="btn btn-primary">Cetak Laporan</button>
                                </div>
                            </div>
                        </body>
                        </html>
                    `;

                    console.log('Writing content to print window...');
                    printWindow.document.open();
                    printWindow.document.write(printContent);
                    printWindow.document.close();
                    console.log('Print window content written');

                    // Close the modal
                    const modal = bootstrap.Modal.getInstance(document.getElementById('printReportModal'));
                    modal.hide();

                })
                .catch(error => {
                    console.error('Error fetching financial report data:', error);
                    alert('Failed to load financial report data: ' + error.message);
                    printWindow.close();
                });
        }

        function editExpense(id) {
            // Fetch expense data and populate modal
            fetch(`get_expense.php?id=${id}`)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('edit_id_pengeluaran').value = data.id_pengeluaran;
                    document.getElementById('edit_tanggal_pengeluaran').value = data.tanggal_pengeluaran;
                    document.getElementById('edit_jumlah_pengeluaran').value = data.jumlah_pengeluaran;
                    document.getElementById('edit_keterangan').value = data.keterangan;
                    new bootstrap.Modal(document.getElementById('editExpenseModal')).show();
                });
        }

        function deleteExpense(id) {
            document.getElementById('delete_id_pengeluaran').value = id;
            new bootstrap.Modal(document.getElementById('deleteExpenseModal')).show();
        }

    </script>
</body>
</html> 