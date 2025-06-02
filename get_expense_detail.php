<?php
require_once 'config/database.php';
require_once 'config/session.php';
requireLogin();

header('Content-Type: application/json');

if (isset($_GET['id'])) {
    $id_pengeluaran = $_GET['id'];

    // Validate ID
    if (!is_numeric($id_pengeluaran)) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid expense ID']);
        exit();
    }

    try {
        $stmt = $pdo->prepare("
            SELECT 
                p.tanggal_pengeluaran,
                p.jumlah_pengeluaran,
                p.keterangan,
                u.name as user_name
            FROM pengeluaran p
            JOIN user u ON p.id_user = u.id_user
            WHERE p.id_pengeluaran = ?
        ");
        
        if (!$stmt->execute([$id_pengeluaran])) {
            throw new Exception('Database query failed');
        }
        
        $expense_detail = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$expense_detail) {
            http_response_code(404);
            echo json_encode(['error' => 'Expense not found']);
            exit();
        }

        echo json_encode($expense_detail);

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
        exit();
    }
} else {
    http_response_code(400);
    echo json_encode(['error' => 'Expense ID not provided']);
    exit();
}
?> 