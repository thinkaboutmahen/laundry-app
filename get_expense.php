<?php
require_once 'config/database.php';
require_once 'config/session.php';
requireLogin();

if (isset($_GET['id'])) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM pengeluaran WHERE id_pengeluaran = ?");
        $stmt->execute([$_GET['id']]);
        $expense = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($expense) {
            header('Content-Type: application/json');
            echo json_encode($expense);
        } else {
            throw new Exception('Expense not found');
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
} else {
    http_response_code(400);
    echo json_encode(['error' => 'ID is required']);
} 