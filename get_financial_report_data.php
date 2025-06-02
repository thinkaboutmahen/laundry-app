<?php
require_once 'config/database.php';
require_once 'config/session.php';
requireLogin();

header('Content-Type: application/json');

try {
    // Get all expenses
    $stmtExpenses = $pdo->query("
        SELECT pengeluaran.*, user.name as user_name 
        FROM pengeluaran 
        JOIN user ON pengeluaran.id_user = user.id_user 
        ORDER BY pengeluaran.tanggal_pengeluaran DESC
    ");
    $expenses = $stmtExpenses->fetchAll(PDO::FETCH_ASSOC);

    // Get total expenses
    $totalExpenses = $pdo->query("SELECT COALESCE(SUM(jumlah_pengeluaran), 0) as total FROM pengeluaran")->fetch(PDO::FETCH_ASSOC)['total'];

    // Get all transactions
    $stmtTransactions = $pdo->query("
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
    $transactions = $stmtTransactions->fetchAll(PDO::FETCH_ASSOC);

    // Get total transactions
    $totalTransactions = $pdo->query("SELECT COALESCE(SUM(total_bayar), 0) as total FROM transaksi")->fetch(PDO::FETCH_ASSOC)['total'];

    $reportData = [
        'expenses' => $expenses,
        'totalExpenses' => $totalExpenses,
        'transactions' => $transactions,
        'totalTransactions' => $totalTransactions
    ];

    echo json_encode($reportData);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
    exit();
}
?> 